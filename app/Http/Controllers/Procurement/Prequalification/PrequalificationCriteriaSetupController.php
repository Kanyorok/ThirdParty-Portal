<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Criteria;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrequalificationCriteriaSetupController extends Controller
{
    /**
     * Show the form for setting up evaluation criteria for a prequalification round.
     *
     * @param PrequalificationRound $prequalificationRound
     * @return View
     */
    public function index(PrequalificationRound $prequalificationRound): View
    {
        // Fetch the applications for this specific prequalification round.
        // This variable is needed to display the list of applications in your view.
        $applications = $prequalificationRound->applications()->with('supplier')->get();

        return view('procurement.suppliers.prequalification.prequalification-criteria-setup.index', [
            'prequalificationRound' => $prequalificationRound,
            'applications' => $applications,
        ]);
    }

    /**
     * Store the configured evaluation sections and criteria for the prequalification round.
     *
     * @param Request $request
     * @param PrequalificationRound $prequalificationRound
     * @return RedirectResponse
     */
    public function store(Request $request, PrequalificationRound $prequalificationRound): RedirectResponse
    {
        // TODO: Implement validation and saving logic here

        return redirect()
            ->route('prequalification.prequalification-rounds.show', $prequalificationRound)
            ->with('success', 'Criteria configuration saved successfully!');
    }
}
