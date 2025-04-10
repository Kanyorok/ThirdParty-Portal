<?php

namespace App\Http\Controllers\CRM\Marketing\Campaign;

use App\Enums\CampaignStatusEnum;
use App\Enums\MarketingListEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignParty;
use App\Models\MarketingList;
use App\Services\Marketing\CampaignService;
use App\Services\Marketing\DynamicListService;
use App\Services\PartyService;
use App\Traits\Controller\WorkflowTrait;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class CampaignActionsController extends Controller
{
    use WorkflowTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * @throws Exception
     */
    public function workflow(Campaign $campaign): JsonResponse
    {
        $this->authorize('view', $campaign);
        return $this->workflows($campaign->workflows());
    }

    /**
     * submit for approval by creator
     * Send if non approval
     *
     * @throws AuthorizationException
     */
    public function submit(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);
        if ($campaign->Status->value !== CampaignStatusEnum::Draft->value) {
            return $this->errored('Campaign has already submitted, cannot be edited');
        }

        if ($campaign->Processing) {
            return $this->errored('campaign contacts processing is not done, please wait.');
        }

        if ($campaign->contacts()->count() === 0) {
            return $this->errored('cannot run campaign with 0 contacts');
        }

        //check content.
        if (Str::length($campaign->Details) < 10) {
            return $this->errored('campaign content is too short');
        }

        $actor = $request->user();

        try {
            DB::transaction(static function () use ($campaign, $actor) {
                (new CampaignService($campaign))->submit($actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception|\Throwable $e) {
            Log::error('Error submitting campaign ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('Campaign submitted for approval', route('campaigns.show', $campaign->CampaignID));
    }

    /**
     * @throws Exception
     */
    public function contacts(Campaign $campaign): JsonResponse
    {
        $this->authorize('view', $campaign);
        return Datatables::of($campaign->contacts()->with(['party'])->select('*'))->addIndexColumn()
            ->addColumn('action', function (CampaignParty $campaignParty) {
                return '';
            })->editColumn('party', function (CampaignParty $campaignParty) {
                return (new PartyService($campaignParty->party))->getDTRow();
            })->editColumn('Status', function (CampaignParty $campaignParty) {
                return $campaignParty->Status->name;
            })->editColumn('CreatedOn', function (CampaignParty $campaignParty) {
                return $campaignParty->CreatedOn?->format('F d, Y h:i A');
            })->rawColumns(['action', 'party'])->make();

    }


    public function progress(Campaign $campaign): JsonResponse
    {
        $total = 0;
        $done = 0;
        $description = '';
        if ($campaign->Status->value === CampaignStatusEnum::Sending->value) {
            $total = $campaign->contacts()->count();
            $done = $campaign->contacts()->where('t_CampaignParties.Status', '!=', CampaignStatusEnum::Draft->value)->count();
            $description = 'Preparing Messages';
        } elseif ($campaign->Status->value === CampaignStatusEnum::Draft->value && $campaign->Processing) {
            $list = $campaign->list;
            if ($list instanceof MarketingList) {
                $done = $campaign->contacts()->count();
                if ($list->Type?->value === MarketingListEnum::Static->value) {
                    $total = $list->parties()->count();
                } elseif ($list->Type?->value === MarketingListEnum::Dynamic->value) {
                    $total = (new DynamicListService($list))->query()->count();
                } else {
                    $total = $done;
                }
                $description = 'Processing Contacts';
            }
        }

        return $this->succeeded('ok', data: [
            'progress' => (int)($total > 0) ? (($done / $total) * 100) : 100,
            'done' => (int)$done,
            'total' => (int)$total,
            'description' => $description . ' (' . number_format($done) . ' / ' . number_format($total) . ')'
        ]);
    }
}
