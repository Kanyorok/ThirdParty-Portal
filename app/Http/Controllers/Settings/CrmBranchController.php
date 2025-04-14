<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\BR\Branch;
use App\Models\CrmBranch;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class CrmBranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
        $this->authorizeResource(CrmBranch::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): JsonResponse
    {
        return Datatables::of(Branch::query()->select('OurBranchID', 'BranchName', 'Address1', 'Address2')->with('local'))->addIndexColumn()
            ->addColumn('action', function (Branch $branch) {
                return '<button type="button" data-click_url="' . route('branches.create', ['branch' => $branch->OurBranchID]) . '" data-summary_title="Branch Details" class="btn btn-primary btn-sm click-summary-data"><i class="fas fa-edit"></i></button>';
            })->addColumn('Manager', function (Branch $branch) {
                if ($branch->local instanceof CrmBranch && $branch->local->manager instanceof User) {
                    return $branch->local->manager->Name;
                }
                return '<b class="text-danger">None<b>';
            })->addColumn('Operation', function (Branch $branch) {
                if ($branch->local instanceof CrmBranch && $branch->local->operation instanceof User) {
                    return $branch->local->operation->Name;
                }
                return '<b class="text-danger">None<b>';
            })->setRowClass('mouse_pointer user-select-none dbl-click-summary-data')->setRowData([
                                                                                                  'dbl_click_url' => function (Branch $branch) {
                                                                                                    return route('branches.create', ['branch' => $branch->OurBranchID]);
                                                                                                  },
                                                                                                  'summary_title' => 'Branch Details',
                                                                                                 ])->rawColumns(['action', 'Manager', 'Operation'])->make();
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
                                    'BranchID'  => 'required',
                                    'Manager'   => 'nullable',
                                    'Operation' => 'nullable',
                                   ]);

        $branch = Branch::query()->where('OurBranchID', $data['BranchID'])->first();
        if (!$branch instanceof Branch) {
            return $this->errored('branch error, refresh and try again');
        }

        $userID = null;
        if ($request->has('Manager') && is_string($data['Manager'])) {
            $user = User::query()->where('UserID', $data['Manager'])->first('Id');
            if (!$user instanceof User) {
                throw ValidationException::withMessages(['Manager' => 'Branch manager selected is invalid']);
            }
            $userID = $user->Id;
        }

        $Operation = null;
        if ($request->has('Operation') && is_string($data['Operation'])) {
            $user = User::query()->where('UserID', $data['Operation'])->first('Id');
            if (!$user instanceof User) {
                throw ValidationException::withMessages(['Operation' => 'Operational manager selected is invalid']);
            }
            $Operation = $user->Id;
        }

        $actor = $request->user();
        try {
            DB::transaction(static function () use ($Operation, $actor, $branch, $userID) {
                CrmBranch::query()->where('BranchID', $branch->OurBranchID)->update([
                                                                                     'DeletedBy' => $actor->Id,
                                                                                    ]);
                CrmBranch::query()->where('BranchID', $branch->OurBranchID)->delete();

                if (!is_null($Operation) || !is_null($userID)) {
                    $crmBranch = CrmBranch::create([
                                                    'BranchID'   => $branch->OurBranchID,
                                                    'UserId'     => $userID,
                                                    'ManagerId'  => $Operation,
                                                    'Name'       => $branch->BranchName,
                                                    'CreatedBy'  => $actor->Id,
                                                    'ModifiedBy' => $actor->Id,
                                                    'CreatedOn'  => now(),
                                                    'UpdatedOn'  => now(),
                                                   ]);
                    activity()->causedBy($actor)->performedOn($crmBranch->refresh())->event('update')->log('updated branch managers ' . $branch->BranchName);
                }
            });
        } catch (Exception $e) {
            Log::error('Error updating branch failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('branch updated successfully');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View|JsonResponse
    {
        if (!$request->has('branch')) {
            return $this->errored('invalid request');
        }
        $branch = Branch::query()->where('OurBranchID', $request->get('branch'))->with('local')->first();
        if (!$branch instanceof Branch) {
            return $this->errored('invalid branch');
        }


        return view('settings.branches.create', compact('branch'))
            ->with('local', $branch->local);
    }
}
