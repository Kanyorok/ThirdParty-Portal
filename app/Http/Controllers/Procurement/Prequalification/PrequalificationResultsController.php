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
     * Admin-only method to generate results for an application.
     */
    public function generateResults(SupplierPrequalificationService $service, $applicationId): \Illuminate\Http\RedirectResponse
    {
        // Check if user is authenticated (you can add more specific admin checks later)
        if (!Auth::check()) {
            abort(403, 'Unauthorized. Only authenticated users can generate results.');
        }

        $evaluations = PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->with(['criteria', 'evaluationSection'])
            ->get();

        if ($evaluations->isEmpty()) {
            return redirect()->back()->with('error', 'No evaluations found for this application.');
        }

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
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Prequalification results generated successfully!');
    }

    /**
     * Display the final prequalification results (only if they exist).
     */
    public function showResults(SupplierPrequalificationService $service, $applicationId): View
    {
        $application = PrequalificationApplication::with(['supplier', 'category'])->findOrFail($applicationId);

        // Check if results exist
        $result = PrequalificationResult::where('ApplicationID', $applicationId)->first();

        if (!$result) {
            // No results exist yet, show a message to evaluate first
            return view('procurement.suppliers.prequalification.prequalification-evaluation.no_results', compact('application'));
        }

        // Results exist, load evaluations with their criteria and sections
        $evaluations = PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->with(['criteria', 'evaluationSection'])
            ->get();

        // Load the round's prequalification sections with master relationships
        $round = $application->round;
        $prequalificationSections = $round->prequalificationSections()
            ->with(['masterSection', 'criteria.masterCriteria'])
            ->get()
            ->keyBy('SectionId');

        $result = PrequalificationResult::where('ApplicationID', $applicationId)->firstOrFail();

        $sections = [];
        $evaluationsBySection = $evaluations->groupBy('SectionID');

        foreach ($evaluationsBySection as $sectionId => $sectionEvaluations) {
            // Calculate section score using the new logic
            $sectionScore = $service->calculateSectionScore($sectionEvaluations, $sectionId);

            // Get section max score (sum of all criteria weights in the section)
            $sectionMaxScore = 0;
            if (isset($prequalificationSections[$sectionId])) {
                $sectionMaxScore = $prequalificationSections[$sectionId]->criteria->sum('Weight');
            }

            // Get the master section name from prequalification sections
            $masterSectionName = '';
            if (isset($prequalificationSections[$sectionId])) {
                $masterSectionName = $prequalificationSections[$sectionId]->masterSection->SectionName ?? '';
            } else {
                $masterSectionName = $sectionEvaluations->first()->evaluationSection->SectionName ?? '';
            }

            $sections[$sectionId] = [
                'name' => $masterSectionName,
                'criteria' => [],
                'sectionScore' => $sectionScore,
                'sectionMaxScore' => $sectionMaxScore,
            ];

            // Add individual criteria details
            foreach ($sectionEvaluations as $evaluation) {
                // Find the master criteria name and weight from prequalification sections
                $masterCriteriaName = '';
                $criteriaWeight = 0;
                if (isset($prequalificationSections[$sectionId])) {
                    $prequalificationCriteria = $prequalificationSections[$sectionId]->criteria
                        ->where('CriteriaId', $evaluation->CriteriaID)
                        ->first();
                    if ($prequalificationCriteria) {
                        $masterCriteriaName = $prequalificationCriteria->masterCriteria->CriteriaName ?? '';
                        $criteriaWeight = $prequalificationCriteria->Weight ?? 0;
                    }
                }
                if (empty($masterCriteriaName)) {
                    $masterCriteriaName = $evaluation->criteria->CriteriaName ?? '';
                }

                $criteriaScore = $evaluation->Score ?? 0;
                $criteriaMaxScore = $evaluation->MaxScore ?? 0;

                // Use the new criterion contribution method
                $weightedScore = $service->calculateCriterionContribution($evaluation);

                $sections[$sectionId]['criteria'][] = [
                    'name' => $masterCriteriaName,
                    'score' => $criteriaScore,
                    'maxScore' => $criteriaMaxScore,
                    'weight' => $criteriaWeight,
                    'weightedScore' => $weightedScore,
                    'remarks' => $evaluation->Remarks,
                ];
            }
        }

        return view('procurement.suppliers.prequalification.prequalification-evaluation.show_results', compact('application', 'sections', 'result'));
    }
}
