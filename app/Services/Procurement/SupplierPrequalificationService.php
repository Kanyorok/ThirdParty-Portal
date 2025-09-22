<?php

namespace App\Services\Procurement;

use App\Models\Procurement\Prequalification\PrequalificationEvaluation;
use App\Models\Procurement\Prequalification\PrequalificationSection;
use Illuminate\Database\Eloquent\Collection;

class SupplierPrequalificationService
{
    /**
     * Calculates the weighted score for a single evaluation (legacy method for backward compatibility).
     *
     * @param PrequalificationEvaluation $evaluation
     * @return float
     */
    public function getWeightedScore(PrequalificationEvaluation $evaluation): float
    {
        // For backward compatibility, return the criterion-level contribution
        return $this->calculateCriterionContribution($evaluation);
    }

    /**
     * Calculates the contribution of a single criterion within its section.
     *
     * @param PrequalificationEvaluation $evaluation
     * @return float
     */
    public function calculateCriterionContribution(PrequalificationEvaluation $evaluation): float
    {
        $criteriaScore = $evaluation->Score ?? 0;
        $criteriaMaxScore = $evaluation->MaxScore ?? 0;

        if ($criteriaMaxScore > 0) {
            // Get criterion weight from PrequalificationCriteria
            $prequalificationCriteria = $this->getPrequalificationCriteria($evaluation);
            $criteriaWeight = $prequalificationCriteria ? $prequalificationCriteria->Weight : 0;

            return ($criteriaScore / $criteriaMaxScore) * $criteriaWeight;
        }

        return 0.0;
    }

    /**
     * Calculates the weighted score for an entire section based on all its criteria.
     *
     * @param Collection $sectionEvaluations
     * @param int $sectionId
     * @return float
     */
    public function calculateSectionScore(Collection $sectionEvaluations, int $sectionId): float
    {
        if ($sectionEvaluations->isEmpty()) {
            return 0.0;
        }

        // Calculate section raw score (sum of all criterion contributions)
        $sectionRawScore = 0.0;
        $sectionMaxScore = 0.0;

        foreach ($sectionEvaluations as $evaluation) {
            $criterionContribution = $this->calculateCriterionContribution($evaluation);
            $sectionRawScore += $criterionContribution;

            // Get criterion weight for max score calculation
            $prequalificationCriteria = $this->getPrequalificationCriteria($evaluation);
            if ($prequalificationCriteria) {
                $sectionMaxScore += $prequalificationCriteria->Weight;
            }
        }

        // Calculate section percentage
        $sectionPercentage = 0.0;
        if ($sectionMaxScore > 0) {
            $sectionPercentage = ($sectionRawScore / $sectionMaxScore) * 100.0;
        }

        // Apply section weight
        $sectionWeight = $this->getSectionWeight($sectionId, $sectionEvaluations->first());
        $sectionWeightedScore = $sectionPercentage * ($sectionWeight / 100.0);

        return $sectionWeightedScore;
    }

    /**
     * Calculates the total weighted score for a collection of evaluations using section-based weighting.
     *
     * @param Collection|PrequalificationEvaluation[] $evaluations
     * @return float
     */
    public function calculateTotalScore(Collection $evaluations): float
    {
        if ($evaluations->isEmpty()) {
            return 0.0;
        }

        // Group evaluations by section
        $evaluationsBySection = $evaluations->groupBy('SectionID');

        $totalScore = 0.0;

        foreach ($evaluationsBySection as $sectionId => $sectionEvaluations) {
            $sectionScore = $this->calculateSectionScore($sectionEvaluations, $sectionId);
            $totalScore += $sectionScore;
        }

        return $totalScore;
    }

    /**
     * Gets the PrequalificationCriteria for an evaluation to access weights.
     *
     * @param PrequalificationEvaluation $evaluation
     * @return \App\Models\Procurement\Prequalification\PrequalificationCriteria|null
     */
    private function getPrequalificationCriteria(PrequalificationEvaluation $evaluation): ?\App\Models\Procurement\Prequalification\PrequalificationCriteria
    {
        // Find the prequalification criteria that matches this evaluation
        return \App\Models\Procurement\Prequalification\PrequalificationCriteria::where('CriteriaId', $evaluation->CriteriaID)
            ->whereHas('round.applications', function ($query) use ($evaluation) {
                $query->where('ApplicationID', $evaluation->ApplicationID);
            })
            ->first();
    }

    /**
     * Gets the section weight for a given section ID.
     *
     * @param int $sectionId
     * @param PrequalificationEvaluation $evaluation
     * @return float
     */
    private function getSectionWeight(int $sectionId, PrequalificationEvaluation $evaluation): float
    {
        // Find the prequalification section weight
        $prequalificationSection = \App\Models\Procurement\Prequalification\PrequalificationSection::where('SectionId', $sectionId)
            ->whereHas('round.applications', function ($query) use ($evaluation) {
                $query->where('ApplicationID', $evaluation->ApplicationID);
            })
            ->first();

        return $prequalificationSection ? $prequalificationSection->Weight : 0.0;
    }
}
