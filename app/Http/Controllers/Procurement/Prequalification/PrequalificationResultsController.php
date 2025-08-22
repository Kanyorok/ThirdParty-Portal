<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationEvaluation;
use App\Models\Procurement\Prequalification\PrequalificationResult;
use App\Services\Procurement\SupplierPrequalificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PrequalificationResultsController extends Controller
{
    /**
     * Generate and store the final results for a prequalification application.
     */
    public function generateResults(SupplierPrequalificationService $service, $applicationId): RedirectResponse
    {
        $evaluations = PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->with([
                'criteria.masterCriteria',
                'criteria.masterSection'
            ])
            ->get();

        $totalOverallScore = $service->calculateTotalScore($evaluations);

        // Set a passing threshold of 70%
        $passingThreshold = 70;
        $decision = ($totalOverallScore >= $passingThreshold) ? 'Passed' : 'Failed';

        PrequalificationResult::updateOrCreate(
            ['ApplicationID' => $applicationId],
            [
                'TotalScore' => $totalOverallScore,
                'Decision' => $decision,
                'ApprovalBy' => Auth::id(),
            ]
        );

        return redirect()->route('prequalification-evaluation.results', $applicationId)
            ->with('success', 'Prequalification results generated successfully!');
    }

    /**
     * Display the final prequalification results.
     */
    public function showResults(SupplierPrequalificationService $service, $applicationId): View
    {
        $application = PrequalificationApplication::with(['supplier'])->findOrFail($applicationId);

        $evaluations = PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->with(['criteria.masterCriteria', 'criteria.section.masterSection'])
            ->get();

        $result = PrequalificationResult::where('ApplicationID', $applicationId)->firstOrFail();

        $sections = [];
        foreach ($evaluations as $evaluation) {
            $sectionId = $evaluation->SectionID;

            if (!isset($sections[$sectionId])) {
                $sections[$sectionId] = [
                    'name' => optional($evaluation->criteria->section->masterSection)->SectionName,
                    'criteria' => [],
                    'sectionScore' => 0,
                    'sectionMaxScore' => 0,
                ];
            }

            $criteriaWeight = $evaluation->criteria->Weight ?? 0;
            $criteriaScore = $evaluation->Score ?? 0;
            $criteriaMaxScore = $evaluation->MaxScore ?? 0;

            $weightedScore = $service->getWeightedScore($evaluation);

            $sections[$sectionId]['criteria'][] = [
                'name' => optional($evaluation->criteria->masterCriteria)->MasterCriteriaName,
                'score' => $criteriaScore,
                'maxScore' => $criteriaMaxScore,
                'weight' => $criteriaWeight,
                'weightedScore' => $weightedScore,
                'remarks' => $evaluation->Remarks,
            ];

            $sections[$sectionId]['sectionScore'] += $weightedScore;
            $sections[$sectionId]['sectionMaxScore'] += $criteriaWeight;
        }

        return view('procurement.suppliers.prequalification.prequalification-evaluation.show_results', compact('application', 'sections', 'result'));
    }
}
