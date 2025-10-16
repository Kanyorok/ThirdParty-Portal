<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Finance\FinanceCreditManagement;
use App\Models\Finance\FinanceInvoice;
use App\Models\Finance\FinanceCreditMovement;
use App\Models\ThirdParty\ThirdParties;
use App\Services\Finance\CreditManagementTransactionService;
use App\Services\Finance\CreditCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditManagementController extends Controller
{
    protected $creditService;

    public function __construct(CreditCalculationService $creditService)
    {
        $this->creditService = $creditService;
    }
    public function index(Request $request)
    {
        $query = FinanceCreditManagement::select('Id','CustomerID','CreditLimit','Status','EffectiveFrom','ExpiryDate')
            ->with(['customer:Id,ThirdPartyName,RegistrationNumber,Email']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('ThirdPartyName', 'like', "%{$search}%")
                  ->orWhere('RegistrationNumber', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%");
            })->orWhere('CreditLimit', 'like', "%{$search}%");
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('Status', $request->get('status'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('EffectiveFrom', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('EffectiveFrom', '<=', $request->get('date_to'));
        }

        // Credit limit range filter
        if ($request->filled('credit_from')) {
            $query->where('CreditLimit', '>=', $request->get('credit_from'));
        }
        if ($request->filled('credit_to')) {
            $query->where('CreditLimit', '<=', $request->get('credit_to'));
        }

        $sortField = $request->sort_by ?? 'Id';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int)($request->per_page ?? 25);
        $credits = $query
            ->paginate($perPage)
            ->appends($request->query())
            ->through(function (FinanceCreditManagement $c) {
                $utilization = $this->creditService->calculateCustomerCreditUtilization($c->CustomerID);

                // Update risk assessment for active credits FIRST
                if (strtolower($c->Status ?? '') === 'approved') {
                    $c->updateRiskAssessment($utilization['utilization']);
                    // Refresh to get the updated risk data
                    $c->refresh();
                }

                // Set as temporary attributes after all database operations
                $c->used = $utilization['used'];
                $c->utilization = $utilization['utilization'];
                $c->available = $utilization['available'];

                return $c;
            });

        return view('finance.accountsreceivable.creditmanagement.index', compact('credits'));
    }

    public function create(){
        $paymentTerms = CodeDetail::select('Value','Description')
            ->where('CodeID', 'PaymentTerm')
            ->orderBy('Description')
            ->get();
         $customers = ThirdParties::select('Id','ThirdPartyName','RegistrationNumber','Email')
            ->orderBy('ThirdPartyName')
            ->get();
        return view('finance.accountsreceivable.creditmanagement.create', compact('paymentTerms','customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'CustomerID'   => 'required|integer|exists:t_ThirdParties,Id',
            'CreditLimit'  => 'required|numeric|min:0',
            'PaymentTerms' => 'required|exists:t_CodeDetails,Value',
            'EffectiveFrom'=> 'required|date',
            'ExpiryDate'   => 'required|date|after_or_equal:EffectiveFrom',
            'Colleteral'   => 'required|string|max:255',
            'Remarks'      => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try{
            $creditManagement = FinanceCreditManagement::create([
                'CustomerID'     => $validated['CustomerID'],
                'CreditLimit'    => $validated['CreditLimit'],
                'PaymentTerms'   => $validated['PaymentTerms'],
                'EffectiveFrom'  => $validated['EffectiveFrom'],
                'ExpiryDate'     => $validated['ExpiryDate'],
                'Colleteral'     => $validated['Colleteral'],
                'Remarks'        => $validated['Remarks'],
                'Status'         => 'Pending',
                // 'ApprovalStatus' => 'Pending', // Now using Status column instead
                'ApprovalReason' => null,
                'CreatedBy'      => Auth::id(),
                'ModifiedBy'     => Auth::id(),
            ]);

            // Initialize movement: limit_set
            FinanceCreditMovement::create([
                'CreditID'      => $creditManagement->Id,
                'CustomerID'    => $creditManagement->CustomerID,
                'MovementType'  => 'limit_set',
                'Amount'        => $creditManagement->CreditLimit, // Positive because it increases available credit
                'ReferenceType' => 'credit_profile',
                'ReferenceID'   => $creditManagement->Id,
                'Notes'         => 'Initial credit limit set',
                'CreatedBy'     => Auth::id(),
                'CreatedOn'     => now(),
                'ModifiedBy'    => Auth::id(),
                'ModifiedOn'    => now(),
                'EffectiveOn'   => $creditManagement->EffectiveFrom ?? now(),
            ]);

            activity()->performedOn($creditManagement)
                ->withProperties(['action' => 'create'])
                ->causedBy(Auth::user())
                ->log('Created Credit Management for Customer ID: ' . $validated['CustomerID']);

            DB::commit();
            return redirect()->route('creditmanagement.index')->with('success', 'Credit Management created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            return redirect()->back()->withErrors(['error' => 'Failed to create credit management: ' . $th->getMessage()])->withInput();
        }
    }

    public function show($id){
         $credit = FinanceCreditManagement::with(['customer:Id,ThirdPartyName,RegistrationNumber,Email'])
            ->findOrFail($id);

        $utilization = $this->creditService->calculateCustomerCreditUtilization($credit->CustomerID);

        $used = $utilization['used'];
        $available = $utilization['available'];
        $util = $utilization['utilization'];

        // Update risk assessment based on current utilization
        $credit->updateRiskAssessment($util);

        // Refresh model to get updated risk data
        $credit->refresh();

        return view('finance.accountsreceivable.creditmanagement.show', compact('credit','used','available','util'));
    }

    public function history($id)
    {
        $credit = FinanceCreditManagement::with(['customer:Id,ThirdPartyName,RegistrationNumber,Email'])
            ->findOrFail($id);

        // Calculate current utilization using helper method
        $utilization = $this->creditService->calculateCustomerCreditUtilization($credit->CustomerID);

        $used = $utilization['used'];
        $available = $utilization['available'];
        $util = $utilization['utilization'];

        // Get all credit movements with proper date handling
        $movements = FinanceCreditMovement::where('CreditID', $credit->Id)
            ->orderBy('EffectiveOn', 'desc')
            ->orderBy('CreatedOn', 'desc')
            ->get()
            ->map(function ($movement) {
                // Ensure dates are properly cast
                if ($movement->EffectiveOn && !($movement->EffectiveOn instanceof \Carbon\Carbon)) {
                    $movement->EffectiveOn = \Carbon\Carbon::parse($movement->EffectiveOn);
                }
                if ($movement->CreatedOn && !($movement->CreatedOn instanceof \Carbon\Carbon)) {
                    $movement->CreatedOn = \Carbon\Carbon::parse($movement->CreatedOn);
                }
                return $movement;
            });

        return view('finance.accountsreceivable.creditmanagement.history',
            compact('credit', 'used', 'available', 'util', 'movements'));
    }

    public function edit($id)
    {
        $credit = FinanceCreditManagement::findOrFail($id);
        $paymentTerms = CodeDetail::select('Value','Description')
            ->where('CodeID', 'PaymentTerm')
            ->orderBy('Description')
            ->get();
        $customers = ThirdParties::select('Id','ThirdPartyName','RegistrationNumber','Email')
            ->orderBy('ThirdPartyName')
            ->get();
        return view('finance.accountsreceivable.creditmanagement.edit', compact('credit','paymentTerms','customers'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'CustomerID'   => 'required|integer|exists:t_ThirdParties,Id',
            'CreditLimit'  => 'required|numeric|min:0',
            'PaymentTerms' => 'required|exists:t_CodeDetails,Value',
            'EffectiveFrom'=> 'required|date',
            'ExpiryDate'   => 'required|date|after_or_equal:EffectiveFrom',
            'Colleteral'   => 'required|string|max:255',
            'Remarks'      => 'required|string|max:1000',
            'Status'       => 'required|string',
        ]);
        $credit = FinanceCreditManagement::findOrFail($id);

        // Disallow editing credit limit (or customer) once approved
        if (strtolower($credit->Status ?? '') === 'approved') {
            $limitChanged = ((float)$validated['CreditLimit']) !== ((float)$credit->CreditLimit);
            $customerChanged = (int)$validated['CustomerID'] !== (int)$credit->CustomerID;
            if ($limitChanged || $customerChanged) {
                return back()->with('error', 'Approved credit cannot change Customer or Credit Limit. Use Credit Adjustment for changes.');
            }
        }

        DB::beginTransaction();
        try {
            // If not approved yet and credit limit changed, remove previous initial movement and recreate to avoid duplicates
            $isPending = strtolower($credit->Status ?? '') === 'pending';
            $limitChanged = ((float)$validated['CreditLimit']) !== ((float)$credit->CreditLimit);
            if ($isPending && $limitChanged) {
                // Soft-delete existing 'limit_set' movements for this credit
                FinanceCreditMovement::where('CreditID', $credit->Id)
                    ->where('MovementType', 'limit_set')
                    ->delete();

                // Recreate initial movement with the new limit (positive amount per signed-amounts convention)
                FinanceCreditMovement::create([
                    'CreditID'      => $credit->Id,
                    'CustomerID'    => $credit->CustomerID,
                    'MovementType'  => 'limit_set',
                    'Amount'        => (float)$validated['CreditLimit'],
                    'ReferenceType' => 'credit_profile',
                    'ReferenceID'   => $credit->Id,
                    'Notes'         => 'Initial credit limit updated before approval',
                    'EffectiveOn'   => $validated['EffectiveFrom'] ?? now(),
                    'CreatedBy'     => Auth::id(),
                    'CreatedOn'     => now(),
                    'ModifiedBy'    => Auth::id(),
                    'ModifiedOn'    => now(),
                ]);
            }

            // Persist credit changes
            $credit->fill(array_merge($validated, [
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]));
            $credit->save();

            DB::commit();
            return redirect()->route('creditmanagement.show', $credit->Id)->with('success', 'Credit Management updated.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Failed to update credit management: ' . $th->getMessage());
        }
    }

    public function destroy($id)
    {
        $credit = FinanceCreditManagement::findOrFail($id);
        $credit->DeletedBy = Auth::id();
        $credit->save();
        $credit->delete();
        return redirect()->route('creditmanagement.index')->with('success', 'Credit Management deleted.');
    }

    public function approve(Request $request, $id)
    {

        $validated = $request->validate([
            'action_type' => 'required|in:approve,reject',
            'Reason' => 'required|string|max:1000',
        ]);

        $credit = FinanceCreditManagement::findOrFail($id);

        if (strtolower($credit->Status) !== 'pending') {
            return back()->with('error', 'This credit profile has already been processed.');
        }

        DB::beginTransaction();
        try {
            if ($validated['action_type'] === 'reject') {
                $credit->update([
                    'Status' => 'Rejected',
                    // 'ApprovalStatus' => 'rejected', // Now using Status column instead
                    'ApprovalReason' => $validated['Reason'],
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                activity('Credit Management Approval')
                    ->performedOn($credit)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'rejected'])
                    ->log("Rejected credit profile for {$credit->customer->ThirdPartyName}");

                DB::commit();
                return back()->with('success', 'Credit profile rejected successfully.');

            } elseif ($validated['action_type'] === 'approve') {
                $credit->update([
                    'Status' => 'Approved',
                    // 'ApprovalStatus' => 'approved', // Now using Status column instead
                    'ApprovalReason' => $validated['Reason'],
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                // Convert existing 'limit_set' into 'initial_approval' (avoid duplicate movements)
                $initialMovement = FinanceCreditMovement::where('CreditID', $credit->Id)
                    ->where('MovementType', 'limit_set')
                    ->latest('CreatedOn')
                    ->first();

                if ($initialMovement) {
                    // Update the existing record in-place
                    $initialMovement->update([
                        'MovementType' => 'initial_approval',
                        'Amount' => (float)$credit->CreditLimit,
                        'ReferenceType' => 'credit_profile',
                        'ReferenceID' => $credit->Id,
                        'Notes' => 'Initial credit limit approved: ' . $validated['Reason'],
                        'EffectiveOn' => $credit->EffectiveFrom ?? now(),
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);

                    // Hard-delete any other stray 'limit_set' rows for this credit to prevent duplicates
                    FinanceCreditMovement::where('CreditID', $credit->Id)
                        ->where('MovementType', 'limit_set')
                        ->where('Id', '!=', $initialMovement->Id)
                        ->delete();
                } else {
                    // Fallback: if no limit_set exists (edge case), create initial_approval
                    FinanceCreditMovement::create([
                        'CreditID' => $credit->Id,
                        'CustomerID' => $credit->CustomerID,
                        'MovementType' => 'initial_approval',
                        'Amount' => $credit->CreditLimit, // Positive because approval makes credit available
                        'ReferenceType' => 'credit_profile',
                        'ReferenceID' => $credit->Id,
                        'Notes' => 'Initial credit limit approved: ' . $validated['Reason'],
                        'EffectiveOn' => $credit->EffectiveFrom ?? now(),
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => now(),
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
                }

                // Post GL transactions for credit approval
                try {
                    $transactionService = app(CreditManagementTransactionService::class);
                    $glResult = $transactionService->postCreditApproval($credit, $validated['Reason']);

                    if ($glResult['status'] !== 'success' && $glResult['status'] !== 'exists') {
                        throw new \Exception('GL posting failed: ' . ($glResult['message'] ?? 'Unknown error'));
                    }
                } catch (\Throwable $e) {
                    // Log but don't fail the approval process
                    Log::warning('Credit approval GL posting failed', [
                        'credit_id' => $credit->Id,
                        'error' => $e->getMessage()
                    ]);
                }

                activity('Credit Management Approval')
                    ->performedOn($credit)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'approved', 'credit_limit' => $credit->CreditLimit])
                    ->log("Approved credit profile for {$credit->customer->ThirdPartyName}");

                DB::commit();
                return back()->with('success', 'Credit profile approved successfully and GL transactions posted.');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Failed to process approval: ' . $th->getMessage());
        }
    }
}
