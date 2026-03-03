<?php

namespace App\Http\Controllers;

use App\Models\CourtCase;
use App\Models\Hearing;
use App\Repositories\CaseRepository;
use App\Repositories\JudgeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly CaseRepository $caseRepository,
        private readonly JudgeRepository $judgeRepository
    ) {
    }

    public function overview(): JsonResponse
    {
        $stats = $this->caseRepository->getStats();
        $avgDisposalDays = CourtCase::query()
            ->where('status', 'completed')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - created_at)) / 86400) as avg_days')
            ->value('avg_days');

        return response()->json([
            'total_active_cases' => ($stats['pending'] ?? 0) + ($stats['scheduled'] ?? 0),
            'fast_track_cases' => $stats['fast_track'] ?? 0,
            'standard_track_cases' => $stats['standard_track'] ?? 0,
            'complex_track_cases' => $stats['complex_track'] ?? 0,
            'average_disposal_time_days' => round((float) ($avgDisposalDays ?? 0), 2),
            'scheduling_efficiency' => $stats['total'] > 0
                ? round((($stats['scheduled'] + $stats['completed']) / $stats['total']) * 100, 2)
                : 0,
        ]);
    }

    public function backlog(Request $request): JsonResponse
    {
        $days = max(7, min(120, (int) $request->query('days', 30)));
        $start = now()->subDays($days)->toDateString();

        $data = CourtCase::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get();

        return response()->json(['trend' => $data]);
    }

    public function workload(): JsonResponse
    {
        return response()->json([
            'judges' => $this->judgeRepository->getWorkload(),
            'aging_distribution' => [
                '0_30_days' => CourtCase::query()->where('created_at', '>=', now()->subDays(30))->count(),
                '31_90_days' => CourtCase::query()
                    ->whereBetween('created_at', [now()->subDays(90), now()->subDays(31)])
                    ->count(),
                '90_plus_days' => CourtCase::query()->where('created_at', '<', now()->subDays(90))->count(),
            ],
            'case_distribution' => CourtCase::query()
                ->selectRaw('track, COUNT(*) as total')
                ->groupBy('track')
                ->pluck('total', 'track'),
            'hearings_per_week' => Hearing::query()
                ->where('hearing_date', '>=', now()->subWeeks(6)->toDateString())
                ->selectRaw("TO_CHAR(hearing_date, 'IYYY-IW') as week, COUNT(*) as total")
                ->groupBy('week')
                ->orderBy('week')
                ->get(),
        ]);
    }
}
