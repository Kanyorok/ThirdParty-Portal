<?php

namespace App\Http\Controllers\CRM\Marketing\Planner;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\CRM\MarketingPlanner;
use App\Services\Marketing\PlannerService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MarketingPlanBranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Request $request, MarketingPlanner $planner): JsonResponse
    {
        $this->authorize('approve', $planner);
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($planner, $actor) {
                (new PlannerService($planner))->branchWorkflowApprove($actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception | Throwable $e) {
            Log::error('Error branch approval planner failed: ' . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('plan approved successfully.', route('marketing-planner.index'));
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Request $request, MarketingPlanner $planner): JsonResponse
    {
        $this->authorize('approve', $planner);
        $actor = $request->user();
        $data = $request->validate([
                                    'branch_reject_reason' => [
                                                               'required',
                                                               'string',
                                                               'min:15',
                                                               'max:2000',
                                                              ],
                                   ]);

        try {
            DB::transaction(static function () use ($planner, $actor, $data) {
                (new PlannerService($planner))->branchWorkflowReject($actor, $data['branch_reject_reason']);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error branch reject planner failed: ' . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('plan rejected successfully.', route('marketing-planner.index'));
    }
}
