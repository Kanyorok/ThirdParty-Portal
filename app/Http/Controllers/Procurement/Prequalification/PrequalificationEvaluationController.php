<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationEvaluation;
use App\Models\Procurement\Prequalification\PrequalificationCriteria;
use App\Models\Procurement\Prequalification\PrequalificationResult;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PrequalificationEvaluationController extends Controller
{
    public function index(): View
    {
        $evaluations = PrequalificationEvaluation::with(['application', 'criteria'])
            ->where('EvaluatorID', Auth::id())
            ->paginate(10);

        return view('procurement.suppliers.prequalification.prequalification-evaluation.index', compact('evaluations'));
    }

    public function showEvaluationForm($applicationId): View|RedirectResponse
    {
        $application = PrequalificationApplication::with('supplier')->findOrFail($applicationId);
        $round = $application->round;

        if (!$round) {
            return redirect()->back()->with('error', 'The prequalification round for this application could not be found.');
        }

        $evaluatorId = Auth::id();

        $sections = $round->prequalificationSections()
            ->with(['masterSection', 'criteria.masterCriteria'])
            ->get();

        $existingEvaluations = PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->where('EvaluatorID', $evaluatorId)
            ->get()
            ->keyBy('CriteriaID');

        return view('procurement.suppliers.prequalification.prequalification-evaluation.evaluate', compact('application', 'sections', 'existingEvaluations'));
    }

    public function submitEvaluation(Request $request, $applicationId): RedirectResponse
    {
        $evaluatorId = Auth::id();

        $request->validate([
            'criteria_scores' => 'required|array',
            'criteria_scores.*.criteria_id' => 'required|integer',
            'criteria_scores.*.score' => 'nullable|numeric|min:0',
            'criteria_scores.*.max_score' => 'required|numeric|min:0',
            'criteria_scores.*.comments' => 'nullable|string',
            'general_comments' => 'nullable|string',
        ]);

        foreach ($request->input('criteria_scores') as $evaluationData) {
            $criteriaId = $evaluationData['criteria_id'];
            $maxScore = $evaluationData['max_score'];
            $scoreAwarded = $evaluationData['score'];

            $prequalificationCriteria = PrequalificationCriteria::where('CriteriaId', $criteriaId)
                ->first();

            if (!$prequalificationCriteria) {
                continue;
            }

            $sectionId = $prequalificationCriteria->SectionId;

            if (!is_null($scoreAwarded) && $scoreAwarded > $maxScore) {
                throw ValidationException::withMessages([
                    "criteria_scores.{$criteriaId}.score" => "Score awarded cannot exceed the max score of {$maxScore}."
                ]);
            }

            PrequalificationEvaluation::updateOrCreate(
                [
                    'ApplicationID' => $applicationId,
                    'EvaluatorID' => $evaluatorId,
                    'CriteriaID' => $criteriaId,
                ],
                [
                    'SectionID' => $sectionId,
                    'Score' => $scoreAwarded,
                    'MaxScore' => $maxScore,
                    'Remarks' => $evaluationData['comments'],
                ]
            );
        }

        $application = PrequalificationApplication::find($applicationId);
        if ($request->filled('general_comments')) {
            $application->GeneralComments = $request->input('general_comments');
            $application->save();
        }

        return redirect()->route('prequalification.applications.show', $applicationId)
            ->with('success', 'Evaluation submitted successfully!');
    }

    public function generateResults($applicationId): RedirectResponse
    {
        $evaluations = PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->with('criteria')
            ->get();

        $totalOverallScore = 0;
        $totalMaxScore = 0;

        foreach ($evaluations as $evaluation) {
            $criteriaWeight = $evaluation->criteria->Weight ?? 0;
            $criteriaScore = $evaluation->Score ?? 0;
            $criteriaMaxScore = $evaluation->MaxScore ?? 0;

            $weightedScore = 0;
            if ($criteriaMaxScore > 0) {
                $weightedScore = ($criteriaScore / $criteriaMaxScore) * $criteriaWeight;
            }
            $totalOverallScore += $weightedScore;
            $totalMaxScore += $criteriaWeight;
        }

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

    public function showResults($applicationId): View
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

            $weightedScore = 0;
            if ($criteriaMaxScore > 0) {
                $weightedScore = ($criteriaScore / $criteriaMaxScore) * $criteriaWeight;
            }

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
