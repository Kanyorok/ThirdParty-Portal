<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\Procurement\Prequalification\PrequalificationSection;
use App\Models\Procurement\Prequalification\PrequalificationCriteria;
use App\Models\Procurement\Section;
use App\Models\Procurement\Criteria;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
