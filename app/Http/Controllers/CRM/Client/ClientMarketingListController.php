<?php

namespace App\Http\Controllers\CRM\Client;

use App\Enums\Core\VisibilityEnum;
use App\Enums\MarketingListEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\PartyListRequest;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\CRM\MarketingList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientMarketingListController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Client $client): View
    {
        return view('crm.marketing.lists.update')
            ->with('party', $client)
            ->with('MarketingListMember', $client->marketingLists()->where('Type', MarketingListEnum::Static->value)
                ->where(function ($q) use ($request) {
                    $q->where('Visibility', VisibilityEnum::Public->value)
                        ->orWhere(function ($subQuery) use ($request) {
                            $subQuery->where('Visibility', VisibilityEnum::Private->value)
                                ->where('CreatedBy', $request->user()->Id);
                        });
                })->where('Source', '!=', DebtProduct::getPrimaryKey())->select(['slug'])->pluck('slug')->toArray())
            ->with('MarketingLists', MarketingList::query()->where('Type', MarketingListEnum::Static->value)
                ->where(function ($q) use ($request) {
                    $q->where('Visibility', VisibilityEnum::Public->value)
                        ->orWhere(function ($subQuery) use ($request) {
                            $subQuery->where('Visibility', VisibilityEnum::Private->value)
                                ->where('CreatedBy', $request->user()->Id);
                        });
                })->where('Source', '!=', DebtProduct::getPrimaryKey())->select(['slug', 'Label'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PartyListRequest $request, Client $client): JsonResponse
    {
        $client->marketingLists()->where('t_MarketingLists.Type', MarketingListEnum::Static->value)->where('Source', '!=', DebtProduct::getPrimaryKey())->syncWithPivotValues($request->getLists(), [
                                                                                                                                                                                                     'CreatedBy'  => $request->user()->Id,
                                                                                                                                                                                                     'ModifiedBy' => $request->user()->Id,
                                                                                                                                                                                                    ]);

        return $this->succeeded('marketing list updated.', route('clients.show', [$client->ClientID]));
    }
}
