<?php

namespace App\Http\Controllers\CRM\Marketing\Lists;

use App\Enums\MarketingListEnum;
use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Models\Lead;
use App\Models\MarketingList;
use App\Models\MarketingListParty;
use App\Services\BR\ClientService;
use App\Services\LeadService;
use App\Services\Marketing\DynamicListService;
use App\Services\Marketing\ListService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketingListsActionsController extends Controller
{

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * @throws Exception
     */
    public function leads(Request $request, MarketingList $list): JsonResponse
    {
        if ($request->isMethod('get')) {
            $this->authorize('view', $list);
            if ($list->Type->value === MarketingListEnum::Dynamic->value) {
                $query = (new DynamicListService($list))->query();
            } else {
                $query = ($request->q === 'current') ?
                    Lead::query()
                        ->whereExists(function ($query) use ($list) {
                            $query->select(DB::raw(1))
                                ->from('t_MarketingListParties')
                                ->where('MarketingListId', '=', $list->MarketingListID)
                                ->where('Party', Lead::getPrimaryKey())
                                ->whereRaw('t_Leads.LeadID = t_MarketingListParties.PartyID');
                        })
                    : Lead::query()
                        ->whereNotExists(function ($query) use ($list) {
                            $query->select(DB::raw(1))
                                ->from('t_MarketingListParties')
                                ->where('MarketingListId', '=', $list->MarketingListID)
                                ->where('Party', Lead::getPrimaryKey())
                                ->whereRaw('t_Leads.LeadID = t_MarketingListParties.PartyID');
                        });
            }

            return LeadService::dt($query);
        }

        //Add Or Remove Marketing List
        if ($request->isMethod('post')) {
            $this->authorize('update', $list);
            if (is_array($list->Processing)) {
                return $this->errored('list is processing');
            }

            if (in_array($list->Source, [Lead::getPrimaryKey(), null], true)) {
                return $this->errored('list does not support leads.');
            }

            if ($request->has('leads')) {
                $leadIDs = array_map('trim', explode(',', $request->input('leads')));
                ($request->q === 'current') ?
                    (new ListService($list))->removeLeads($leadIDs, $request->user()) :
                    (new ListService($list))->addLeads($leadIDs, $request->user());
            }
            return $this->succeeded('added successfully');
        }

        return $this->errored('invalid action at this moment');
    }

    /**
     * @throws Exception
     */
    public function clients(Request $request, MarketingList $list): JsonResponse
    {
        if ($request->isMethod('get')) {
            $this->authorize('view', $list);
            if ($list->Type->value === MarketingListEnum::Dynamic->value) {
                $query = (new DynamicListService($list))->query();
            } else {
                $query = ($request->q === 'current') ?
                    Client::query()->whereIn('ClientID', MarketingListParty::query()->where('MarketingListId', $list->MarketingListID)
                        ->where('Party', Client::getPrimaryKey())->select('PartyID'))

                    : Client::query()->where(function (Builder $query) {
                        $query->whereNotNull('Phone1')
                            ->orWhereNotNull('Phone2')
                            ->orWhereNotNull('Mobile')
                            ->orWhereNotNull('Email');
                    })->whereNotIn('ClientID', MarketingListParty::query()->where('MarketingListId', $list->MarketingListID)
                        ->where('Party', Client::getPrimaryKey())->select('PartyID'));
            }


            return ClientService::dt($query, ['type']);
        }

        if ($list->Type === MarketingListEnum::Dynamic->value) {
            return $this->errored('maybe dynamic cannot be changed');
        }

        if ($request->isMethod('post')) {
            $this->authorize('update', $list);
            if (is_array($list->Processing)) {
                return $this->errored('list is processing');
            }
            if (in_array($list->Source, [Client::getPrimaryKey(), null], true)) {
                return $this->errored('list does not support clients.');
            }
            if ($request->has('clients')) {
                $clientIDs = array_map('trim', explode(',', $request->input('clients')));
                ($request->q === 'current') ?
                    (new ListService($list))->removeClients($clientIDs, $request->user()) :
                    (new ListService($list))->addClients($clientIDs, $request->user());
            }
            return $this->succeeded('processed successfully');
        }

        return $this->errored('invalid action at this moment');
    }
}
