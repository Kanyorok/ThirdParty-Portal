<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Auth\ModelRole;
use App\Models\Core\CodeDetail;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceGLSubAccountTypes;
use App\Models\Finance\FinanceGLTypeGroup;
use Couchbase\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChartOfAccountsController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceCOAView,FinanceGLAccounts::class);
        $charts = FinanceGLAccounts::select('Id','GLSubAccountTypeID','GLTypeGroupID','GLCode', 'GLName','GLAccountTypeID','IsActive','Description')
                    ->with('typeGroup:Id,Description','subAccount:Id,Description')->latest()->get();

        return view('finance.chartofaccounts.chartofaccounts.index', compact('charts'));
    }

    public function create()
    {
        $accountTypes = CodeDetail::select('CodeID','Value','Description')->where('CodeID','GLAccountType')->get();
        $typeGroups = FinanceGLTypeGroup::select('Id','Description')->get();
        $subAccountTypes = FinanceGLSubAccountTypes::select('Id','Description','GLTypeGroupId')->get();
        $allGLAccounts = FinanceGLAccounts::select('Id','GLCode', 'GLName','GLSubAccountTypeID')->get();

        return view('finance.chartofaccounts.chartofaccounts.create', compact(
            'accountTypes', 'typeGroups', 'subAccountTypes', 'allGLAccounts'
        ));
    }

    public function getTypeGroups(Request $request)
    {
        $typeGroups = FinanceGLTypeGroup::where('GLAccountTypeID', $request->GLAccountTypeID)->get();
        return response()->json($typeGroups);
    }

    public function getSubAccountTypes(Request $request)
    {
        $subTypes = FinanceGLSubAccountTypes::where('GLTypeGroupId', $request->GLTypeGroupID)->get();
        return response()->json($subTypes);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            //'GLCode'             => 'required|string',
            'GLName'             => 'required|string|max:50',
            'GLAccountTypeID'    => 'required',
            'GLTypeGroupID'      => 'required|exists:t_FinanceGLTypeGroups,Id',
            'GLSubAccountTypeID' => 'required|exists:t_FinanceGLSubAccountTypes,Id',
            //'ParentGLID'         => 'nullable|integer|exists:t_FinanceGLAccounts,Id',
            'Description'        => 'required|string|max:255',
            'IsActive'           => 'required|boolean'
        ]);

        DB::beginTransaction();

        try{
            $branchID=ModelRole::where('model_id',Auth::id())->pluck('BranchID')->first();
            $charts = FinanceGLAccounts::create([
                //'GLCode'             => $validated['GLCode'],
                'GLName'             => $validated['GLName'],
                'GLAccountTypeID'    => $validated['GLAccountTypeID'],
                'GLTypeGroupID'      => $validated['GLTypeGroupID'],
                'GLSubAccountTypeID' => $validated['GLSubAccountTypeID'],
                'BranchID'=>$branchID,
                //'ParentGLID'         => $validated['ParentGLID'] ?? null,
                'Description'        => $validated['Description'],
                //'IsActive'           => $validated['IsActive'],
                'CreatedBy'          =>Auth::Id(),
                'ModifiedBy'         => Auth::Id(),
            ]);

            // dd ($charts);

            activity()
                ->performedOn($charts)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created budget activity: ' . $charts->GLName);

            DB::commit();

            return redirect()->route('chartofaccounts.index')->with('success', 'General Ledger Account created Successfully.');
        }catch(QueryException $e){
            if ($e->getCode() == '23000') { // SQL duplicate error
                Log::error('Duplicate GL Code: ' . $e->getMessage());
                return back()->with('error', 'A General Ledger Account with this GL Code already exists.');
            }
        }
        catch(\Throwable $th){
            DB::rollBack();
            //return $th->getMessage();
            Log::error ('Failed to create General Ledger Account:' .  $th->getMessage());

            return back()->with('error','Failed to create General Ledger Account:' .  $th->getMessage());
        }
    }

    public function hierarchy()
    {
        $accounts = DB::table('t_GLAccounts')->orderBy('GLCode')->get();
        return view('finance.chartofaccounts.chartofaccounts.account_hierarchy', compact('accounts'));
    }

    public function show($code)
    {
        $account = DB::table('t_GLAccounts')->where('GLCode', $code)->first();

        if (!$account) {
            abort(404, 'Account not found.');
        }

        return view('finance.chartofaccounts.chartofaccounts.show', compact('account'));
    }
}
