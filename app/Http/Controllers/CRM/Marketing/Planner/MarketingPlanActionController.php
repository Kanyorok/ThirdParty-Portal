<?php

namespace App\Http\Controllers\CRM\Marketing\Planner;

use App\Enums\Marketing\PlannerStatus;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\CRM\MarketingPlanner;
use App\Services\Marketing\PlannerService;
use App\Traits\Controller\WorkflowTrait;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MarketingPlanActionController extends Controller
{
    use WorkflowTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * @throws Exception
     */
    public function workflow(MarketingPlanner $planner): JsonResponse
    {
        $this->authorize('view', $planner);

        return $this->workflows($planner->workflows());
    }

    /**
     * @throws AuthorizationException
     */
    public function submit(Request $request, string $planner_id): JsonResponse
    {
        $actor = $request->user();
        $planner = MarketingPlanner::query()->where('PlannerID', $planner_id)->where('OwnerId', $actor->Id)
            ->where('Status', PlannerStatus::Draft->value)->first();
        if (! $planner instanceof MarketingPlanner) {
            return $this->errored('Cannot submit,  plan already submitted');
        }
        $this->authorize('view', $planner);

        if ($planner->activities()->count() === 0) {
            return $this->errored('plan has no activities');
        }

        $branch = $planner->branch;


        if ((! $branch?->manager instanceof User && ! $branch?->operation instanceof User)) {
            return $this->errored('No Branch or Operation Manager in Branch.');
        }

        try {
            DB::transaction(static function () use ($branch, $planner, $actor) {
                (new PlannerService($planner))->submit($branch, $actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Throwable | Exception $e) {
            Log::error('Error submitting planner failed: ' . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('planner submitted successfully.', route('marketing-planner.show', [$planner->PlannerID]));
    }
}
