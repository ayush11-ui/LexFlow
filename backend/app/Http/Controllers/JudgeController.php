<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertAvailabilityRequest;
use App\Http\Resources\CaseResource;
use App\Http\Resources\JudgeResource;
use App\Repositories\CaseRepository;
use App\Repositories\JudgeRepository;
use Illuminate\Http\JsonResponse;

class JudgeController extends Controller
{
    public function __construct(
        private readonly JudgeRepository $judgeRepository,
        private readonly CaseRepository $caseRepository
    ) {
    }

    public function index()
    {
        return JudgeResource::collection($this->judgeRepository->all());
    }

    public function show(int $id): JsonResponse
    {
        $judge = $this->judgeRepository->find($id);

        if (!$judge) {
            return response()->json(['message' => 'Judge not found.'], 404);
        }

        return response()->json([
            'data' => new JudgeResource($judge),
            'cases' => CaseResource::collection($this->caseRepository->getForJudge($id)),
        ]);
    }

    public function upsertAvailability(UpsertAvailabilityRequest $request, int $id): JsonResponse
    {
        $availability = $this->judgeRepository->setAvailability($id, $request->validated());

        return response()->json([
            'message' => 'Availability updated successfully.',
            'data' => $availability,
        ]);
    }
}
