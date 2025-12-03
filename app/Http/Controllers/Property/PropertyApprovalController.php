<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\ApprovalEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Approval;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ErroredException;
use Exception;
use Illuminate\Http\RedirectResponse;

class PropertyApprovalController extends Controller
{
    protected ApprovalWorkflow $workflow;
    protected ApprovalWorkflow $workflowterm;

    public function __construct()
    {
        // Create workflow instances with the correct codeId for property approvals
        $this->workflow = new ApprovalWorkflow('ApprovalStatus', 'ApprovalStatus');
        $this->workflowterm = new ApprovalWorkflow('ApprovalStatus', 'Status');
    }

    public function index()
    {
        $this->authorize('viewAny', PropertyNewLease::class);

        $approvals = PropertyNewLease::with(['tenant', 'property', 'block', 'floor', 'unit'])
            ->where('ApprovalStatus', '!=', ApprovalEnum::Approved)
            ->get();

        $terminationapprovals = PropertyLeaseTermination::with(['lease', 'lease.tenant', 'code'])
            ->where('Status', '!=', ApprovalEnum::Approved)
            ->get();

        return view('property.tenantmanagement.leasemanagement.approval.index', compact('approvals', 'terminationapprovals'));
    }

    // public function show($Id)
    // {
    //     $lease = PropertyNewLease::findOrFail($Id);

    //     $this->authorize('view', $lease);
    //     $lease->load(['tenant', 'property', 'block', 'floor', 'unit']);

    //     $user = Auth::user();
    //     $canApprove = $this->workflow->canApproveModel($lease, $user);

    //     Log::info("Can approve lease {$lease->Id} for user {$user->Id}: " . ($canApprove ? 'Yes' : 'No'));

    //     return view('property.tenantmanagement.leasemanagement.approval.show', [
    //         'lease' => $lease,
    //         'canApprove' => $canApprove,
    //         //'history' => $this->workflow->historyForModel($lease),
    //     ]);
    // }

    public function approve($Id)
    {
        $lease = PropertyNewLease::findOrFail($Id);

        $this->authorize('approve', $lease);

        $user = Auth::user();

        if (! $this->workflow->canApproveModel($lease, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to approve this lease.']);
        }

        $lock = Cache::lock('approve-PropertyNewLease-' . $lease->Id, 5);
        if (! $lock->get()) {
            return redirect()->back()->with('error', 'Lease has been approved, or another user is working on it.');
        }

        try {
            DB::transaction(function () use ($lease, $user) {
                // Use 'ApprovalStatus' column for lease approvals (not the default 'Status')
                $this->workflow->approve($lease, $user, ApprovalEnum::Approved, 'Approved via UI', statusColumn: 'ApprovalStatus');
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error approving lease: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unexpected error, try again later.');
        } finally {
            // Release lock
            optional($lock)->release();
        }

        return redirect()->route('propertyapproval.index')->with('success', 'Lease approved successfully.');
    }

    public function reject(Request $request, $Id)
    {
        $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $lease = PropertyNewLease::findOrFail($Id);

        $this->authorize('reject', $lease);

        $user = $request->user();

        if (! $this->workflow->canApproveModel($lease, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to reject this lease.']);
        }

        $lock = Cache::lock('approve-PropertyNewLease-' . $lease->Id, 5);
        if (! $lock->get()) {
            return redirect()->back()->with('error', 'Lease has been processed, or another user is working on it.');
        }

        $reason = $request->input('reason');

        try {
            DB::transaction(function () use ($lease, $user, $reason) {
                // Ensure workflow updates the 'ApprovalStatus' column for leases
                $this->workflow->reject($lease, $user, ApprovalEnum::Rejected, $reason, statusColumn: 'ApprovalStatus');
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error rejecting lease: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unexpected error, try again later.');
        } finally {
            optional($lock)->release();
        }

        return redirect()->route('propertyapproval.index')->with('success', 'Lease rejected successfully.');
    }

    // public function showTermination($Id)
    // {
    //     $termination = PropertyLeaseTermination::findOrFail($Id);

    //     $this->authorize('view', $termination);
    //     $termination->load(['lease', 'lease.tenant', 'lease.property', 'lease.block', 'lease.floor', 'lease.unit', 'code']);

    //     $user = Auth::user();
    //     $canApprove = $this->workflowterm->canApproveModel($termination, $user);

    //     Log::info("Can approve termination {$termination->Id} for user {$user->Id}: " . ($canApprove ? 'Yes' : 'No'));

    //     return view('property.tenantmanagement.leasemanagement.approval.showTermination', [
    //         'termination' => $termination,
    //         'canApprove' => $canApprove,
    //         'history' => $this->workflowterm->historyForModel($termination),
    //     ]);
    // }

    public function approveTermination($Id)
    {
        $termination = PropertyLeaseTermination::findOrFail($Id);

        $this->authorize('approve', $termination);

        $user = Auth::user();

        if (! $this->workflowterm->canApproveModel($termination, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to approve this termination.']);
        }

        $lock = Cache::lock('approve-PropertyLeaseTermination-' . $termination->Id, 5);
        if (! $lock->get()) {
            return redirect()->back()->with('error', 'Termination has been approved, or another user is working on it.');
        }

        try {
            DB::transaction(function () use ($termination, $user) {
                // Create a separate workflow instance for terminations with correct codeId
                $terminationWorkflow = new ApprovalWorkflow('ApprovalStatus', 'Status');
                $terminationWorkflow->approve($termination, $user, ApprovalEnum::Approved, 'Approved via UI', statusColumn: 'Status');
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error approving termination: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unexpected error, try again later.');
        } finally {
            // Release lock
            optional($lock)->release();
        }

        return redirect()->route('propertyapproval.index')->with('success', 'Termination approved successfully.');
    }

    public function rejectTermination(Request $request, $Id)
    {
        $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $termination = PropertyLeaseTermination::findOrFail($Id);

        $this->authorize('reject', $termination);

        $user = $request->user();

        if (! $this->workflowterm->canApproveModel($termination, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to reject this termination.']);
        }

        $lock = Cache::lock('approve-PropertyLeaseTermination-' . $termination->Id, 5);
        if (! $lock->get()) {
            return redirect()->back()->with('error', 'Termination has been processed, or another user is working on it.');
        }

        $reason = $request->input('reason');

        try {
            DB::transaction(function () use ($termination, $user, $reason) {
                // Create a separate workflow instance for terminations with correct codeId
                $terminationWorkflow = new ApprovalWorkflow('ApprovalStatus', 'Status');
                $terminationWorkflow->reject($termination, $user, ApprovalEnum::Rejected, $reason, statusColumn: 'Status');
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error rejecting termination: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unexpected error, try again later.');
        } finally {
            optional($lock)->release();
        }

        return redirect()->route('propertyapproval.index')->with('success', 'Termination rejected successfully.');
    }
}
