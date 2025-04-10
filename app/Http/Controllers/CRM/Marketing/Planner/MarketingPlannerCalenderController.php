<?php

namespace App\Http\Controllers\CRM\Marketing\Planner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Marketing\PlannerActivitiesCollection;
use App\Models\MarketingPlanner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MarketingPlannerCalenderController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, MarketingPlanner $planner): PlannerActivitiesCollection
    {
        $start = ($request->has('start'))
            ? Carbon::createFromFormat('Y-m-d', $request->start)?->startOfDay()
            : $planner->StartOn?->startOfDay();
        $end = ($request->has('end'))
            ? Carbon::createFromFormat('Y-m-d', $request->end)?->endOfDay()
            : $planner->EndOn?->startOfDay();

        if (!$start instanceof Carbon || !$end instanceof Carbon) {
            return new PlannerActivitiesCollection(collect());
        }

        return new PlannerActivitiesCollection($planner->activities()->whereBetween('t_MarketingPlannerActivities.StartOn', [$start, $end])->lock('WITH(NOLOCK)')->get());
    }
}
