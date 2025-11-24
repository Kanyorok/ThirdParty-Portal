<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
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
use Illuminate\Support\Facades\Log;

class GLMappingController extends Controller
{
    public function index()
    {
<<<<<<< HEAD
        $mappings = FinanceGLMapping::with('modules:ModuleID,Name', 'transactions:Id,Name', 'debitAccount:Id,GLName', 'creditAccount:Id,GLName')
            ->orderBy('Id', 'desc')->get();
=======
        $this->authorize(PermissionEnum::FinanceGLMappingView, FinanceGLMapping::class);
        $mappings = FinanceGLMapping::with('modules:ModuleID,Name','transactions:Id,Name', 'debitAccount:Id,GLName,GLCode', 'creditAccount:Id,GLName,GLCode')
            ->orderBy('Id','desc')->get();
>>>>>>> origin

        $glaccounts = FinanceGLAccounts::select('Id','GLName')->get();
        $moduleIds=FinanceModuleTransactions::distinct()->pluck('ModuleID')->toArray();
        $modules = Module::select('ModuleID','Name')->whereIn('ModuleID', $moduleIds)
            ->where('ParentID', null)
            ->orderBy('Name', 'asc')->get();
        $transactionTypes = FinanceModuleTransactions::all();

        return view('finance.integration.glmapping.index', compact('mappings', 'glaccounts', 'modules', 'transactionTypes'));
    }

    public function create()
    {
<<<<<<< HEAD
        $glaccounts = FinanceGLAccounts::select('Id', 'GLName')->get();
        $moduleIds = FinanceModuleTransactions::distinct()->pluck('ModuleID')->toArray();
        $modules = Module::select('ModuleID', 'Name')->whereIn('ModuleID', $moduleIds)
=======
        $this->authorize(PermissionEnum::FinanceGLMappingCreate, FinanceGLMapping::class);
        $glaccounts = FinanceGLAccounts::select('Id','GLName')->get();
        $moduleIds=FinanceModuleTransactions::distinct()->pluck('ModuleID')->toArray();
        $modules = Module::select('ModuleID','Name')->whereIn('ModuleID', $moduleIds)
>>>>>>> origin
            ->where('ParentID', null)
            ->orderBy('Name', 'asc')->get();
        $transactionTypes = FinanceModuleTransactions::all();
        return view('finance.integration.glmapping.create', compact('transactionTypes', 'modules', 'glaccounts'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceGLMappingCreate, FinanceGLMapping::class);

<<<<<<< HEAD
        $glmaps = FinanceGLMapping::create([
            'ModuleID' => $validated['ModuleID'],
            'TransactionTypeID' => $validated['TransactionType'],
            'DebitGLAccountID' => $validated['DebitGLAccountID'],
            'CreditGLAccountID' => $validated['CreditGLAccountID'],
            'IsActive' => $request->has('IsActive') ? 1 : 0,
            'CreatedBy' => Auth::Id(),
            'ModifiedBy' => Auth::Id(),
        ]);
=======
        // Check if the required tables exist and have data
        try {
            $moduleCount = DB::table('t_Modules')->count();
            $transactionTypeCount = DB::table('t_FinanceTransactionTypes')->count();
            $glAccountCount = DB::table('t_FinanceGLAccounts')->count();
>>>>>>> origin

        } catch (\Exception $e) {
            Log::error('Database table check failed:', ['error' => $e->getMessage()]);
        }

        try {
            $validated = $request->validate([
                'ModuleID' => 'required|string|exists:t_Modules,ModuleID',
                'TransactionType' => 'required|string|exists:t_FinanceTransactionTypes,Id',
                'DebitGLAccountID' => 'required|string|exists:t_FinanceGLAccounts,Id',
                'CreditGLAccountID' => 'required|string|exists:t_FinanceGLAccounts,Id',
                // 'IsActive' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('GL Mapping Validation Failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            throw $e;
        }

        try {
            $glmaps = FinanceGLMapping::create([
                'ModuleID' => $validated['ModuleID'],
                'TransactionTypeID' => $validated['TransactionType'],
                'DebitGLAccountID' => $validated['DebitGLAccountID'],
                'CreditGLAccountID' => $validated['CreditGLAccountID'],
                'IsActive' => $request->has('IsActive') ? 1 : 0,
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            activity('GL Mapping Creation')
                ->performedOn($glmaps)
                ->causedBy(Auth::id())
                ->withProperties(['Create' => $glmaps])
                ->log('Created GL Mapping');

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'GL Mapping saved successfully.'
                ]);
            }

            return redirect()->route('glpostingmap.index')->with('success', 'GL Mapping saved successfully.');
        } catch (\Exception $e) {
            Log::error('GL Mapping Creation Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create GL Mapping: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to create GL Mapping: ' . $e->getMessage());
        }
    }

    public function fetchTransactionTypes($selectedModule, Request $request)
    {
        $types = FinanceModuleTransactions::where('ModuleID', $selectedModule)
            ->with('transactions:Id,Name')
            ->get();

        return response()->json($types);
    }
<<<<<<< HEAD
=======
    public function edit($id)
    {
        $this->authorize(PermissionEnum::FinanceGLMappingUpdate, FinanceGLMapping::class);

        $mapping = FinanceGLMapping::with('modules:ModuleID,Name','transactions:Id,Name', 'debitAccount:Id,GLName', 'creditAccount:Id,GLName')->findOrFail($id);

        // Check if this is an AJAX request
        if (request()->expectsJson()) {
            return response()->json([
                'ModuleID' => $mapping->ModuleID,
                'TransactionTypeID' => $mapping->TransactionTypeID,
                'DebitGLAccountID' => $mapping->DebitGLAccountID,
                'CreditGLAccountID' => $mapping->CreditGLAccountID,
                'IsActive' => $mapping->IsActive,
            ]);
        }

        $glaccounts = FinanceGLAccounts::select('Id','GLName')->get();
        $moduleIds=FinanceModuleTransactions::distinct()->pluck('ModuleID')->toArray();
        $modules = Module::select('ModuleID','Name')->whereIn('ModuleID', $moduleIds)
            ->where('ParentID', null)
            ->orderBy('Name', 'asc')->get();
        $transactionTypes = FinanceModuleTransactions::all();

        return view('finance.integration.glmapping.edit', compact('mapping', 'transactionTypes', 'modules', 'glaccounts'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceGLMappingUpdate, FinanceGLMapping::class);

        $mapping = FinanceGLMapping::findOrFail($id);

        $validated = $request->validate([
            'ModuleID' => 'required|string|exists:t_Modules,ModuleID',
            'TransactionType' => 'required|string|exists:t_FinanceTransactionTypes,Id',
            'DebitGLAccountID' => 'required|string|exists:t_FinanceGLAccounts,Id',
            'CreditGLAccountID' => 'required|string|exists:t_FinanceGLAccounts,Id',
            // 'IsActive' => 'nullable|boolean',
        ]);

        $mapping->update([
            'ModuleID' => $validated['ModuleID'],
            'TransactionTypeID' => $validated['TransactionType'],
            'DebitGLAccountID' => $validated['DebitGLAccountID'],
            'CreditGLAccountID' => $validated['CreditGLAccountID'],
            'IsActive' => $request->has('IsActive') ? 1 : 0,
            'ModifiedBy' => Auth::id(),
        ]);

        activity('GL Mapping Update')
            ->performedOn($mapping)
            ->causedBy(Auth::id())
            ->withProperties(['Update' => $mapping])
            ->log('Updated GL Mapping');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'GL Mapping updated successfully.'
            ]);
        }

        return redirect()->route('glpostingmap.index')->with('success', 'GL Mapping updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::FinanceGLMappingDelete, FinanceGLMapping::class);

        $mapping = FinanceGLMapping::findOrFail($id);

        activity('GL Mapping Deletion')
            ->performedOn($mapping)
            ->causedBy(Auth::id())
            ->withProperties(['Delete' => $mapping])
            ->log('Deleted GL Mapping');

        $mapping->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'GL Mapping deleted successfully.'
            ]);
        }

        return redirect()->route('glpostingmap.index')->with('success', 'GL Mapping deleted successfully.');
    }
>>>>>>> origin

    public function list()
    {
        $this->authorize(PermissionEnum::FinanceGLMappingView, FinanceGLMapping::class);
        $accounts = DB::table('t_FinanceGLAccounts')  // Table name
        ->select('Id', 'GLName')            // Columns we need
        ->orderBy('GLName')                 // Sort for better UX
        ->get();

        return response()->json($accounts);   // Send data as JSON
    }
}
