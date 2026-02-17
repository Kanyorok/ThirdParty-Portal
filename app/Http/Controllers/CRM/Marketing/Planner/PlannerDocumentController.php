<?php

namespace App\Http\Controllers\CRM\Marketing\Planner;

use App\Http\Controllers\Controller;
use App\Models\CRM\MarketingPlanner;
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

        $org = \App\Models\Settings\APICredential::query()->where('Integration', \App\Enums\Core\IntegrationsEnum::Organization->value)->latest('Id')->first();
        $branding = $org?->Configuration;
        $name = is_object($branding) && isset($branding->name) ? $branding->name : config('app.name');
        $motto = is_object($branding) && isset($branding->motto) ? $branding->motto : 'Thinking.Crafting.Transforming';

        return PDF::loadview('crm.marketing.planner.document', [
            'title' => Str::upper($planner->PlannerID),
            'planner' => $planner,
            'name' => $name,
            'motto' => $motto,
        ])->setPaper('a4', 'landscape')->download($planner->PlannerID . '.pdf');
    }
}
