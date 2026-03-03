<?php

namespace App\Services;

use App\Models\CourtCase;

class PriorityService
{
    private const URGENCY_WEIGHT = 0.4;
    private const AGE_WEIGHT = 0.3;
    private const TRACK_WEIGHT = 0.3;

    private const TRACK_VALUES = [
        'fast' => 3,
        'standard' => 2,
        'complex' => 1,
    ];

    /**
     * Calculate priority score for a case.
     *
     * Formula: (urgency_level × 0.4) + (days_since_creation × 0.3) + (track_weight × 0.3)
     */
    public function calculateScore(CourtCase $case): float
    {
        $urgencyComponent = $case->urgency_level * self::URGENCY_WEIGHT;
        $ageComponent = $case->getDaysSinceCreation() * self::AGE_WEIGHT;
        $trackComponent = (self::TRACK_VALUES[$case->track] ?? 2) * self::TRACK_WEIGHT;

        return round($urgencyComponent + $ageComponent + $trackComponent, 2);
    }

    /**
     * Calculate and store priority score.
     */
    public function updatePriority(CourtCase $case): CourtCase
    {
        $score = $this->calculateScore($case);
        $case->update(['priority_score' => $score]);

        return $case;
    }

    /**
     * Preview priority score without saving (for case intake preview).
     */
    public function previewScore(array $caseData): float
    {
        $urgency = (int) ($caseData['urgency_level'] ?? 1);
        $track = $caseData['track'] ?? 'standard';
        $daysOld = 0; // New case

        $urgencyComponent = $urgency * self::URGENCY_WEIGHT;
        $ageComponent = $daysOld * self::AGE_WEIGHT;
        $trackComponent = (self::TRACK_VALUES[$track] ?? 2) * self::TRACK_WEIGHT;

        return round($urgencyComponent + $ageComponent + $trackComponent, 2);
    }

    /**
     * Recalculate priorities for all active cases.
     */
    public function recalculateAll(): int
    {
        $cases = CourtCase::whereIn('status', ['pending', 'scheduled'])->get();
        $count = 0;

        foreach ($cases as $case) {
            $this->updatePriority($case);
            $count++;
        }

        return $count;
    }
}
