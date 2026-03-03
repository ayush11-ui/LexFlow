<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtCase extends Model
{
    use HasFactory;

    protected $table = 'cases';

    protected $fillable = [
        'case_number',
        'title',
        'description',
        'case_type',
        'urgency_level',
        'complexity_level',
        'estimated_duration',
        'priority_score',
        'track',
        'status',
        'assigned_judge_id',
    ];

    protected function casts(): array
    {
        return [
            'urgency_level' => 'integer',
            'estimated_duration' => 'decimal:2',
            'priority_score' => 'decimal:2',
        ];
    }

    // Relationships
    public function assignedJudge()
    {
        return $this->belongsTo(User::class, 'assigned_judge_id');
    }

    public function hearings()
    {
        return $this->hasMany(Hearing::class, 'case_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByTrack($query, string $track)
    {
        return $query->where('track', $track);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_judge_id');
    }

    public function scopeHighPriority($query)
    {
        return $query->orderByDesc('priority_score');
    }

    // Helpers
    public function getTrackWeight(): int
    {
        return match ($this->track) {
            'fast' => 3,
            'standard' => 2,
            'complex' => 1,
            default => 2,
        };
    }

    public function getDaysSinceCreation(): int
    {
        return $this->created_at->diffInDays(now());
    }

    public static function generateCaseNumber(): string
    {
        $year = now()->format('Y');
        $latest = static::where('case_number', 'like', "LF-{$year}-%")
            ->orderByDesc('id')
            ->first();

        if ($latest) {
            $lastNum = (int) str_replace("LF-{$year}-", '', $latest->case_number);
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return sprintf("LF-%s-%05d", $year, $nextNum);
    }
}
