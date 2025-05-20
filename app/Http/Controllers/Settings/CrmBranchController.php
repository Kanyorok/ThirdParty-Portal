<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\BranchRequest;
//use App\Models\BR\Branch;
use App\Models\Core\Branch;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\DataTables;

#use App\Models\BR\Branch;

class CrmBranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except('index');
        // $this->authorizeResource(Branch::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): JsonResponse|View
    {
        $this->authorize('viewAny', Branch::class);
        if ($request->ajax()) {
            return Datatables::of(Branch::query()->select('*'))->addIndexColumn()
                ->addColumn('action', function (Branch $branch) {
                    return '<button type="button" class="btn btn-primary btn-sm branch-action-update" data-info="' . $branch->BranchID . '~' . $branch->Name . '~' . $branch->Address . '~' . $branch->Address2 . '~' . $branch->Phone . '~' . $branch->Email . '"
                       data-manager="' . $branch->manager?->UserID . '~' . $branch->manager?->Name . '" data-operation="' . $branch->operation?->UserID . '~' . $branch->operation?->Name . '" data-route="' . route('branches.update', [$branch->Id]) . '" ><i class="fas fa-edit"></i> edit</button>
                         <button type="button" class="btn btn-danger btn-sm  branch-action-trash" data-info="' . $branch->BranchID . '~' . $branch->Name . '"  data-route="' . route('branches.destroy', [$branch->Id]) . '"><i class="fas fa-trash"></i> trash</button>';
                })->addColumn('Manager', function (Branch $branch) {
                    return $branch->manager?->Name;
                })->editColumn('Address', function (Branch $branch) {
                    return $branch->Address ?? '';
                })->editColumn('Address2', function (Branch $branch) {
                    return $branch->Address2 ?? '';
                })->addColumn('Operation', function (Branch $branch) {
                    return $branch->operation?->Name;
                })->rawColumns(['action', 'Manager', 'Operation'])->make();
        }
        return view('settings.branches.index');
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(BranchRequest $request): JsonResponse
    {
        $userID = $request->getManager()?->Id ?? null;
        $Operation = $request->getOperation()?->Id ?? null;
        $branchID = $request->getBranchID();
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($branchID, $Operation, $actor, $request, $userID) {
                $crmBranch = Branch::create([
                    'Name' => $request->string('Name'),
                    'Address' => $request->string('Address', ''),
                    'Address2' => $request->string('Address2', ''),
                    'Phone' => $request->string('Phone', ''),
                    'Email' => $request->string('Email', ''),
                    'BranchID' => $branchID,
                    'UserId' => $userID,
                    'OperationId' => $Operation,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => now(),
                    'UpdatedOn' => now(),
                ]);

                activity()->causedBy($actor)->performedOn($crmBranch->refresh())->event('create')->log('created a branch ' . $crmBranch->BranchID);

            });
        } catch (Throwable|Exception $e) {
            Log::error('Error creating branch failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('branch added successfully');
    }

    public function update(BranchRequest $request, Branch $crmBranch): JsonResponse
    {
        $userID = $request->getManager()?->Id ?? null;
        $Operation = $request->getOperation()?->Id ?? null;
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($crmBranch, $Operation, $actor, $request, $userID) {

                $crmBranch->update([
                    'Name' => $request->string('Name'),
                    'Address' => $request->string('Address', ''),
                    'Address2' => $request->string('Address2', ''),
                    'Phone' => $request->string('Phone', ''),
                    'Email' => $request->string('Email', ''),
                    'UserId' => $userID,
                    'OperationId' => $Operation,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($crmBranch)->event('update')->log('updated branch ' . $crmBranch->BranchID);

            });
        } catch (Throwable|Exception $e) {
            Log::error('Error updating branch failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('branch updated successfully');
    }

    public function destroy(Request $request, Branch $crmBranch): JsonResponse
    {
        try {
            DB::transaction(static function () use ($request, $crmBranch) {
                $crmBranch->forceFill([
                    'DeletedOn' => now(),
                    'DeletedBy' => $request->user()->Id,
                ])->save();
            });
        } catch (Throwable|Exception $e) {
            Log::error('Error trashing branch failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('branch trashed successfully');
    }
}
