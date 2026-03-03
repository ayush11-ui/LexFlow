<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JudgeAvailability extends Model
{
    use HasFactory;

    protected $table = 'judges_availability';

    protected $fillable = [
        'judge_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_available' => 'boolean',
        ];
    }

    public function judge()
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForJudge($query, int $judgeId)
    {
        return $query->where('judge_id', $judgeId);
    }
}
