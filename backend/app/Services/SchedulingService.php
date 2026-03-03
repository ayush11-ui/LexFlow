<?php

namespace App\Services;

use App\Models\CourtCase;
use App\Models\Hearing;
use App\Models\JudgeAvailability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SchedulingService
{
    private const COURTROOMS = ['Courtroom A', 'Courtroom B', 'Courtroom C', 'Courtroom D'];

    /**
     * Auto-schedule a single case.
     */
    public function autoSchedule(CourtCase $case): ?Hearing
    {
        return DB::transaction(function () use ($case) {
            // Find suitable judge
            $judge = $this->findSuitableJudge($case);
            if (!$judge) {
                return null;
            }

            // Find available slot
            $slot = $this->findAvailableSlot($judge, $case);
            if (!$slot) {
                return null;
            }

            // Create hearing
            $hearing = Hearing::create([
                'case_id' => $case->id,
                'judge_id' => $judge->id,
                'courtroom' => $slot['courtroom'],
                'hearing_date' => $slot['date'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'status' => 'scheduled',
            ]);

            // Update case
            $case->update([
                'status' => 'scheduled',
                'assigned_judge_id' => $judge->id,
            ]);

            return $hearing;
        });
    }

    /**
     * Auto-schedule all unscheduled cases by priority.
     */
    public function autoScheduleAll(): array
    {
        $cases = CourtCase::pending()
            ->highPriority()
            ->get();

        $scheduled = [];
        $failed = [];

        foreach ($cases as $case) {
            $hearing = $this->autoSchedule($case);
            if ($hearing) {
                $scheduled[] = $case->case_number;
            } else {
                $failed[] = $case->case_number;
            }
        }

        return [
            'scheduled' => $scheduled,
            'failed' => $failed,
            'scheduled_count' => count($scheduled),
            'failed_count' => count($failed),
        ];
    }

    /**
     * Manually schedule a case with specific parameters.
     */
    public function manualSchedule(CourtCase $case, array $params): Hearing|array
    {
        // Check for conflicts
        $conflicts = $this->detectConflicts(
            $params['judge_id'],
            $params['hearing_date'],
            $params['start_time'],
            $params['end_time'],
            $params['courtroom']
        );

        if (!empty($conflicts)) {
            return ['error' => 'Scheduling conflict detected', 'conflicts' => $conflicts];
        }

        return DB::transaction(function () use ($case, $params) {
            $hearing = Hearing::create([
                'case_id' => $case->id,
                'judge_id' => $params['judge_id'],
                'courtroom' => $params['courtroom'],
                'hearing_date' => $params['hearing_date'],
                'start_time' => $params['start_time'],
                'end_time' => $params['end_time'],
                'status' => 'scheduled',
            ]);

            $case->update([
                'status' => 'scheduled',
                'assigned_judge_id' => $params['judge_id'],
            ]);

            return $hearing;
        });
    }

    /**
     * Find the most suitable judge for a case.
     */
    private function findSuitableJudge(CourtCase $case): ?User
    {
        $query = User::judges();

        // Prefer judges with matching specialization
        if ($case->case_type) {
            $query->orderByRaw("CASE WHEN specialization = ? THEN 0 ELSE 1 END", [$case->case_type]);
        }

        // Prefer judges with fewer active cases (load balancing)
        $judges = $query->withCount(['assignedCases' => function ($q) {
            $q->whereIn('status', ['pending', 'scheduled']);
        }])->orderBy('assigned_cases_count')->get();

        // Find first judge with availability
        foreach ($judges as $judge) {
            $hasAvailability = JudgeAvailability::forJudge($judge->id)
                ->available()
                ->where('date', '>=', now()->toDateString())
                ->exists();

            if ($hasAvailability) {
                return $judge;
            }
        }

        // Fallback: return judge with least cases even without explicit availability
        return $judges->first();
    }

    /**
     * Find an available time slot for a judge and case.
     */
    private function findAvailableSlot(User $judge, CourtCase $case): ?array
    {
        $duration = (float) $case->estimated_duration;
        $durationMinutes = (int) ($duration * 60);

        // Search next 30 days
        for ($day = 0; $day < 30; $day++) {
            $date = now()->addDays($day)->toDateString();

            // Get judge availability for this date
            $availability = JudgeAvailability::forJudge($judge->id)
                ->forDate($date)
                ->available()
                ->first();

            $startTime = $availability ? $availability->start_time : '09:00';
            $endTime = $availability ? $availability->end_time : '17:00';

            // Get existing hearings for this judge on this date
            $existingHearings = Hearing::forJudge($judge->id)
                ->forDate($date)
                ->scheduled()
                ->orderBy('start_time')
                ->get();

            // Find gap in schedule
            $slot = $this->findGapInSchedule(
                $startTime,
                $endTime,
                $existingHearings,
                $durationMinutes,
                $date
            );

            if ($slot) {
                return $slot;
            }
        }

        return null;
    }

    /**
     * Find a gap in an existing schedule.
     */
    private function findGapInSchedule(
        string $dayStart,
        string $dayEnd,
        $existingHearings,
        int $durationMinutes,
        string $date
    ): ?array {
        $current = Carbon::createFromFormat('H:i', substr($dayStart, 0, 5));
        $end = Carbon::createFromFormat('H:i', substr($dayEnd, 0, 5));

        foreach ($existingHearings as $hearing) {
            $hearingStart = Carbon::createFromFormat('H:i', substr($hearing->start_time, 0, 5));
            $gapMinutes = $current->diffInMinutes($hearingStart);

            if ($gapMinutes >= $durationMinutes) {
                $slotEnd = $current->copy()->addMinutes($durationMinutes);
                $courtroom = $this->findAvailableCourtroom($date, $current->format('H:i'), $slotEnd->format('H:i'));

                return [
                    'date' => $date,
                    'start_time' => $current->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'courtroom' => $courtroom,
                ];
            }

            $current = Carbon::createFromFormat('H:i', substr($hearing->end_time, 0, 5));
        }

        // Check remaining time after last hearing
        $remainingMinutes = $current->diffInMinutes($end);
        if ($remainingMinutes >= $durationMinutes) {
            $slotEnd = $current->copy()->addMinutes($durationMinutes);
            $courtroom = $this->findAvailableCourtroom($date, $current->format('H:i'), $slotEnd->format('H:i'));

            return [
                'date' => $date,
                'start_time' => $current->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'courtroom' => $courtroom,
            ];
        }

        return null;
    }

    /**
     * Find an available courtroom for a given date/time.
     */
    private function findAvailableCourtroom(string $date, string $startTime, string $endTime): string
    {
        foreach (self::COURTROOMS as $courtroom) {
            $conflict = Hearing::where('hearing_date', $date)
                ->where('courtroom', $courtroom)
                ->where('status', 'scheduled')
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                    });
                })
                ->exists();

            if (!$conflict) {
                return $courtroom;
            }
        }

        return self::COURTROOMS[0]; // Fallback
    }

    /**
     * Detect scheduling conflicts.
     */
    public function detectConflicts(
        int $judgeId,
        string $date,
        string $startTime,
        string $endTime,
        string $courtroom,
        ?int $excludeHearingId = null
    ): array {
        $conflicts = [];

        // Check judge conflict
        $judgeConflict = Hearing::forJudge($judgeId)
            ->forDate($date)
            ->scheduled()
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->when($excludeHearingId, fn($q) => $q->where('id', '!=', $excludeHearingId))
            ->with('courtCase')
            ->get();

        if ($judgeConflict->isNotEmpty()) {
            $conflicts['judge'] = $judgeConflict->map(fn($h) => [
                'hearing_id' => $h->id,
                'case_number' => $h->courtCase->case_number ?? 'N/A',
                'time' => $h->start_time . ' - ' . $h->end_time,
            ])->toArray();
        }

        // Check courtroom conflict
        $courtroomConflict = Hearing::where('hearing_date', $date)
            ->where('courtroom', $courtroom)
            ->scheduled()
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->when($excludeHearingId, fn($q) => $q->where('id', '!=', $excludeHearingId))
            ->with('courtCase')
            ->get();

        if ($courtroomConflict->isNotEmpty()) {
            $conflicts['courtroom'] = $courtroomConflict->map(fn($h) => [
                'hearing_id' => $h->id,
                'case_number' => $h->courtCase->case_number ?? 'N/A',
                'time' => $h->start_time . ' - ' . $h->end_time,
            ])->toArray();
        }

        return $conflicts;
    }
}
