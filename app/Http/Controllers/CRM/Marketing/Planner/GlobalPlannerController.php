<?php

namespace App\Http\Controllers\CRM\Marketing\Planner;

use App\Enums\Marketing\PlannerStatus;
use App\Enums\Marketing\PlannerTypeEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\GlobalPlannerRequest;
use App\Models\BR\Branch;
use App\Models\MarketingPlanner;
use App\Models\User;
use App\Services\Marketing\PlannerService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GlobalPlannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->only(['update', 'destroy']);
    }

    /**
     * Create a global Plan
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function store(GlobalPlannerRequest $request): JsonResponse
    {
        $this->authorize('marketingManager', User::class);
        $plans = $request->getPlans();
        $actor = $request->user();

        try {
            $planner = DB::transaction(static function () use ($plans, $request, $actor) {
                $planner = PlannerService::createMaster($request->validated('Name'), $plans, ($request->validated('Notes')) ?? "", $actor)->planner;
                activity()->causedBy($request->user())->performedOn($planner)->event('create')->log('created master  marketing plan ' . $planner->PlannerID);
                return $planner;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error creating master planner failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('planner created successfully.', route('master-planner.edit', $planner->PlannerID));
    }

    /**
     * Update Planner with details
     * @throws AuthorizationException
     */
    public function edit(Request $request, string $planner_id): View|RedirectResponse
    {
        $this->authorize('marketingManager', User::class);
        $planner = MarketingPlanner::query()->where('PlannerID', $planner_id)->where('OwnerId', $request->user()->Id)
            ->where('Type', PlannerTypeEnum::MasterPlanner->value)->where('Status', PlannerStatus::Draft->value)->first();
        if (!$planner instanceof MarketingPlanner) {
            return redirect()->route('marketing-planner.index')->with('fail', 'Cannot edit a plan already submitted');
        }

        return view('crm.marketing.planner.master.edit')
            ->with('planner', $planner)
            ->with('Branches', Branch::all(['BranchName', 'OurBranchID']));
    }

    /**
     * Submit
     * @throws AuthorizationException
     */
    public function update(Request $request, string $planner_id): JsonResponse
    {
        $this->authorize('marketingManager', User::class);
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:200'],
            'Notes' => ['nullable', 'string'],
        ]);

        $planner = MarketingPlanner::query()->where('PlannerID', $planner_id)->where('OwnerId', $request->user()->Id)
            ->where('Type', PlannerTypeEnum::MasterPlanner->value)->where('Status', PlannerStatus::Draft->value)->first();
        if (!$planner instanceof MarketingPlanner) {
            return $this->errored('plan cannot be updated');
        }

        $planner->update($data);

        return $this->succeeded('planner updated successfully.', route('master-planner.edit', $planner_id));
    }

    /**
     * cancel all
     * @throws AuthorizationException
     */
    public function destroy(Request $request, string $planner_id): JsonResponse
    {
        $this->authorize('marketingManager', User::class);
        $actor = $request->user();
        $planner = MarketingPlanner::query()->where('PlannerID', $planner_id)->where('OwnerId', $actor->Id)
            ->where('Type', PlannerTypeEnum::MasterPlanner->value)->where('Status', PlannerStatus::Draft->value)->first();
        if (!$planner instanceof MarketingPlanner) {
            return $this->errored('plan cannot be trashed');
        }

        try {
            DB::transaction(static function () use ($planner, $actor) {
                (new PlannerService($planner))->trash($actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error removing master planner failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('plan removed successfully.', route('marketing-planner.index'));
    }
}
