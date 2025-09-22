<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationCriteria;
use App\Models\Procurement\Prequalification\PrequalificationEvaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PrequalificationEvaluationController extends Controller
{
    public function index(): View
    {
        $evaluations = PrequalificationEvaluation::with(['application.supplier', 'application.result', 'criteria'])
            ->where('EvaluatorID', Auth::id())
            ->paginate(10);

        return view('procurement.suppliers.prequalification.prequalification-evaluation.index', compact('evaluations'));
    }

    public function showEvaluationForm($applicationId): View|RedirectResponse
    {
        $application = PrequalificationApplication::with('supplier', 'category')->findOrFail($applicationId);
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
}
