<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceGLSubAccountTypes;
use App\Models\Finance\FinanceGLTypeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChartOfAccountsController extends Controller
{
    public function index()
    {
        $charts = FinanceGLAccounts::with('parent')->get();

        return view('finance.chartofaccounts.chartofaccounts.index', compact('charts'));
    }

    public function create()
    {
        $accountTypes = CodeDetail::where('CodeID','GLAccountType')->get();
        $typeGroups = FinanceGLTypeGroup::all();
        $subAccountTypes = FinanceGLSubAccountTypes::all();
        $allGLAccounts = FinanceGLAccounts::select('Id','GLCode', 'GLName')->get();

        return view('finance.chartofaccounts.chartofaccounts.create', compact(
            'accountTypes', 'typeGroups', 'subAccountTypes', 'allGLAccounts'
        ));
    }


    public function store(Request $request)
    {
        // dd($request->all());
         //return $request;
        $validated = $request->validate([
            'GLCode'             => 'required|string',
            'GLName'             => 'required|string|max:50',
            'GLAccountTypeID'    => 'required|exists:t_CodeDetails,CodeID',
            'GLTypeGroupID'      => 'required|exists:t_FinanceGLTypeGroups,Id',
            'GLSubAccountTypeID' => 'required|exists:t_FinanceGLSubAccountTypes,Id',
            'ParentGLID'         => 'nullable|integer|exists:t_FinanceGLAccounts,Id',
            'Description'        => 'required|string|max:255',
            'IsActive'           => 'required|boolean'
        ]);

        DB::beginTransaction();

        try{

            $charts = FinanceGLAccounts::create([
                'GLCode'             => $validated['GLCode'],
                'GLName'             => $validated['GLName'],
                'GLAccountTypeID'    => $validated['GLAccountTypeID'],
                'GLTypeGroupID'      => $validated['GLTypeGroupID'],
                'GLSubAccountTypeID' => $validated['GLSubAccountTypeID'],
                'ParentGLID'         => $validated['ParentGLID'] ?? null,
                'Description'        => $validated['Description'],
                'IsActive'           => $validated['IsActive'],
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

            return redirect()->route('chartofaccounts.index')->with('Success', 'General Ledger Account created Successfully.');
        }catch(\Throwable $th){

            DB::rollBack();
            return $th->getMessage();
            Log::error ('Failed to create General Ledger Account:' .  $th->getMessage());

            return back()->withErrors(['error'=>'Failed to create General Ledger Account:' .  $th->getMessage()]);
        }
    }

    public function getTypeGroups($accountTypeId)
    {
        $groups = DB::table('t_GLTypeGroups')
            ->where('GLAccountTypeID', $accountTypeId)
            ->select('Id', 'Description')
            ->get();

        return response()->json($groups);
    }

    public function getSubTypes($typeGroupId)
    {
        $subTypes = DB::table('t_GLSubAccountTypes')
            ->where('GLTypeGroupID', $typeGroupId)
            ->select('Id', 'Description')
            ->get();

        return response()->json($subTypes);
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
