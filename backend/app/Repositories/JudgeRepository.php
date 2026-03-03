<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\JudgeAvailability;
use Illuminate\Database\Eloquent\Collection;

class JudgeRepository
{
    public function all(): Collection
    {
        return User::judges()
            ->withCount(['assignedCases' => function ($q) {
                $q->whereIn('status', ['pending', 'scheduled']);
            }])
            ->withCount('hearings')
            ->get();
    }

    public function find(int $id): ?User
    {
        return User::judges()
            ->withCount(['assignedCases' => function ($q) {
                $q->whereIn('status', ['pending', 'scheduled']);
            }])
            ->with(['availability' => function ($q) {
                $q->where('date', '>=', now()->toDateString())
                  ->orderBy('date');
            }])
            ->find($id);
    }

    public function getWorkload(): Collection
    {
        return User::judges()
            ->withCount([
                'assignedCases as active_cases_count' => function ($q) {
                    $q->whereIn('status', ['pending', 'scheduled']);
                },
                'assignedCases as completed_cases_count' => function ($q) {
                    $q->where('status', 'completed');
                },
                'hearings as upcoming_hearings_count' => function ($q) {
                    $q->where('hearing_date', '>=', now()->toDateString())
                      ->where('status', 'scheduled');
                },
            ])
            ->get()
            ->map(function ($judge) {
                $maxCases = 20; // Configurable max caseload
                $judge->utilization = min(100, round(($judge->active_cases_count / $maxCases) * 100));
                return $judge;
            });
    }

    public function setAvailability(int $judgeId, array $data): JudgeAvailability
    {
        return JudgeAvailability::updateOrCreate(
            [
                'judge_id' => $judgeId,
                'date' => $data['date'],
            ],
            [
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'is_available' => $data['is_available'] ?? true,
            ]
        );
    }

    public function getAvailability(int $judgeId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = JudgeAvailability::forJudge($judgeId);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->orderBy('date')->get();
    }
}
