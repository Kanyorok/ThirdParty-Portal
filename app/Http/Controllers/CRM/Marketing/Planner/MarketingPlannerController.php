<?php

namespace App\Http\Controllers\CRM\Marketing\Planner;

use App\Enums\Core\PermissionEnum;
use App\Enums\Marketing\PlannerStatus;
use App\Enums\Marketing\PlannerTypeEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\PlannerRequest;
use App\Models\Core\Branch;
use App\Models\CRM\MarketingPlanner;
use App\Services\HRM\UserService;
use App\Services\Marketing\PlannerService;
use App\Services\StaticListsService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\DataTables;

class MarketingPlannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'edit', 'show']);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', MarketingPlanner::class);
        if ($request->ajax()) {
            $query = MarketingPlanner::query()->where('Status', '!=', PlannerStatus::Merged);
            $actor = $request->user();

            //check pending marketing plans on me.
            /*

            $statuses = collect();

            if ($actor->hasPermissionTo(PermissionEnum::MarketingPlannerApproval->value)) {
               $statuses->add(PlannerStatus::BranchManager->value);
               $statuses->add(PlannerStatus::MarketingManager->value);
               $statuses->add(PlannerStatus::Committee->value);
               $statuses->add(PlannerStatus::Board->value);
               //plans
               PendingWorkflow::query()->where('Source',MarketingPlanner::getPrimaryKey())
                   ->where('SourceID',);
           }*/

            $query->where(function (Builder $query) use ($actor) {
                $query->orWhere(function (Builder $query) use ($actor) {
                    $query->where('t_MarketingPlanner.Status', PlannerStatus::Draft->value)
                        ->where('t_MarketingPlanner.OwnerId', $actor->Id);
                })->orWhere(function (Builder $query) {
                    $query->where('t_MarketingPlanner.Status', PlannerStatus::Active->value);
                    /*   })->orWhere(function (Builder $query) use ($statuses) {
                           $query->whereIn('t_MarketingPlanner.Status', $statuses->toArray());*/
                })->orWhere(function (Builder $query) use ($actor) {
                    $query->where('t_MarketingPlanner.OwnerId', $actor->Id);
                });
            });

            if ($actor->hasPermissionTo(PermissionEnum::MarketingPlannerApproval->value)) {
                $query->orWhereHas('pendingWorkflows', function (Builder $query) use ($actor) {
                    $query->where('UserId', '=', $actor->Id);
                });
            }

            return Datatables::of($query->with(['branch', 'mode'])->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
                ->addColumn('action', function (MarketingPlanner $planner) use ($actor) {
                    $btn = '';
                    if (((int) $planner->OwnerId === (int) $actor->Id && $planner->Status->value === PlannerStatus::Draft->value)) {
                        $btn = ($planner->Type->value === PlannerTypeEnum::MasterPlanner->value)
                            ? '<a class="btn btn-primary btn-sm m-2" href="' . route('master-planner.edit', [$planner->PlannerID]) . '"><i class="fas fa-edit"></i> edit</a>'
                            : '<a class="btn btn-primary btn-sm m-2" href="' . route('marketing-planner.edit', [$planner->PlannerID]) . '"><i class="fas fa-edit"></i> edit</a>';
                    }


                    return $btn . '<a class="btn btn-info btn-sm m-2" href="' . route('marketing-planner.show', [$planner->PlannerID]) . '"><i class="fas fa-eye"></i> details</a>';
                })->editColumn('mode.Description', function (MarketingPlanner $planner) {
                    return $planner->mode?->Description;
                })->editColumn('PlannerID', function (MarketingPlanner $planner) {
                    return Str::upper($planner->PlannerID);
                })->editColumn('ModifiedOn', function (MarketingPlanner $planner) {
                    return $planner->ModifiedOn?->format('M d, Y H:i');
                })->editColumn('Status', function (MarketingPlanner $planner) {
                    return $planner->Status->description();
                })->setRowData([
                                'dbl_click_url' => function (MarketingPlanner $planner) {
                                    return route('marketing-planner.show', $planner->PlannerID);
                                },
                               ])->rawColumns(['action'])->make();
        }
        return ((new UserService($request->user()))->isMarketingManager()) ?
            view('crm.marketing.planner.index')
                ->with('isMarketingManager', true)
                ->with('plans', MarketingPlanner::query()->where('t_MarketingPlanner.Status', PlannerStatus::MarketingManager->value)->whereNull('t_MarketingPlanner.MasterPlannerId')->select(['t_MarketingPlanner.PlannerID', 't_MarketingPlanner.Name', 'BranchId'])->get())
            : view('crm.marketing.planner.index')->with('isMarketingManager', false)
                ->with('Branches', Branch::query()->get(['t_Branches.Name', 't_Branches.BranchID']))
                ->with('MarketingModes', StaticListsService::getList(StaticListsService::MarketingModes));
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function store(PlannerRequest $request): JsonResponse
    {
        $this->authorize('create', MarketingPlanner::class);
        $branch = $request->getBranch();
        $mode = $request->getMode();
        $actor = $request->user();

        if ((new UserService($actor))->isMarketingManager()) {
            return $this->errored('you are the marketing manager, cannot create.');
        }

        try {
            $planner = DB::transaction(static function () use ($request, $branch, $mode, $actor) {
                $planner = PlannerService::create($branch, $mode, $request->validated('Name'), ($request->validated('Notes')) ?? "", $actor)->planner;
                activity()->causedBy($request->user())->performedOn($planner)->event('create')->log('created  marketing plan ' . $planner->PlannerID);
                return $planner;
            });
        } catch (Throwable|Exception $e) {
            Log::error('Error creating planner failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('planner created successfully.', route('marketing-planner.edit', $planner->PlannerID));
    }

    /**
     * Display the specified resource.
     * @throws AuthorizationException
     */
   public function show(Request $request, MarketingPlanner $planner): View
    {
        $this->authorize('view', $planner);

        $Branches = Branch::query()->get(['BranchID', 'Name']); // <-- Add this

        return view('crm.marketing.planner.show', compact('planner', 'Branches'))
            ->with('canApprove', (new PlannerService($planner))->canApprove($request->user()));
    }

    /**
     * Show the form for editing the specified resource.
     * @throws AuthorizationException
     */
    public function edit(Request $request, string $planner_id): View|RedirectResponse
    {
        $this->authorize('create', MarketingPlanner::class);
        $planner = MarketingPlanner::query()->where('PlannerID', $planner_id)->where('OwnerId', $request->user()->Id)
            ->where('Type', PlannerTypeEnum::BranchPlanner->value)->where('Status', PlannerStatus::Draft->value)->first();
        if (!$planner instanceof MarketingPlanner) {
            return redirect()->route('marketing-planner.index')->with('fail', 'Cannot edit a plan already submitted');
        }

        return view('crm.marketing.planner.edit')
            ->with('planner', $planner)
            ->with('Branches', Branch::query()->get(['t_Branches.Name', 't_Branches.BranchID']))
            ->with('MarketingModes', StaticListsService::getList(StaticListsService::MarketingModes));
    }

    /**
     * Submit from draft so role if create
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function update(PlannerRequest $request, string $planner_id): JsonResponse
    {
        $this->authorize('create', MarketingPlanner::class);
        $planner = MarketingPlanner::query()->where('PlannerID', $planner_id)->where('OwnerId', $request->user()->Id)
            ->where('Type', PlannerTypeEnum::BranchPlanner->value)->where('Status', PlannerStatus::Draft->value)->first();
        if (!$planner instanceof MarketingPlanner) {
            return $this->errored('Cannot edit a plan already submitted');
        }
        $branch = $request->getBranch();
        $mode = $request->getMode();
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($request, $planner, $branch, $mode, $actor) {
                (new PlannerService($planner))->update($branch, $mode, $request->validated('Name'), ($request->validated('Notes')) ?? "", $actor);
                activity()->causedBy($actor)->performedOn($planner)->event('update')->log('updated  marketing plan ' . $planner->PlannerID);
            });
        } catch (Exception|Throwable $e) {
            Log::error('Error updating planner failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('update planner successfully.', route('marketing-planner.edit', [$planner->PlannerID]));
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, string $planner_id): JsonResponse
    {
        $this->authorize('create', MarketingPlanner::class);
        $actor = $request->user();
        $planner = MarketingPlanner::query()->where('PlannerID', $planner_id)->where('OwnerId', $request->user()->Id)
            ->where('Type', PlannerTypeEnum::BranchPlanner->value)->where('Status', PlannerStatus::Draft->value)->first();
        if (!$planner instanceof MarketingPlanner) {
            return $this->errored('cannot find that planner');
        }

        try {
            DB::transaction(static function () use ($planner, $actor) {
                (new PlannerService($planner))->trash($actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error removing  planner failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('plan removed successfully.', route('marketing-planner.index'));
    }
}
