<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Http\Resources\CaseResource;
use App\Models\CourtCase;
use App\Repositories\CaseRepository;
use App\Services\CaseClassificationService;
use App\Services\PriorityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function __construct(
        private readonly CaseRepository $caseRepository,
        private readonly CaseClassificationService $classificationService,
        private readonly PriorityService $priorityService
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', CourtCase::class);
        $filters = $request->only([
        $cases = $this->caseRepository->all($request->only([
            'track',
            'status',
            'urgency_level',
            'case_type',
            'assigned_judge_id',
            'search',
            'sort_by',
            'sort_dir',
            'per_page',
        ]);

        if ($request->user()->isJudge()) {
            $filters['assigned_judge_id'] = $request->user()->id;
        }

        $cases = $this->caseRepository->all($filters);
        return CaseResource::collection($cases);
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'case_type' => ['required', 'string', 'max:100'],
            'urgency_level' => ['required', 'integer', 'min:1', 'max:5'],
            'estimated_duration' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'complexity_level' => ['nullable', 'string', 'max:100'],
        ]);

        $payload = $request->all();
        $track = $this->classificationService->classify($payload);
        $priority = $this->priorityService->previewScore([
            ...$payload,
            'track' => $track,
        ]);

        return response()->json([
            'predicted_track' => $track,
            'priority_score' => $priority,
            'estimated_duration' => (float) $payload['estimated_duration'],
        ]);
    }

    public function store(StoreCaseRequest $request): JsonResponse
    {
        $this->authorize('create', CourtCase::class);

        $payload = $request->validated();
        $track = $this->classificationService->classify($payload);
        $priority = $this->priorityService->previewScore([
            ...$payload,
            'track' => $track,
        ]);

        $case = $this->caseRepository->create([
            ...$payload,
            'case_number' => CourtCase::generateCaseNumber(),
            'track' => $track,
            'priority_score' => $priority,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Case created successfully.',
            'data' => new CaseResource($case->load('assignedJudge')),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $case = $this->caseRepository->find($id);

        if (!$case) {
            return response()->json(['message' => 'Case not found.'], 404);
        }

        $this->authorize('view', $case);

        return response()->json(['data' => new CaseResource($case)]);
    }

    public function update(UpdateCaseRequest $request, int $id): JsonResponse
    {
        $case = $this->caseRepository->find($id);

        if (!$case) {
            return response()->json(['message' => 'Case not found.'], 404);
        }

        $this->authorize('update', $case);

        $payload = $request->validated();
        if (array_key_exists('assigned_judge_id', $payload) || array_key_exists('status', $payload)) {
            if (!$request->user()->isAdmin() && $request->user()->isJudge()) {
                unset($payload['assigned_judge_id']);
            }
        }

        if (
            isset($payload['urgency_level']) ||
            isset($payload['estimated_duration']) ||
            isset($payload['case_type'])
        ) {
            $track = $this->classificationService->classify([
                'case_type' => $payload['case_type'] ?? $case->case_type,
                'urgency_level' => $payload['urgency_level'] ?? $case->urgency_level,
                'estimated_duration' => $payload['estimated_duration'] ?? $case->estimated_duration,
            ]);
            $payload['track'] = $track;
            $payload['priority_score'] = $this->priorityService->previewScore([
                'urgency_level' => $payload['urgency_level'] ?? $case->urgency_level,
                'track' => $track,
            ]);
        }

        $updated = $this->caseRepository->update($case, $payload);

        return response()->json([
            'message' => 'Case updated successfully.',
            'data' => new CaseResource($updated->load(['assignedJudge', 'hearings.judge'])),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $case = $this->caseRepository->find($id);

        if (!$case) {
            return response()->json(['message' => 'Case not found.'], 404);
        }

        $this->authorize('delete', $case);
        $this->caseRepository->delete($case);

        return response()->json(['message' => 'Case deleted successfully.']);
    }
}
