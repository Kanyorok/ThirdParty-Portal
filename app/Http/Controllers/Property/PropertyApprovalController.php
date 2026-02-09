<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\ApprovalEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\PropertyManagement\PropertyLeaseRenewal;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyUnit;
use App\Services\Workflow\ApprovalWorkflow;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $renewalapprovals = PropertyLeaseRenewal::with([
        'lease.tenant.thirdParty',
        'lease.property',
        'lease.unit',
        'lease.block',
        'lease.floor',
        ])->where('Status', ApprovalEnum::Pending->value)->get();

        return view('property.tenantmanagement.leasemanagement.approval.index', compact('approvals', 'terminationapprovals', 'renewalapprovals'));
    }

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

                $this->workflow->approve($lease, $user, ApprovalEnum::Approved, 'Approved via UI', statusColumn: 'ApprovalStatus');
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error approving lease: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Unexpected error, try again later.');
        } finally {
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

        // Availability of the property Unit
            $unit = PropertyUnit::findOrFail($termination->lease->Unit);
            $unit->update([
                'IsRentable' => 1,   // Unit can now be rented again
                'CurrentStatus' => 1,   // Status = Available
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);


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

    public function approveRenewal($Id)
    {
        $renewal = PropertyLeaseRenewal::findOrFail($Id);

        $this->authorize('approve', $renewal);

        $user = Auth::user();

        if (! $this->workflow->canApproveModel($renewal, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to approve this renewal.']);
        }

        $lock = Cache::lock('approve-PropertyLeaseRenewal-' . $renewal->Id, 5);
        if (! $lock->get()) {
            return redirect()->back()->with('error', 'Renewal has been approved, or another user is working on it.');
        }

        try {
            DB::transaction(function () use ($renewal, $user) {
                // Use 'Status' column for renewal approvals
                $this->workflow->approve($renewal, $user, ApprovalEnum::Approved, 'Approved via UI', statusColumn: 'Status');
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error approving renewal: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Unexpected error, try again later.');
        } finally {
            // Release lock
            optional($lock)->release();
        }

        return redirect()->route('propertyapproval.index')->with('success', 'Renewal approved successfully.');
    }

    public function rejectRenewal(Request $request, $Id)
    {
        $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $renewal = PropertyLeaseRenewal::findOrFail($Id);

        $this->authorize('reject', $renewal);

        $user = $request->user();

        if (! $this->workflow->canApproveModel($renewal, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to reject this renewal.']);
        }

        $lock = Cache::lock('approve-PropertyLeaseRenewal-' . $renewal->Id, 5);
        if (! $lock->get()) {
            return redirect()->back()->with('error', 'Renewal has been processed, or another user is working on it.');
        }

        $reason = $request->input('reason');

        try {
            DB::transaction(function () use ($renewal, $user, $reason) {
                // Ensure workflow updates the 'Status' column for renewals
                $this->workflow->reject($renewal, $user, ApprovalEnum::Rejected, $reason, statusColumn: 'Status');
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error rejecting renewal: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Unexpected error, try again later.');
        } finally {
            optional($lock)->release();
        }

        return redirect()->route('propertyapproval.index')->with('success', 'Renewal rejected successfully.');
    }
}
