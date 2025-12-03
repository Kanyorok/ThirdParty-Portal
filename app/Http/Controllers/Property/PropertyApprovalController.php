<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\ApprovalEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Approval;
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
    public function __construct(ApprovalWorkflow $workflow)
    {
        $this->workflow = $workflow;  // Injected with codeId via service container
    }

    public function index()
    {
        $this->authorize('viewAny', PropertyNewLease::class);

        $approvals = PropertyNewLease::with(['tenant', 'property', 'block', 'floor', 'unit'])
            ->where('ApprovalStatus', '!=', ApprovalEnum::Approved)
            ->get();

        return view('property.tenantmanagement.leasemanagement.approval.index', compact('approvals'));
    }

    public function show($Id)
    {
        $lease = PropertyNewLease::findOrFail($Id);

        $this->authorize('view', $lease);
        $lease->load(['tenant', 'property', 'block', 'floor', 'unit']);

        $user = Auth::user();
        $canApprove = $this->workflow->canApproveModel($lease, $user);

        Log::info("Can approve lease {$lease->Id} for user {$user->Id}: " . ($canApprove ? 'Yes' : 'No'));

        return view('property.tenantmanagement.leasemanagement.approval.show', [
            'lease' => $lease,
            'canApprove' => $canApprove,
            'history' => $this->workflow->historyForModel($lease),
        ]);
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
}
