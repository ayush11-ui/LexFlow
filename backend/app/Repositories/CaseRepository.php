<?php

namespace App\Repositories;

use App\Models\CourtCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CaseRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = CourtCase::with('assignedJudge');

        if (!empty($filters['track'])) {
            $query->byTrack($filters['track']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['urgency_level'])) {
            $query->where('urgency_level', $filters['urgency_level']);
        }

        if (!empty($filters['case_type'])) {
            $query->where('case_type', $filters['case_type']);
        }

        if (!empty($filters['assigned_judge_id'])) {
            $query->where('assigned_judge_id', $filters['assigned_judge_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'ilike', "%{$search}%")
                  ->orWhere('title', 'ilike', "%{$search}%");
            });
        }

        $allowedSortColumns = ['priority_score', 'created_at', 'urgency_level', 'case_number'];
        $sortBy = in_array($filters['sort_by'] ?? '', $allowedSortColumns, true)
            ? $filters['sort_by']
            : 'priority_score';
        $sortDir = strtolower((string) ($filters['sort_dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir)
            ->paginate($filters['per_page'] ?? 15);
    }

    public function find(int $id): ?CourtCase
    {
        return CourtCase::with(['assignedJudge', 'hearings.judge'])->find($id);
    }

    public function findByNumber(string $caseNumber): ?CourtCase
    {
        return CourtCase::where('case_number', $caseNumber)
            ->with(['assignedJudge', 'hearings.judge'])
            ->first();
    }

    public function create(array $data): CourtCase
    {
        return CourtCase::create($data);
    }

    public function update(CourtCase $case, array $data): CourtCase
    {
        $case->update($data);
        return $case->fresh();
    }

    public function delete(CourtCase $case): bool
    {
        return $case->delete();
    }

    public function getStats(): array
    {
        return [
            'total' => CourtCase::count(),
            'pending' => CourtCase::pending()->count(),
            'scheduled' => CourtCase::scheduled()->count(),
            'completed' => CourtCase::completed()->count(),
            'fast_track' => CourtCase::byTrack('fast')->whereIn('status', ['pending', 'scheduled'])->count(),
            'standard_track' => CourtCase::byTrack('standard')->whereIn('status', ['pending', 'scheduled'])->count(),
            'complex_track' => CourtCase::byTrack('complex')->whereIn('status', ['pending', 'scheduled'])->count(),
        ];
    }

    public function getForJudge(int $judgeId): Collection
    {
        return CourtCase::where('assigned_judge_id', $judgeId)
            ->with('hearings')
            ->orderByDesc('priority_score')
            ->get();
    }
}
