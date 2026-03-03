<?php

namespace App\Policies;

use App\Models\CourtCase;
use App\Models\User;

class CourtCasePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'clerk', 'judge'], true);
    }

    public function view(User $user, CourtCase $courtCase): bool
    {
        if (in_array($user->role, ['admin', 'clerk'], true)) {
            return true;
        }

        return $user->role === 'judge' && (int) $courtCase->assigned_judge_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'clerk'], true);
    }

    public function update(User $user, CourtCase $courtCase): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'judge' && (int) $courtCase->assigned_judge_id === (int) $user->id;
    }

    public function delete(User $user, CourtCase $courtCase): bool
    {
        return $user->role === 'admin';
    }
}
