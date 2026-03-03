<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'specialization',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function assignedCases()
    {
        return $this->hasMany(CourtCase::class, 'assigned_judge_id');
    }

    public function hearings()
    {
        return $this->hasMany(Hearing::class, 'judge_id');
    }

    public function availability()
    {
        return $this->hasMany(JudgeAvailability::class, 'judge_id');
    }

    // Scopes
    public function scopeJudges($query)
    {
        return $query->where('role', 'judge');
    }

    public function scopeClerks($query)
    {
        return $query->where('role', 'clerk');
    }

    // Helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClerk(): bool
    {
        return $this->role === 'clerk';
    }

    public function isJudge(): bool
    {
        return $this->role === 'judge';
    }

    public function activeCasesCount(): int
    {
        return $this->assignedCases()
            ->whereIn('status', ['pending', 'scheduled'])
            ->count();
    }
}
