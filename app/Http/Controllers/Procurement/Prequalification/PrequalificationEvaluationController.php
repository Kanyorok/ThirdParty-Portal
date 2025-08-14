<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationEvaluation;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Assuming you have this Enum to update the application status
use App\Enums\Procurement\PrequalificationApplicationEnum;

class PrequalificationEvaluationController extends Controller
{
    public function index(PrequalificationRound $prequalificationRound): View
    {
        $applications = $prequalificationRound->applications()->with('supplier')->paginate(10);
        return view('procurement.suppliers.evaluationsection.index', [
            'prequalificationRound' => $prequalificationRound,
            'applications' => $applications,
        ]);
    }

    public function create(PrequalificationApplication $application): View
    {
        $round = $application->round;
        $sections = $round->evaluationSections()
            ->with(['masterSection', 'criteria.masterCriteria'])
            ->get();
        $evaluations = PrequalificationEvaluation::where('ApplicationID', $application->ApplicationID)
            ->where('EvaluatorID', auth()->id())
            ->get()
            ->keyBy(function ($item) {
                return $item->CriteriaID;
            });
        return view('procurement.suppliers.evaluationcriteria.create', [
            'application' => $application,
            'round' => $round,
            'sections' => $sections,
            'evaluations' => $evaluations,
        ]);
    }

    public function store(Request $request, PrequalificationApplication $application): RedirectResponse
    {
        $validatedData = $request->validate([
            'evaluations' => 'required|array',
            'evaluations.*.CriteriaID' => 'required|exists:t_PrequalificationRoundCriteria,CriteriaID',
            'evaluations.*.Score' => 'required|numeric|min:0',
            'evaluations.*.Remarks' => 'nullable|string|max:1000',
        ]);
        DB::beginTransaction();
        try {
            // First, delete any previous evaluations by this user to avoid conflicts
            PrequalificationEvaluation::where('ApplicationID', $application->ApplicationID)
                ->where('EvaluatorID', auth()->id())
                ->delete();

            foreach ($validatedData['evaluations'] as $evaluationData) {
                // Fetch the SectionID once before creating the evaluation
                $roundCriteria = $application->round->evaluationCriteria()
                    ->where('CriteriaId', $evaluationData['CriteriaID'])
                    ->first();
                if ($roundCriteria) {
                    PrequalificationEvaluation::create([
                        'ApplicationID' => $application->ApplicationID,
                        'EvaluatorID' => auth()->id(),
                        'CriteriaID' => $evaluationData['CriteriaID'],
                        'SectionID' => $roundCriteria->SectionId,
                        'Score' => $evaluationData['Score'],
                        'Remarks' => $evaluationData['Remarks'],
                        'MaxScore' => 10,
                    ]);
                }
            }

            // After all evaluations are submitted, update the application's status
            // This is crucial for the approval workflow to proceed
            $application->Status = PrequalificationApplicationEnum::Submitted;
            $application->save();

            DB::commit();
            return redirect()
                ->route('prequalification.evaluation.index', $application->RoundId)
                ->with('success', 'Evaluation submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit evaluation: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to submit evaluation. Please try again.');
        }
    }
}
