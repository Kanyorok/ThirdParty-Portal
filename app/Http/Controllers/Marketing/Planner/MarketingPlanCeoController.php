<?php

namespace App\Http\Controllers\Marketing\Planner;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\MarketingPlanner;
use App\Models\User;
use App\Services\Marketing\PlannerService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketingPlanCeoController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException
     */
    public function update(Request $request, MarketingPlanner $planner): JsonResponse
    {
        $this->authorize('ceo', User::class);
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($planner, $actor) {
                (new PlannerService($planner))->ceoWorkflowApprove($actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error ceo approval planner failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('plan approved successfully.', route('marketing-planner.show', [$planner->PlannerID]));

    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, MarketingPlanner $planner): JsonResponse
    {
        $this->authorize('ceo', User::class);
        $actor = $request->user();
        $data = $request->validate([
            'ceo_reject_reason' => ['required', 'string', 'min:15', 'max:2000'],
        ]);

        try {
            DB::transaction(static function () use ($planner, $actor, $data) {
                (new PlannerService($planner))->ceoWorkflowReject($actor, $data['ceo_reject_reason']);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error ceo reject planner failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('plan rejected successfully.', route('marketing-planner.index'));
    }
}
