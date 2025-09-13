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
     * Build weighted results structure using new formula:
     * Each criterion raw max = 10; section weight distributed equally among its criteria.
     * criterionWeighted = (score / 10) * (sectionWeight / criteriaCount)
     * Returns array: ['sections'=>[], 'grandTotal'=>float]
     */
    private function buildWeightedResults($application, $evaluations)
    {
        $sectionsOut = [];
        $grandTotal = 0.0;
        $round = $application->round;
        $preqSections = $round->prequalificationSections()
            ->with(['masterSection','criteria'])
            ->get()
            ->keyBy('SectionId');

        $bySection = $evaluations->groupBy('SectionID');
        foreach ($bySection as $sectionId => $sectionEvaluations) {
            $sectionModel = $preqSections[$sectionId] ?? null;
            $sectionWeight = $sectionModel?->Weight ?? 0; // expects total of all to be 100
            $criteriaCount = $sectionModel?->criteria?->count() ?: max(1, $sectionEvaluations->count());
            $perCriterionWeight = $criteriaCount > 0 ? ($sectionWeight / $criteriaCount) : 0; // portion of 100
            $sectionDisplayName = $sectionModel?->masterSection?->SectionName
                ?? $sectionEvaluations->first()?->evaluationSection?->SectionName
                ?? 'Section';

            $criteriaArr = [];
            $sectionTotal = 0.0;
            foreach ($sectionEvaluations as $eval) {
                $rawScore = (float) ($eval->Score ?? 0); // out of 10
                if ($rawScore < 0) $rawScore = 0; if ($rawScore > 10) $rawScore = 10;
                $weighted = ($perCriterionWeight * ($rawScore / 10)); // already a % portion of 100
                $sectionTotal += $weighted;
                $criteriaArr[] = [
                    'name' => $eval->criteria->CriteriaName ?? 'Criterion',
                    'score' => $rawScore,
                    'maxScore' => 10,
                    'criterionWeightShare' => $perCriterionWeight, // share of 100 allocated to this criterion
                    'weightedScore' => $weighted,
                    'remarks' => $eval->Remarks,
                ];
            }
            // clip floating drift
            $sectionTotal = round($sectionTotal, 6);
            $grandTotal += $sectionTotal;
            $sectionsOut[] = [
                'id' => $sectionId,
                'name' => $sectionDisplayName,
                'sectionWeight' => $sectionWeight,
                'perCriterionWeight' => $perCriterionWeight,
                'sectionScore' => $sectionTotal, // percent contribution to 100
                'criteria' => $criteriaArr,
            ];
        }
        $grandTotal = round($grandTotal, 6);
        return ['sections' => $sectionsOut, 'grandTotal' => $grandTotal];
    }

    /**
     * Persist (create/update) a PrequalificationResult using new weighting output.
     */
    private function persistResults(PrequalificationApplication $application, array $calc): PrequalificationResult
    {
        $grandTotal = $calc['grandTotal'] ?? 0.0;
        $passingThreshold = 70; // configurable later
        $decision = ($grandTotal >= $passingThreshold) ? 'Passed' : 'Failed';

        return PrequalificationResult::updateOrCreate(
            ['ApplicationID' => $application->ApplicationID],
            [
                'TotalScore' => $grandTotal,
                'Decision' => $decision,
                'ApprovalBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]
        );
    }
    /**
     * Admin-only method to generate results for an application.
     */
    public function generateResults(SupplierPrequalificationService $service, $applicationId): \Illuminate\Http\RedirectResponse
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized. Only authenticated users can generate results.');
        }

        $application = PrequalificationApplication::findOrFail($applicationId);
        $evaluations = PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->with(['criteria', 'evaluationSection'])
            ->get();

        if ($evaluations->isEmpty()) {
            return redirect()->back()->with('error', 'No evaluations found for this application.');
        }

        $calc = $this->buildWeightedResults($application, $evaluations);
        $this->persistResults($application, $calc);

        return redirect()->back()->with('success', 'Prequalification results generated successfully!');
    }

    /**
     * Display the final prequalification results (only if they exist).
     */
    public function showResults(SupplierPrequalificationService $service, $applicationId): View
    {
        $application = PrequalificationApplication::with(['supplier', 'category'])->findOrFail($applicationId);

        // Load evaluations
        $evaluations = PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->with(['criteria', 'evaluationSection'])
            ->get();

        if ($evaluations->isEmpty()) {
            return view('procurement.suppliers.prequalification.prequalification-evaluation.no_results', compact('application'));
        }

        // Always (re)calculate & persist on viewing to keep data fresh
        $calc = $this->buildWeightedResults($application, $evaluations);
        $result = $this->persistResults($application, $calc);

        $sections = $calc['sections'];
        $grandTotal = $calc['grandTotal'];

        return view('procurement.suppliers.prequalification.prequalification-evaluation.show_results', compact('application', 'sections', 'result', 'grandTotal'));

    }
}
