<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Core\Module;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceGLMapping;
use App\Models\Finance\FinanceModuleTransactions;
use App\Models\Finance\FinanceTransactionTypes;
use Database\Seeders\FinanceModuleTransactionSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GLMappingController extends Controller
{
    public function index()
    {
        $mappings = FinanceGLMapping::with('modules:ModuleID,Name','transactions:Id,Name', 'debitAccount:Id,GLName', 'creditAccount:Id,GLName')
            ->get();

        return view('finance.integration.glmapping.index', compact('mappings'));
    }

    public function create()
    {
        $glaccounts = FinanceGLAccounts::select('Id','GLName')->get();
        $modules = Module::where('ParentID', null)->get();
        $transactionTypes = FinanceModuleTransactions::all();
        return view('finance.integration.glmapping.create', compact('transactionTypes', 'modules', 'glaccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ModuleID' => 'required|string|exists:t_Modules,ModuleID',
            'TransactionType' => 'required|string|exists:t_TransactionTypes,Id',
            'DebitGLAccountID' => 'required|string|exists:t_FinanceGLAccounts,Id',
            'CreditGLAccountID' => 'required|string|exists:t_FinanceGLAccounts,Id',
            'IsActive' => 'nullable|boolean',
        ]);

        $glmaps = FinanceGLMapping::create([
            'ModuleID' => $validated['ModuleID'],
            'TransactionType' => $validated['TransactionType'],
            'DebitGLAccountID' => $validated['DebitGLAccountID'],
            'CreditGLAccountID' => $validated['CreditGLAccountID'],
            'IsActive' => $request->has('IsActive') ? 1 : 0,
            'CreatedBy'          =>Auth::Id(),
            'ModifiedBy'         => Auth::Id(),
        ]);

        return redirect()->route('glmapping.index')->with('success', 'GL Mapping saved successfully.');
    }

    public function fetchTransactionTypes($selectedModule, Request $request)
    {
        $moduleId = $request->input('ModuleID');

        $types = FinanceModuleTransactions::where('ModuleID', $selectedModule)
            ->with('transactions:Id,Name')
            ->get();

        return response()->json($types);
    }
    public function list()
    {
        $accounts = DB::table('t_FinanceGLAccounts')  // Table name
            ->select('Id', 'GLName')            // Columns we need
            ->orderBy('GLName')                 // Sort for better UX
            ->get();

        return response()->json($accounts);   // Send data as JSON
    }
}
