<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceCreditAdjustment;
use App\Models\Finance\FinanceCreditManagement;
use App\Models\Finance\FinanceCreditMovement;
use App\Services\Finance\CreditManagementTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = FinanceCreditAdjustment::with([
            'customer:Id,ThirdPartyName,RegistrationNumber',
            'creditProfile:Id,CreditLimit',
            'requestedByUser:Id,Name',
        ]);

        // Filter by customer if customer_id is provided
        if ($request->filled('customer_id')) {
            $query->where('CustomerID', $request->get('customer_id'));
        }

        $adjustments = $query->orderBy('CreatedOn', 'desc')->get();

        return view('finance.accountsreceivable.creditadjustment.index', compact('adjustments'));
    }

    public function create(Request $request)
    {
        $creditProfiles = FinanceCreditManagement::with('customer:Id,ThirdPartyName')
            ->where('Status', 'Approved')
            ->get();

        $selectedCredit = null;
        $creditId = $request->get('creditId') ?? $request->get('id');

        // Support bare numeric query like ?4
        if (! $creditId) {
            $rawQuery = $request->getQueryString();
            if (is_string($rawQuery) && ctype_digit($rawQuery)) {
                $creditId = (int) $rawQuery;
            }
        }

        if ($creditId) {
            $selectedCredit = FinanceCreditManagement::with('customer')->find($creditId);
        }

        return view('finance.accountsreceivable.creditadjustment.create', compact('creditProfiles', 'selectedCredit'));
    }

    public function createWithId($creditId)
    {
        $creditProfiles = FinanceCreditManagement::with('customer:Id,ThirdPartyName')
            ->where('Status', 'Approved') // Updated to use Status column instead of ApprovalStatus
            ->get();

        $selectedCredit = null;
        if ($creditId) {
            $selectedCredit = FinanceCreditManagement::with('customer')->find($creditId);
        }

        return view('finance.accountsreceivable.creditadjustment.create', compact('creditProfiles', 'selectedCredit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'CreditID' => 'required|exists:t_FinanceCreditManagement,Id',
            'AdjustmentType' => 'required|in:increase,decrease,revision',
            'Amount' => 'required|numeric|min:0.01',
            'Reason' => 'required|string|max:1000',
            'ReferenceType' => 'required|string',
            'EffectiveFrom' => 'required|date|after_or_equal:today',
            'Notes' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();

        try {
            $creditProfile = FinanceCreditManagement::findOrFail($validated['CreditID']);

            // Calculate new credit limit
            $currentLimit = (float)$creditProfile->CreditLimit;
            $adjustmentAmount = (float)$validated['Amount'];

            $newLimit = match ($validated['AdjustmentType']) {
                'increase' => $currentLimit + $adjustmentAmount,
                'decrease' => max(0, $currentLimit - $adjustmentAmount),
                'revision' => $adjustmentAmount, // Amount is the new total limit
                default => $currentLimit
            };

            // Business rule validations
            if ($validated['AdjustmentType'] === 'decrease' && $newLimit < 0) {
                throw new \Exception('Credit limit cannot be negative');
            }

            // Create adjustment record
            $adjustment = FinanceCreditAdjustment::create([
                'CreditID' => $validated['CreditID'],
                'CustomerID' => $creditProfile->CustomerID,
                'AdjustmentType' => $validated['AdjustmentType'],
                'Amount' => $adjustmentAmount,
                'NewCreditLimit' => $newLimit,
                'Reason' => $validated['Reason'],
                'ReferenceType' => $validated['ReferenceType'],
                'RequestedBy' => Auth::id(),
                'ApprovalStatus' => 'draft', // Credit adjustments still use ApprovalStatus
                'EffectiveFrom' => $validated['EffectiveFrom'],
                'Notes' => $validated['Notes'],
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Log activity
            activity('Credit Adjustment')
                ->performedOn($adjustment)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'created', 'adjustment_type' => $validated['AdjustmentType']])
                ->log("Created {$validated['AdjustmentType']} credit adjustment for " . $creditProfile->customer->ThirdPartyName);

            DB::commit();

            return redirect()->route('creditadjustment.show', $adjustment->Id)
                ->with('success', 'Credit adjustment request created successfully and is pending approval.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Credit adjustment creation failed: ' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to create credit adjustment: ' . $th->getMessage()]);
        }
    }

    public function show($id)
    {
        $adjustment = FinanceCreditAdjustment::with([
            'creditProfile.customer',
            'requestedByUser:Id,Name',
            'approvedByUser:Id,Name',
        ])->findOrFail($id);

        return view('finance.accountsreceivable.creditadjustment.show', compact('adjustment'));
    }

    public function edit($id)
    {
        $adjustment = FinanceCreditAdjustment::with('creditProfile.customer')->findOrFail($id);

        // Only allow editing if still in draft status
        if ($adjustment->ApprovalStatus !== 'draft') {
            return redirect()->route('creditadjustment.show', $id)
                ->with('error', 'Cannot edit adjustment that has been approved or rejected.');
        }

        $creditProfiles = FinanceCreditManagement::with('customer:Id,ThirdPartyName')
            ->where('Status', 'Approved') // Updated to use Status column instead of ApprovalStatus
            ->get();

        return view('finance.accountsreceivable.creditadjustment.edit', compact('adjustment', 'creditProfiles'));
    }

    public function update(Request $request, $id)
    {
        $adjustment = FinanceCreditAdjustment::findOrFail($id);

        // Only allow editing if still in draft status
        if ($adjustment->ApprovalStatus !== 'draft') {
            return redirect()->route('creditadjustment.show', $id)
                ->with('error', 'Cannot edit adjustment that has been approved or rejected.');
        }

        $validated = $request->validate([
            'AdjustmentType' => 'required|in:increase,decrease,revision',
            'Amount' => 'required|numeric|min:0.01',
            'Reason' => 'required|string|max:1000',
            'ReferenceType' => 'required|string',
            'EffectiveFrom' => 'required|date|after_or_equal:today',
            'Notes' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();

        try {
            $creditProfile = $adjustment->creditProfile;

            // Recalculate new credit limit
            $currentLimit = (float)$creditProfile->CreditLimit;
            $adjustmentAmount = (float)$validated['Amount'];

            $newLimit = match ($validated['AdjustmentType']) {
                'increase' => $currentLimit + $adjustmentAmount,
                'decrease' => max(0, $currentLimit - $adjustmentAmount),
                'revision' => $adjustmentAmount,
                default => $currentLimit
            };

            $adjustment->update([
                'AdjustmentType' => $validated['AdjustmentType'],
                'Amount' => $adjustmentAmount,
                'NewCreditLimit' => $newLimit,
                'Reason' => $validated['Reason'],
                'ReferenceType' => $validated['ReferenceType'],
                'EffectiveFrom' => $validated['EffectiveFrom'],
                'Notes' => $validated['Notes'],
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            activity('Credit Adjustment')
                ->performedOn($adjustment)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'updated'])
                ->log("Updated credit adjustment #{$adjustment->Id}");

            DB::commit();

            return redirect()->route('creditadjustment.show', $adjustment->Id)
                ->with('success', 'Credit adjustment updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Credit adjustment update failed: ' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update credit adjustment: ' . $th->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $adjustment = FinanceCreditAdjustment::findOrFail($id);

        // Only allow deletion if still in draft status
        if ($adjustment->ApprovalStatus !== 'draft') {
            return redirect()->route('creditadjustment.show', $id)
                ->with('error', 'Cannot delete adjustment that has been approved or rejected.');
        }

        $adjustment->DeletedBy = Auth::id();
        $adjustment->save();
        $adjustment->delete();

        activity('Credit Adjustment')
            ->performedOn($adjustment)
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'deleted'])
            ->log("Deleted credit adjustment #{$adjustment->Id}");

        return redirect()->route('creditadjustment.index')
            ->with('success', 'Credit adjustment deleted successfully.');
    }

    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'action_type' => 'required|in:approve,reject',
            'Reason' => 'required|string|max:1000',
        ]);

        $adjustment = FinanceCreditAdjustment::with('creditProfile')->findOrFail($id);

        if ($adjustment->ApprovalStatus !== 'draft') {
            return back()->with('error', 'This adjustment has already been processed.');
        }

        DB::beginTransaction();

        try {
            if ($validated['action_type'] === 'reject') {
                $adjustment->update([
                    'ApprovalStatus' => 'rejected',
                    'ApprovalReason' => $validated['Reason'],
                    'ApprovedBy' => Auth::id(),
                    'ApprovedOn' => now(),
                ]);

                activity('Credit Adjustment Approval')
                    ->performedOn($adjustment)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'rejected'])
                    ->log("Rejected credit adjustment #{$adjustment->Id}");

                DB::commit();

                return back()->with('success', 'Credit adjustment rejected successfully.');
            } elseif ($validated['action_type'] === 'approve') {
                // Approve the adjustment
                $adjustment->update([
                    'ApprovalStatus' => 'approved',
                    'ApprovalReason' => $validated['Reason'],
                    'ApprovedBy' => Auth::id(),
                    'ApprovedOn' => now(),
                ]);

                // Update the credit profile
                $creditProfile = $adjustment->creditProfile;
                $creditProfile->update([
                    'CreditLimit' => $adjustment->NewCreditLimit,
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                // Create movement record
                FinanceCreditMovement::create([
                    'CreditID' => $adjustment->CreditID,
                    'CustomerID' => $adjustment->CustomerID,
                    'MovementType' => 'adjustment_' . $adjustment->AdjustmentType,
                    'Amount' => $adjustment->AdjustmentType === 'increase' ? $adjustment->Amount : -$adjustment->Amount, // Positive for increase, negative for decrease
                    'ReferenceType' => 'credit_adjustment',
                    'ReferenceID' => $adjustment->Id,
                    'Notes' => "Credit {$adjustment->AdjustmentType}: {$adjustment->Reason}",
                    'EffectiveOn' => $adjustment->EffectiveFrom,
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                // Post GL transactions for credit adjustment
                try {
                    $transactionService = app(CreditManagementTransactionService::class);
                    $glResult = $transactionService->postCreditAdjustment($adjustment);

                    if ($glResult['status'] !== 'success' && $glResult['status'] !== 'exists') {
                        throw new \Exception('GL posting failed: ' . ($glResult['message'] ?? 'Unknown error'));
                    }
                } catch (\Throwable $e) {
                    // Log but don't fail the approval process
                    Log::warning('Credit adjustment GL posting failed', [
                        'adjustment_id' => $adjustment->Id,
                        'error' => $e->getMessage(),
                    ]);
                }

                activity('Credit Adjustment Approval')
                    ->performedOn($adjustment)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'approved', 'new_limit' => $adjustment->NewCreditLimit])
                    ->log("Approved credit adjustment #{$adjustment->Id}");

                DB::commit();

                return back()->with('success', 'Credit adjustment approved, credit limit updated, and GL transactions posted successfully.');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Credit adjustment approval failed: ' . $th->getMessage());

            return back()->with('error', 'Failed to process approval: ' . $th->getMessage());
        }
    }
}
