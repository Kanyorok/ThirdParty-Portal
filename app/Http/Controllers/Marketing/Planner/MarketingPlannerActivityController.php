<?php

namespace App\Http\Controllers\Marketing\Planner;

use App\Enums\Marketing\PlannerTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\PlannerActivityRequest;
use App\Models\BR\Branch;
use App\Models\MarketingPlanner;
use App\Models\MarketingPlannerActivity;
use App\Services\Marketing\PlannerService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class MarketingPlannerActivityController extends Controller
{

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * List of plan activities
     * @throws AuthorizationException
     * @throws Exception
     */
    public function index(Request $request, MarketingPlanner $planner): JsonResponse
    {
        $this->authorize('viewAny', MarketingPlannerActivity::class);
        $actor = $request->user();
        $branches = Branch::all(['OurBranchID', 'BranchName']);

        return Datatables::of($planner->activities()->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('action', function (MarketingPlannerActivity $activity) use ($actor, $planner) {
                return (((int)$planner->OwnerId === (int)$actor->Id)) ?
                    '<button class="btn btn-primary btn-sm m-2 click-summary-data" type="button" data-click_url="' . route('planner-activities.edit', [$planner->PlannerID, $activity->PlannerActivityID]) . '"
                        data-summary_title="update ' . Str::upper($planner->PlannerID) . ' activity"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm m-2 trash-planner-activity" type="button"
                    data-info="' . route('planner-activities.destroy', [$planner->PlannerID, $activity->PlannerActivityID]) . '~' . $activity->Name . '" >
                    <i class="fas fa-trash"></i> </button>' : '';
            })->editColumn('Branch', function (MarketingPlannerActivity $activity) use ($planner, $branches) {
                if ($planner->Type->value === PlannerTypeEnum::MasterPlanner->value) {
                    return $branches->where('OurBranchID', $activity->BranchId)->first()?->BranchName;
                }
                return '-';
            })->editColumn('StartOn', function (MarketingPlannerActivity $activity) {
                return $activity->StartOn?->format('M d, Y');
            })->editColumn('EndOn', function (MarketingPlannerActivity $activity) {
                return $activity->EndOn?->format('M d, Y');
            })->editColumn('Budget', function (MarketingPlannerActivity $activity) {
                return number_format($activity->Budget, 2);
            })->setRowClass('mouse_pointer user-select-none dbl-click-summary-data')->setRowData([
                'dbl_click_url' => function (MarketingPlannerActivity $activity) {
                    return route('planner-activities.show', [$activity->planner->PlannerID, $activity->PlannerActivityID]);
                }, 'summary_title' => 'Plan Activity Details'
            ])->rawColumns(['action'])->make();
    }

    /**
     * Add an activity in draft && owner
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function store(PlannerActivityRequest $request, MarketingPlanner $planner): JsonResponse
    {
        $this->authorize('create', [MarketingPlannerActivity::class, $planner]);
        $branch = null;
        if ($planner->Type->value === PlannerTypeEnum::MasterPlanner->value) {
            if ($request->has('Branch')) {
                $branch = Branch::query()->where('OurBranchID', $request->get('Branch'))->first();
            }
            if (!$branch instanceof Branch) {
                throw ValidationException::withMessages([
                    'Branch' => 'branch is required'
                ]);
            }
        }
        $actor = $request->user();
        $start = $request->getStart();
        $end = $request->getEnd($start);
        $users = $request->getUsers();

        try {
            $planner = DB::transaction(static function () use ($users, $end, $start, $request, $planner, $actor, $branch) {
                (new PlannerService($planner))
                    ->addActivity($request->validated('activity_name'), $request->validated('activity_location'), $start, $end, $request->validated('activity_budget'),
                        ($request->validated('activity_notes')) ?? '', $actor, $users, $branch, ($request->validated('activity_materials')) ?? '');
                activity()->causedBy($request->user())->performedOn($planner)->event('update')->log('added activity to plan ' . $planner->PlannerID);
                return $planner;
            });
        } catch (Exception $e) {
            Log::error('Error adding  planner activity failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('activity added successfully.', route('marketing-planner.edit', $planner->PlannerID));
    }


    /**
     * Summary
     * @throws AuthorizationException
     */
    public function show(MarketingPlanner $planner, string $ActivityId): View|JsonResponse
    {
        $this->authorize('view', [$planner]);
        $Activity = $planner->activities()->where('t_MarketingPlannerActivities.PlannerActivityID', $ActivityId)->first();
        if (!$Activity instanceof MarketingPlannerActivity) {
            return $this->errored('Activity not found');
        }
        return view('marketing.planner.activities.show')
            ->with('activity', $Activity)
            ->with('users', $Activity->users);
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(MarketingPlanner $planner, string $ActivityId): View|JsonResponse
    {
        $this->authorize('create', [MarketingPlannerActivity::class, $planner]);
        $Activity = $planner->activities()->where('t_MarketingPlannerActivities.PlannerActivityID', $ActivityId)->first();
        if (!$Activity instanceof MarketingPlannerActivity) {
            return $this->errored('Activity not found');
        }
        return view('marketing.planner.activities.edit')
            ->with('activity', $Activity)->with('planner', $planner)->with('users', $Activity->users)
            ->with('Branches', Branch::all(['BranchName', 'OurBranchID']));
    }


    /**
     * Add an activity in draft && owner
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function update(PlannerActivityRequest $request, MarketingPlanner $planner, string $ActivityId): JsonResponse
    {
        $this->authorize('create', [MarketingPlannerActivity::class, $planner]);
        $Activity = $planner->activities()->where('t_MarketingPlannerActivities.PlannerActivityID', $ActivityId)->first();
        if (!$Activity instanceof MarketingPlannerActivity) {
            return $this->errored('Activity not found');
        }
        $branchId = $Activity->BranchId;
        if ($planner->Type->value === PlannerTypeEnum::MasterPlanner->value) {
            if ($request->has('Branch') && Branch::query()->where('OurBranchID', $request->get('Branch'))->exists()) {
                $branchId = $request->get('Branch');
            } else {
                throw ValidationException::withMessages([
                    'Branch' => 'branch is required'
                ]);
            }
        }
        $actor = $request->user();
        $start = $request->getStart();
        $end = $request->getEnd($start);
        $users = $request->getUsers();

        try {
            DB::transaction(static function () use ($Activity, $users, $end, $start, $request, $planner, $actor, $branchId) {
                (new PlannerService($planner))->updateActivity($Activity, $request->validated('activity_name'), $request->validated('activity_location'),
                    $start, $end, $request->validated('activity_budget'), ($request->validated('activity_notes')) ?? '', $actor, $users, $branchId);
                activity()->causedBy($request->user())->performedOn($planner)->event('update')->log('updated activity (' . $Activity->PlannerActivityID . ') in plan ' . $planner->PlannerID);
            });
        } catch (Exception $e) {
            Log::error('Error adding  planner activity failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('activity added successfully.', route('marketing-planner.edit', $planner->PlannerID));
    }



    /**
     * Remover an activity in draft && owner.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, MarketingPlanner $planner, string $ActivityId): JsonResponse
    {
        $this->authorize('create', [MarketingPlannerActivity::class, $planner]);
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($ActivityId, $request, $planner, $actor) {
                $planner->activities()->where('t_MarketingPlannerActivities.PlannerActivityID', $ActivityId)->update([
                    'DeletedBy' => $actor->id,
                    'DeletedOn' => now()
                ]);
                activity()->causedBy($request->user())->performedOn($planner)->event('delete')->log('removed activity ' . $ActivityId . ' from  plan ' . $planner->PlannerID);
                return $planner;
            });
        } catch (Exception $e) {
            Log::error('Error removing  planner activity failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('removed activity successfully.');
    }
}
