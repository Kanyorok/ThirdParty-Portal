<?php

namespace App\Http\Controllers\CRM\Marketing\Planner;

use App\Http\Controllers\Controller;
use App\Models\MarketingPlanner;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PlannerDocumentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, MarketingPlanner $planner): Response
    {
        return PDF::loadview('crm.marketing.planner.document', [
                                                                'title'   => Str::upper($planner->PlannerID),
                                                                'planner' => $planner,
                                                               ])->setPaper('a4', 'landscape')->download($planner->PlannerID . '.pdf');
    }
}
