<?php

namespace App\Services\Procurement;

use App\Models\Procurement\PrequalificationApplication;
use App\Models\Procurement\PrequalificationEvaluation;
use App\Models\Procurement\PrequalificationRound;
use App\Repositories\Procurement\PrequalificationRepository;

class PrequalificationService
{
    protected PrequalificationRepository $PrequalificationRepository;

    public function __construct(PrequalificationRepository $PrequalificationRepository)
    {
        $this->PrequalificationRepository = $PrequalificationRepository;
    }

    public function createPrequalificationRound(array $data): PrequalificationRound
    {
        return $this->PrequalificationRepository->createPrequalificationRound($data);
    }

    public function getPrequalificationRound(int $id): ?PrequalificationRound
    {
        return $this->PrequalificationRepository->getPrequalificationRound($id, ['sections.criteria']);
    }

    public function createApplication(array $data): PrequalificationApplication
    {
        return $this->PrequalificationRepository->createApplication($data);
    }

    public function createEvaluation(array $data): PrequalificationEvaluation
    {
        return $this->PrequalificationRepository->createEvaluation($data);
    }

    public function checkExistingEvaluation(int $applicationId, int $evaluatorId, int $criteriaId): bool
    {
        return $this->PrequalificationRepository->getEvaluationsByCriteria($applicationId, $evaluatorId, $criteriaId) !== null;
    }

    public function calculateFinalScore(PrequalificationApplication $application): float
    {
        $FinalScore = 0.0;
        $application->load('evaluations', 'round.sections.criteria');
        $Sections = $application->round->sections;

        foreach ($Sections as $Section) {
            $SectionTotalScore = 0.0;
            $SectionMaxScore = 0.0;

            $criteriaIds = $Section->criteria->pluck('CriteriaID');
            $Evaluations = $application->evaluations->whereIn('CriteriaID', $criteriaIds);

            foreach ($Evaluations as $Evaluation) {
                $criteria = $Section->criteria->firstWhere('CriteriaID', $Evaluation->CriteriaID);
                if ($criteria) {
                    $SectionTotalScore += $Evaluation->Score;
                    $SectionMaxScore += $criteria->MaxScore;
                }
            }

            if ($SectionMaxScore > 0) {
                $SectionPercentage = $SectionTotalScore / $SectionMaxScore;
                $FinalScore += $SectionPercentage * ($Section->Weight / 100);
            }
        }

        return $FinalScore * 100;
    }

    public function updateApplicationStatus(PrequalificationApplication $application, int $threshold): PrequalificationApplication
    {
        $FinalScore = $this->calculateFinalScore($application);
        $Status = ($FinalScore >= $threshold) ? 'Approved' : 'Rejected';

        $application->Status = $Status;
        $application->FinalScore = $FinalScore;
        $application->save();

        return $application;
    }

    public function createPrequalificationStructure(int $roundId, array $sectionIds, array $weights, int $userId): void
    {
        foreach ($sectionIds as $sectionId) {
            $weight = $weights[$sectionId] ?? 0;
            $this->PrequalificationRepository->createOrUpdatePrequalificationSection(
                ['RoundId' => $roundId, 'SectionId' => $sectionId],
                [
                    'Weight' => floatval($weight),
                    'CreatedBy' => $userId,
                    'ModifiedBy' => $userId,
                ]
            );

            $criteriaItems = $this->PrequalificationRepository->getCriteriaBySection($sectionId);
            foreach ($criteriaItems as $criteria) {
                $this->PrequalificationRepository->createOrUpdatePrequalificationCriteria(
                    ['RoundId' => $roundId, 'SectionId' => $sectionId, 'CriteriaId' => $criteria->id],
                    [
                        'Included' => true,
                        'CreatedBy' => $userId,
                        'ModifiedBy' => $userId,
                    ]
                );
            }
        }
    }

    public function updatePrequalificationStructure(int $roundId, array $sectionIds, array $weights, int $userId): void
    {
        $this->PrequalificationRepository->deletePrequalificationSectionsByRoundId($roundId);
        $this->PrequalificationRepository->deletePrequalificationCriteriaByRoundId($roundId);
        $this->createPrequalificationStructure($roundId, $sectionIds, $weights, $userId);
    }

    public function deletePrequalificationStructure(int $roundId): void
    {
        $this->PrequalificationRepository->deletePrequalificationSectionsByRoundId($roundId);
        $this->PrequalificationRepository->deletePrequalificationCriteriaByRoundId($roundId);
    }
}
