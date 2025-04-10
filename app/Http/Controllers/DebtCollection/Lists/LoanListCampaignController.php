<?php

namespace App\Http\Controllers\DebtCollection\Lists;

use App\Enums\CampaignStatusEnum;
use App\Enums\CampaignTypeEnum;
use App\Enums\EmailStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DebtCollection\LoanCampaignRequest;
use App\Models\BR\DebtProduct;
use App\Models\Campaign;
use App\Models\MarketingList;
use App\Services\Marketing\CampaignService;
use App\Traits\Controller\CampaignTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LoanListCampaignController extends Controller
{
    use CampaignTrait;

    public function __construct()
    {
        $this->middleware('ajax')->except(['show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, MarketingList $list): JsonResponse
    {
        return $this->getCampaigns(Campaign::query()->where('t_Campaigns.MarketingListId', $list->MarketingListID), $request->user(), list: $list);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(LoanCampaignRequest $request, MarketingList $list): JsonResponse
    {
        $actor = $request->user();
        try {
            $campaign = DB::transaction(static function () use ($actor, $request, $list) {
                return CampaignService::create($list, $request->string('Label', 'Non Labeled Loan Campaign')->toString(), $actor, CampaignTypeEnum::SMS, $request->string('Notes', '')->toString(), true)->campaign;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Throwable|\Exception $e) {
            Log::error('Error loan list campaign auto: ');
            Log::error($e);
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('campaign submitted for processing.', route: route('loans-campaigns.show', $campaign->CampaignID));
    }

    /**
     * Display the specified resource.
     */
    public function show($listID, Campaign $campaign): View|RedirectResponse
    {
        if ($campaign->list->Source !== DebtProduct::getPrimaryKey() && $listID !== $campaign->list->MarketingListID) {
            return redirect()->back()->with(['fail' => 'campaign not found.']);
        }
        $data = ['labels' => [], 'data' => [], 'rate' => 0];

        if (!$campaign->Processing) {
            $failed = $campaign->contacts()->where('t_CampaignParties.Status', EmailStatusEnum::Failed->value)->count();
            $success = $campaign->contacts()->where('t_CampaignParties.Status', EmailStatusEnum::Sent->value)->count();
            data_set($data, 'labels', [EmailStatusEnum::Failed->name, EmailStatusEnum::Sent->name]);
            data_set($data, 'rate', number_format((($success / $campaign->contacts()->count()) * 100), 2));
            data_set($data, 'data', [$failed, $success]);
        }

        return view('debt-collection.campaigns.show', compact('campaign', 'data'))
            ->with('list', $campaign->list)->with('hasProgress', $campaign->Processing);
    }

    /**
     * Progress for view.
     */
    public function edit($listID, Campaign $campaign): JsonResponse
    {
        $total = 100;
        $done = 0;
        $description = '';
        if (!$campaign->Processing) {
            $total = 0;
            $done = 0;
            $description = 'Processing Complete';
        } else if ($campaign->Status->value === CampaignStatusEnum::Processing->value) {// 2 for processing messages.
            $total = $campaign->contacts()->count();
            $done = $campaign->contacts()->where('t_CampaignParties.Status', '!=', CampaignStatusEnum::Draft->value)->count();
            $description = 'Preparing Messages 2/3';
        } else if ($campaign->Status->value === CampaignStatusEnum::Sending->value) {// 3 Sending messages
            $total = $campaign->contacts()->count();
            $done = $campaign->contacts()->whereIn('t_CampaignParties.Status', [CampaignStatusEnum::Sent->value, CampaignStatusEnum::Failed->value])->count();
            $description = 'Sending Messages 3/3';
        } elseif ($campaign->Status->value === CampaignStatusEnum::Draft->value) { // 1, for contacts processing &
            $list = $campaign->list;
            if ($list instanceof MarketingList) {
                $done = $campaign->contacts()->count();
                $total = $list->parties()->count();
                $description = 'Processing Contacts 1/3';
            }
        }

        return $this->succeeded('ok', data: [
            'progress' => (int)($total > 0) ? (($done / $total) * 100) : 100,
            'done' => (int)$done,
            'total' => (int)$total,
            'description' => $description . ' (' . number_format($done) . ' of ' . number_format($total) . ')'
        ]);

    }
}
