<?php

namespace App\Services\Procurement;

use App\Models\Procurement\Prequalification\PrequalificationEvaluation;
use Illuminate\Database\Eloquent\Collection;

class SupplierPrequalificationService
{
    /**
     * Calculates the weighted score for a single evaluation.
     *
     * @param PrequalificationEvaluation $evaluation
     * @return float
     */
    public function getWeightedScore(PrequalificationEvaluation $evaluation): float
    {
        $criteriaWeight = $evaluation->criteria->Weight ?? 0;
        $criteriaScore = $evaluation->Score ?? 0;
        $criteriaMaxScore = $evaluation->MaxScore ?? 0;

        if ($criteriaMaxScore > 0) {
            return ($criteriaScore / $criteriaMaxScore) * $criteriaWeight;
        }

        return 0.0;
    }

    /**
     * Calculates the total weighted score for a collection of evaluations.
     *
     * @param Collection|PrequalificationEvaluation[] $evaluations
     * @return float
     */
    public function calculateTotalScore(Collection $evaluations): float
    {
        $totalOverallScore = 0.0;

        foreach ($evaluations as $evaluation) {
            $totalOverallScore += $this->getWeightedScore($evaluation);
        }

        return $totalOverallScore;
    }
}
