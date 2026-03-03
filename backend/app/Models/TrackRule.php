<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_type',
        'document_threshold',
        'duration_threshold',
        'assigned_track',
    ];

    protected function casts(): array
    {
        return [
            'document_threshold' => 'integer',
            'duration_threshold' => 'decimal:2',
        ];
    }

    public function scopeForCaseType($query, string $caseType)
    {
        return $query->where('case_type', $caseType);
    }
}
