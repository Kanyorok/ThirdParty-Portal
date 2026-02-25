<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\ThirdParty\SupplierMaster;
use App\Services\ThirdParties\SupplierWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierApprovalController extends Controller
{
    protected SupplierWorkflowService $workflowService;

    public function __construct(SupplierWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display a listing of suppliers pending approval.
     */
    public function index()
    {
        $this->authorize('viewAny', SupplierMaster::class);

        // Fetch all pending suppliers
        // In a real scenario, we might want to filter only those the user CAN approve here,
        // but often we list all pending and show actions based on permission.
        // Or strictly show only those waiting for THIS user.
        // For now, listing all Pending is a safe start, matching DepartmentNeed pattern.

        $suppliers = SupplierMaster::where('ApprovalStatus', ThirdPartyApprovalStatusEnum::Submitted)
            ->with(['party', 'party.types'])
            ->get();
        // Filter valid for this user?
        // dependent on requirements. DepartmentNeed lists all pending.

        return view('procurement.suppliers.approval.index', compact('suppliers'));
    }

    /**
     * Display the specified supplier with approval options.
     */
    public function show($id)
    {
        $supplier = SupplierMaster::where('SupplierID', $id)->firstOrFail();
        $this->authorize('view', $supplier);

        $supplier->load(['party', 'party.types', 'categories']);

        $user = Auth::user();
        $canApprove = $this->workflowService->canApproveSupplier($supplier, $user);
        $history = $this->workflowService->historyForSupplier($supplier);

        return view('procurement.suppliers.approval.show', compact('supplier', 'canApprove', 'history'));
    }

    /**
     * Approve the specified supplier.
     */
    public function approve(Request $request, $id)
    {
        $supplier = SupplierMaster::where('SupplierID', $id)->firstOrFail();
        $this->authorize('update', $supplier); // Using update policy as approval is an update

        $user = Auth::user();

        if (! $this->workflowService->canApproveSupplier($supplier, $user)) {
            $msg = $this->workflowService->getApprovalDetailsMessage(SupplierMaster::getPrimaryKey(), $id);

            return redirect()->back()->with('error', "You are not authorized to approve. " . $msg);
        }

        try {
            DB::transaction(function () use ($supplier, $user) {
                $this->workflowService->approve($supplier, $user, 'Approved via Approval Screen');
            });

            activity()
                ->causedBy($user)
                ->performedOn($supplier)
                ->event('approve')
                ->log("Approved supplier {$supplier->SupplierID}");

            return redirect()->route('suppliers-approval.index')->with('success', 'Supplier approved successfully.');
        } catch (\Exception $e) {
            Log::error('Supplier approval failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject the specified supplier.
     */
    public function reject(Request $request, $id)
    {
        $supplier = SupplierMaster::where('SupplierID', $id)->firstOrFail();
        $this->authorize('update', $supplier);

        $user = Auth::user();

        if (! $this->workflowService->canApproveSupplier($supplier, $user)) {
            $msg = $this->workflowService->getApprovalDetailsMessage(SupplierMaster::getPrimaryKey(), $id);

            return redirect()->back()->with('error', "You are not authorized to reject. " . $msg);
        }

        $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        try {
            DB::transaction(function () use ($supplier, $user, $request) {
                $this->workflowService->reject($supplier, $user, $request->input('reason'));
            });

            activity()
                ->causedBy($user)
                ->performedOn($supplier)
                ->event('reject')
                ->log("Rejected supplier {$supplier->SupplierID}");

            return redirect()->route('suppliers-approval.index')->with('success', 'Supplier rejected successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }
}
