<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hearing extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'judge_id',
        'courtroom',
        'hearing_date',
        'start_time',
        'end_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'hearing_date' => 'date',
        ];
    }

    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class, 'case_id');
    }

    public function judge()
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeForJudge($query, int $judgeId)
    {
        return $query->where('judge_id', $judgeId);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('hearing_date', $date);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('hearing_date', '>=', now()->toDateString())
            ->where('status', 'scheduled')
            ->orderBy('hearing_date')
            ->orderBy('start_time');
    }
}
