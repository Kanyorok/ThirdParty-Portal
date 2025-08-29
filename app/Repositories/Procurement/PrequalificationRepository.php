<?php

namespace App\Repositories\Procurement;

use App\Models\Procurement\Criteria;
use App\Models\Procurement\PrequalificationApplication;
use App\Models\Procurement\PrequalificationCriteria;
use App\Models\Procurement\PrequalificationEvaluation;
use App\Models\Procurement\PrequalificationRound;
use App\Models\Procurement\PrequalificationSection;

class PrequalificationRepository
{
    public function createPrequalificationRound(array $attributes): PrequalificationRound
    {
        return PrequalificationRound::create($attributes);
    }

    public function getPrequalificationRound(int $id, array $with = []): ?PrequalificationRound
    {
        return PrequalificationRound::with($with)->find($id);
    }

    public function createApplication(array $attributes): PrequalificationApplication
    {
        return PrequalificationApplication::create($attributes);
    }

    public function getApplication(int $id, array $with = []): ?PrequalificationApplication
    {
        return PrequalificationApplication::with($with)->find($id);
    }

    public function createEvaluation(array $attributes): PrequalificationEvaluation
    {
        return PrequalificationEvaluation::create($attributes);
    }

    public function getEvaluationsByCriteria(int $applicationId, int $evaluatorId, int $criteriaId): ?PrequalificationEvaluation
    {
        return PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->where('EvaluatorID', $evaluatorId)
            ->where('CriteriaID', $criteriaId)
            ->first();
    }

    public function createOrUpdatePrequalificationSection(array $where, array $attributes): PrequalificationSection
    {
        return PrequalificationSection::updateOrCreate($where, $attributes);
    }

    public function createOrUpdatePrequalificationCriteria(array $where, array $attributes): PrequalificationCriteria
    {
        return PrequalificationCriteria::updateOrCreate($where, $attributes);
    }

    public function deletePrequalificationSectionsByRoundId(int $roundId): void
    {
        PrequalificationSection::where('RoundId', $roundId)->delete();
    }

    public function deletePrequalificationCriteriaByRoundId(int $roundId): void
    {
        PrequalificationCriteria::where('RoundId', $roundId)->delete();
    }

    public function getCriteriaBySection(int $sectionId): \Illuminate\Database\Eloquent\Collection
    {
        return Criteria::where('SectionId', $sectionId)->get();
    }
}
