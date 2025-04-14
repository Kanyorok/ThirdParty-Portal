<?php

namespace App\Http\Controllers\CRM\Marketing\Campaign;

use App\Enums\CampaignStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\Marketing\CampaignService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampaignApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Approve
     * @throws AuthorizationException
     */
    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('approve', $campaign);
        if ($campaign->Status->value !== CampaignStatusEnum::Approval->value) {
            return $this->errored('campaign has already been approved');
        }

        $lock = Cache::lock('approve-campaign-' . $campaign->CampaignID, 5);
        if (!$lock->get()) {
            return $this->errored('campaign has been approved, or another user is working on it');
        }

        $actor = $request->user();

        try {
            DB::transaction(static function () use ($campaign, $actor) {
                (new CampaignService($campaign))->workflowApprove($actor)
                    ->run($actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Throwable | Exception $e) {
            Log::error('Error approve campaign failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('campaign approved successfully.', route('campaigns.index'));
    }

    /**
     * Reject and Revert
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('approve', $campaign);
        $actor = $request->user();
        $data = $request->validate([
                                    'campaign_reject_reason' => [
                                                                 'required',
                                                                 'string',
                                                                 'min:15',
                                                                 'max:2000',
                                                                ],
                                   ]);

        try {
            DB::transaction(static function () use ($campaign, $actor, $data) {
                (new CampaignService($campaign))->workflowReject($actor, $data['campaign_reject_reason']);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error reject campaign failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('campaign rejected successfully.', route('campaigns.index'));
    }
}
