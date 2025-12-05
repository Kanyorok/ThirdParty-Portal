<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\Inventory\InterBranchRequisitionEnum;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InterBranchRequisition;
use App\Services\Inventory\InterBranchRequisitionService;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Http\Request;
use App\Models\Core\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ErroredException;
use Exception;
use Illuminate\Http\RedirectResponse;

class InterBranchRequisitionApprovalController extends Controller
{
    protected InterBranchRequisitionService $service;
    protected ApprovalWorkflow $workflow;

    public function __construct(InterBranchRequisitionService $service, ApprovalWorkflow $workflow)
    {
        $this->service = $service;
        $this->workflow = new ApprovalWorkflow('InterBranchRequisitionStatus','Status');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', InterBranchRequisition::class);

        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        // SIMPLIFIED: Show requisitions where the current branch is ToBranch (receiving items)
        $pendingRequisitions = InterBranchRequisition::where('ToBranch', $branchId)
            ->where('Status', 'P') // Only pending status
            ->orderBy('CreatedOn', 'desc')
            ->get();
        
        $requisition = null;
        
        // Load selected requisition if ID is provided
        if ($request->filled('ReqId')) {
            $requisition = InterBranchRequisition::with(['fromBranch', 'toBranch', 'creator', 'items', 'items.item'])
                ->find($request->ReqId);
                
            // Verify that the requisition can be viewed by the logged-in user
            if ($requisition && $requisition->ToBranch != $branchId) {
                return redirect()->route('interbranchrequisitionapproval.index')
                    ->with('error', 'You can only view requisitions for your branch.');
            }
        }

        return view('inventory.interbranchrequisition.approval.index', [
            'pendingRequisitions' => $pendingRequisitions,
            'requisition' => $requisition,
            'isHeadOffice' => $currentBranch->IsHQ,
        ]);
    }

    public function show($Id)
    {
        $requisition = InterBranchRequisition::findOrFail($Id);
        
        $this->authorize('view', $requisition);

        $currentBranch = Auth::user()->branch;
        $branchId = $currentBranch->Id;

        // Authorization: User can only view requisitions where their branch is ToBranch
        if ($requisition->ToBranch != $branchId) {
            abort(403, 'You are not authorized to view this requisition.');
        }

        $requisition->load(['fromBranch', 'toBranch', 'creator', 'items', 'items.item']);
        $requisition->CurrentApprLevel = $this->service->getApprovalLevelFromStatus($requisition->Status);

        $user = Auth::user();
        $canApprove = $this->workflow->canApproveModel($requisition, $user);

        Log::info("Can approve requisition {$requisition->Id} for user {$user->Id}: " . ($canApprove ? 'Yes' : 'No'));

        return view('inventory.interbranchrequisition.approval.show', [
            'requisition' => $requisition,
            'canApprove' => $canApprove,
            'isHeadOffice' => $currentBranch->IsHQ,
            'history' => $this->workflow->historyForModel($requisition),
        ]);
    }

    public function approve($Id)
    {
        $requisition = InterBranchRequisition::findOrFail($Id);
        
        $this->authorize('approve', $requisition);

        // Additional check: User's branch must be ToBranch
        $currentBranch = Auth::user()->branch;
        if ($requisition->ToBranch != $currentBranch->Id) {
            return redirect()->back()->withErrors(['error' => 'You can only approve requisitions for your branch.']);
        }

        $user = Auth::user();

        if (!$this->workflow->canApproveModel($requisition, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to approve this requisition.']);
        }

        $lock = Cache::lock('approve-InterBranchRequisition-' . $requisition->Id, 5);
        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Requisition has been approved, or another user is working on it.');
        }

        try {
            DB::transaction(function () use ($requisition, $user) {
                $this->workflow->approve($requisition, $user, InterBranchRequisitionEnum::Approved, 'Approved via UI');
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error approving requisition: ' . $e->getMessage());
            
            // Display specific error messages from ApprovalWorkflow
            $errorMessage = 'Unexpected error, try again later.';
            $errorLower = strtolower($e->getMessage());
            
            if (str_contains($errorLower, 'cannot approve your own submission') || 
                str_contains($errorLower, 'maker-checker')) {
                $errorMessage = 'You cannot approve your own submission. Please have another user approve this requisition.';
            } elseif (str_contains($errorLower, 'already approved') || 
                     str_contains($errorLower, 'already actioned')) {
                $errorMessage = 'This requisition has already been approved or processed.';
            } elseif (str_contains($errorLower, 'not in approvable status') ||
                     str_contains($errorLower, 'no pending approval found')) {
                $errorMessage = 'This requisition cannot be approved in its current status or no pending approval found for your user.';
            } elseif (str_contains($errorLower, 'insufficient stock')) {
                $errorMessage = 'Insufficient stock available for one or more items.';
            }
            
            return redirect()->back()->with('error', $errorMessage);
        } finally {
            optional($lock)->release();
        }

        return redirect()->route('interbranchrequisitionapproval.index')->with('success', 'Requisition approved successfully.');
    }

    public function reject(Request $request, $Id)
    {
        $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $requisition = InterBranchRequisition::findOrFail($Id);
        
        $this->authorize('reject', $requisition);

        // Additional check: User's branch must be ToBranch
        $currentBranch = Auth::user()->branch;
        if ($requisition->ToBranch != $currentBranch->Id) {
            return redirect()->back()->withErrors(['error' => 'You can only reject requisitions for your branch.']);
        }

        $user = Auth::user();

        if (!$this->workflow->canApproveModel($requisition, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to reject this requisition.']);
        }

        $lock = Cache::lock('approve-InterBranchRequisition-' . $requisition->Id, 5);
        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Requisition has been processed, or another user is working on it.');
        }

        $reason = $request->input('reason');

        try {
            DB::transaction(function () use ($requisition, $user, $reason) {
                $this->workflow->reject($requisition, $user, InterBranchRequisitionEnum::Rejected, $reason);
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error rejecting requisition: ' . $e->getMessage());
            
            // Display specific error messages from ApprovalWorkflow
            $errorMessage = 'Unexpected error, try again later.';
            $errorLower = strtolower($e->getMessage());
            
            if (str_contains($errorLower, 'cannot approve your own submission') || 
                str_contains($errorLower, 'maker-checker')) {
                $errorMessage = 'You cannot reject your own submission. Please have another user review this requisition.';
            } elseif (str_contains($errorLower, 'already rejected') || 
                     str_contains($errorLower, 'already actioned')) {
                $errorMessage = 'This requisition has already been rejected or processed.';
            } elseif (str_contains($errorLower, 'not in rejectable status') ||
                     str_contains($errorLower, 'no pending approval found')) {
                $errorMessage = 'This requisition cannot be rejected in its current status or no pending approval found for your user.';
            }
            
            return redirect()->back()->with('error', $errorMessage);
        } finally {
            optional($lock)->release();
        }

        return redirect()->route('interbranchrequisitionapproval.index')->with('success', 'Requisition rejected successfully.');
    }

    public function submitDecision(Request $request)
    {
        $request->validate([
            'ReqId' => 'required|numeric',
            'action' => 'required|in:APPROVED,REJECTED',
            'comments' => 'required|string',
            'approved_qty' => 'array',
            'item_remarks' => 'array',
        ]);

        $user = Auth::user();
        $requisition = InterBranchRequisition::findOrFail($request->ReqId);
        
        $this->authorize('approve', $requisition);

        // Additional check: User's branch must be ToBranch
        $currentBranch = $user->branch;
        if ($requisition->ToBranch != $currentBranch->Id) {
            return redirect()->back()->withErrors(['error' => 'You can only process requisitions for your branch.']);
        }

        $lock = Cache::lock('approve-InterBranchRequisition-' . $requisition->Id, 5);
        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Requisition has been processed, or another user is working on it.');
        }

        try {
            DB::transaction(function () use ($requisition, $request, $user) {
                $this->service->submitDecision(
                    $requisition,
                    $request->action,
                    $request->comments,
                    $request->approved_qty ?? [],
                    $request->item_remarks ?? [],
                    $user
                );
            });
        } catch (Exception $e) {
            Log::error('Error processing requisition decision: ' . $e->getMessage());
            
            // Display specific error messages from ApprovalService
            $errorMessage = 'Unexpected error: ' . $e->getMessage();
            $errorLower = strtolower($e->getMessage());
            
            // Common error messages
            if (str_contains($errorLower, 'cannot approve your own submission') || 
                str_contains($errorLower, 'maker-checker')) {
                $errorMessage = 'You cannot approve or reject your own submission. Please have another user review this requisition.';
            } elseif (str_contains($errorLower, 'no pending approval found') || 
                     str_contains($errorLower, 'already actioned')) {
                $errorMessage = 'No pending approval found for your user or this requisition has already been processed.';
            } elseif (str_contains($errorLower, 'insufficient stock')) {
                $errorMessage = 'Insufficient stock available for one or more items.';
            } elseif (str_contains($errorLower, 'already processed')) {
                $errorMessage = 'This requisition has already been processed.';
            } elseif (str_contains($errorLower, 'not authorized')) {
                $errorMessage = 'You are not authorized to process this requisition.';
            } elseif (str_contains($errorLower, 'invalid status')) {
                $errorMessage = 'This requisition cannot be processed in its current status.';
            }
            
            return redirect()->back()->with('error', $errorMessage)->withInput();
        } finally {
            optional($lock)->release();
        }

        return redirect()->route('interbranchrequisitionapproval.index')
            ->with('success', 'Your decision has been recorded.');
    }

    public function create()
    {
        //return view('inventory.interbranchrequisition.approval.create');
    }
}