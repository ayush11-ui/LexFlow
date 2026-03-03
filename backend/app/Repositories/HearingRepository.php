<?php

namespace App\Repositories;

use App\Models\Hearing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class HearingRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Hearing::with(['courtCase', 'judge']);

        if (!empty($filters['judge_id'])) {
            $query->forJudge($filters['judge_id']);
        }

        if (!empty($filters['date'])) {
            $query->forDate($filters['date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('hearing_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('hearing_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('hearing_date')
            ->orderBy('start_time')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getCalendarEvents(array $filters = []): Collection
    {
        $query = Hearing::with(['courtCase', 'judge'])
            ->scheduled();

        if (!empty($filters['judge_id'])) {
            $query->forJudge($filters['judge_id']);
        }

        if (!empty($filters['start'])) {
            $query->where('hearing_date', '>=', $filters['start']);
        }

        if (!empty($filters['end'])) {
            $query->where('hearing_date', '<=', $filters['end']);
        }

        return $query->orderBy('hearing_date')
            ->orderBy('start_time')
            ->get();
    }

    public function find(int $id): ?Hearing
    {
        return Hearing::with(['courtCase', 'judge'])->find($id);
    }

    public function create(array $data): Hearing
    {
        return Hearing::create($data);
    }

    public function update(Hearing $hearing, array $data): Hearing
    {
        $hearing->update($data);
        return $hearing->fresh();
    }

    public function delete(Hearing $hearing): bool
    {
        return $hearing->delete();
    }

    public function getUpcomingForJudge(int $judgeId, int $limit = 10): Collection
    {
        return Hearing::forJudge($judgeId)
            ->upcoming()
            ->with('courtCase')
            ->limit($limit)
            ->get();
    }
}
