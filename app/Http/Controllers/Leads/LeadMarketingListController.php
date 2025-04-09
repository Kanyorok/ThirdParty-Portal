<?php

namespace App\Http\Controllers\Leads;

use App\Enums\MarketingListEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\PartyListRequest;
use App\Models\Lead;
use App\Models\MarketingList;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LeadMarketingListController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Lead $lead): View
    {
        return view('marketing.lists.update')
            ->with('party', $lead)
            ->with('MarketingListMember', $lead->marketingLists()->where('Type', MarketingListEnum::Static->value)->select(['slug'])->pluck('slug')->toArray())
            ->with('MarketingLists', MarketingList::query()->where('Type', MarketingListEnum::Static->value)->select(['slug', 'Label'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PartyListRequest $request, Lead $lead): JsonResponse
    {
        $lead->marketingLists()->where('t_MarketingLists.Type', MarketingListEnum::Static->value)->syncWithPivotValues($request->getLists(), [
            'CreatedBy' => $request->user()->Id,
            'ModifiedBy' => $request->user()->Id,
        ]);

        return $this->succeeded('marketing list updated.', route('leads.show', [$lead->LeadID]));
    }
}
