<?php

namespace App\Services;

use App\Models\CourtCase;
use App\Models\TrackRule;

class CaseClassificationService
{
    /**
     * Classify a case into a track (fast, standard, complex).
     */
    public function classify(array $caseData): string
    {
        // Check for rule overrides first
        $ruleOverride = $this->checkTrackRules($caseData);
        if ($ruleOverride) {
            return $ruleOverride;
        }

        // Default classification logic
        return $this->applyDefaultRules($caseData);
    }

    /**
     * Classify and update an existing case.
     */
    public function classifyCase(CourtCase $case): CourtCase
    {
        $track = $this->classify([
            'case_type' => $case->case_type,
            'urgency_level' => $case->urgency_level,
            'estimated_duration' => $case->estimated_duration,
            'complexity_level' => $case->complexity_level,
        ]);

        $case->update(['track' => $track]);

        return $case;
    }

    /**
     * Check track_rules table for overrides.
     */
    private function checkTrackRules(array $caseData): ?string
    {
        $rule = TrackRule::forCaseType($caseData['case_type'] ?? '')->first();

        if (!$rule) {
            return null;
        }

        $duration = (float) ($caseData['estimated_duration'] ?? 0);

        if ($duration <= $rule->duration_threshold) {
            return $rule->assigned_track;
        }

        return null;
    }

    /**
     * Apply default classification rules.
     *
     * IF urgency_level >= 4 AND estimated_duration < 2 hours → Fast Track
     * IF estimated_duration > 4 hours → Complex Track
     * ELSE → Standard Track
     */
    private function applyDefaultRules(array $caseData): string
    {
        $urgency = (int) ($caseData['urgency_level'] ?? 1);
        $duration = (float) ($caseData['estimated_duration'] ?? 1);

        if ($urgency >= 4 && $duration < 2) {
            return 'fast';
        }

        if ($duration > 4) {
            return 'complex';
        }

        return 'standard';
    }

    /**
     * Batch reclassify all pending cases.
     */
    public function reclassifyAll(): int
    {
        $cases = CourtCase::pending()->get();
        $count = 0;

        foreach ($cases as $case) {
            $this->classifyCase($case);
            $count++;
        }

        return $count;
    }
}
