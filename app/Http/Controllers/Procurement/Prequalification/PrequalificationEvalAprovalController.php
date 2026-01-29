<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Enums\Procurement\PrequalificationApplicationEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PrequalificationEvalAprovalController extends Controller
{
    public function index(): View
    {
        $applications = PrequalificationApplication::where('Status', PrequalificationApplicationEnum::Submitted)
            ->with(['supplier', 'round'])
            ->paginate(10);

        return view('procurement.suppliers.prequalification.evaluationapproval.index', [
            'applications' => $applications,
        ]);
    }

    /**
     * Show the detailed summary for a specific application to be approved.
     *
     * @param PrequalificationApplication $application
     * @return View|RedirectResponse
     */
    public function show(PrequalificationApplication $application): View|RedirectResponse
    {
        // Check if the application is in the correct status for approval.
        // It should be 'Submitted', not 'Submitted', since it must be
        // evaluated before it can be approved.
        if ($application->Status !== PrequalificationApplicationEnum::Submitted) {
            return back()->with('error', 'This application is not ready for approval.');
        }

        // Load all the necessary relationships to display a full summary.
        $application->load([
            'supplier',
            'round.evaluationSections.criteria.masterCriteria',
            'evaluations.evaluator',
        ]);

        return view('procurement.suppliers.prequalification.evaluationapproval.show', [
            'application' => $application,
        ]);
    }

    public function store(Request $request, PrequalificationApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();

        try {
            if ($validated['decision'] === 'approve') {
                $application->Status = PrequalificationApplicationEnum::Approved;
                $message = 'Application has been approved successfully.';
            } else {
                $application->Status = PrequalificationApplicationEnum::Rejected;
                $message = 'Application has been rejected.';
            }

            $application->save();
            DB::commit();

            return redirect()->route('procurement.evaluation.approval.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update application status: ' . $e->getMessage());

            return back()->with('error', 'Failed to save approval decision.');
        }
    }
}
