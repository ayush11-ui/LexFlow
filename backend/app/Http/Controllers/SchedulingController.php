<?php

namespace App\Http\Controllers;

use App\Http\Requests\AutoScheduleRequest;
use App\Http\Requests\ScheduleHearingRequest;
use App\Http\Resources\HearingResource;
use App\Models\CourtCase;
use App\Repositories\HearingRepository;
use App\Services\SchedulingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchedulingController extends Controller
{
    public function __construct(
        private readonly SchedulingService $schedulingService,
        private readonly HearingRepository $hearingRepository
    ) {
    }

    public function auto(AutoScheduleRequest $request): JsonResponse
    {
        if ($request->filled('case_id')) {
            $case = CourtCase::query()->findOrFail($request->integer('case_id'));
            $hearing = $this->schedulingService->autoSchedule($case);

            if (!$hearing) {
                return response()->json(['message' => 'No scheduling slot found for the case.'], 422);
            }

            return response()->json([
                'message' => 'Case auto-scheduled successfully.',
                'data' => new HearingResource($hearing->load(['judge', 'courtCase'])),
            ]);
        }

        return response()->json([
            'message' => 'Auto-scheduling execution completed.',
            'result' => $this->schedulingService->autoScheduleAll(),
        ]);
    }

    public function manual(ScheduleHearingRequest $request): JsonResponse
    {
        $case = CourtCase::query()->findOrFail($request->integer('case_id'));
        $result = $this->schedulingService->manualSchedule($case, $request->validated());

        if (is_array($result) && isset($result['error'])) {
            return response()->json($result, 422);
        }

        return response()->json([
            'message' => 'Manual scheduling successful.',
            'data' => new HearingResource($result->load(['judge', 'courtCase'])),
        ]);
    }

    public function events(Request $request)
    {
        $events = $this->hearingRepository->getCalendarEvents($request->only([
            'judge_id',
            'start',
            'end',
        ]));

        return HearingResource::collection($events);
    }
}
