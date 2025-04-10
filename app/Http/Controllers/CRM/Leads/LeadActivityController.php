<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LeadActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Lead $lead): View
    {
        $last = ($request->last_view) ?? 9223372036854775807;//can replace it with max

        return view('crm.clients.snippets.activities')
            ->with('activities', $lead->activities()->where('ActivityID', '<', $last)->latest('ActivityID')->limit(10)->get())
            ->with('activity_more', $lead->activities()->where('ActivityID', '<', $last)->latest('ActivityID')->count());
    }
}
