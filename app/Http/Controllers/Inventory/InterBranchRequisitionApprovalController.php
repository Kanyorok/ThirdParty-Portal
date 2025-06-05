<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\InterBranchRequisitionItem;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InterBranchRequisitionApprovalController extends Controller
{
    public function index(Request $request)
    {
        $pendingRequisitions = InterBranchRequisition::where('Status', 'Pending Approval')->get();

        $requisition = null;
        if ($request->has('ReqId') && !empty($request->ReqId)) {
            $requisition = InterBranchRequisition::where('Id', $request->ReqId)->first();

            if ($requisition) {
                $requisition->load(['fromBranch', 'toBranch', 'creator', 'items', 'items.item', 'items.uom']);
                $requisition->CurrentApprLevel = $this->getApprovalLevelFromStatus($requisition->Status);
            } else {
                return redirect()->back()->with('error', 'Selected requisition not found.');
            }
        }

        // Pass $requisition (not $items) to the view for clarity
        return view('inventory.interbranchrequisition.approval.index', [
            'pendingRequisitions' => $pendingRequisitions,
            'requisition' => $requisition,
        ]);
    }

    private function getApprovalLevelFromStatus($status)
    {
        return match ($status) {
            'Draft' => 'Level 1',
            'Submitted' => 'Submitted Awaiting Approval',
            'Pending Approval' => 'Pending Approval',
            'Approved' => 'Approved',
            'Rejected' => 'Requisition Rejected',
            default => 'N/A',
        };
    }

    public function submitDecision(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'ReqId' => 'required|numeric',
            'role' => 'required|string',
            'action' => 'required|in:APPROVED,REJECTED,COMMENTED',
            'comments' => 'required|string|max:1000',
            'approved_qty' => 'array',
            'item_remarks' => 'array',
        ]);

        $requisition = InterBranchRequisition::findOrFail($request->ReqId);

        // 1. Update per-item approved qty and remarks
        if ($request->has('approved_qty')) {
            foreach ($request->approved_qty as $itemId => $qty) {
                $item = $requisition->items()->find($itemId);
                if ($item) {
                    $item->ApprovedQty = $qty;
                    $item->Remarks = $request->item_remarks[$itemId] ?? $item->Remarks;
                    $item->ModifiedBy = $user->Id;
                    $item->ModifiedOn = Carbon::now();
                    $item->save();
                }
            }
        }

        // 2. Update requisition status
        switch ($request->action) {
            case 'APPROVED':
                $requisition->Status = 'Approved';
                break;
            case 'REJECTED':
                $requisition->Status = 'Rejected';
                break;
            case 'COMMENTED':
                // No status change
                break;
        }

        
        $requisition->ModifiedBy = $user->Id;
        $requisition->ModifiedOn = Carbon::now();
        $requisition->save();

        // 3. Log in workflow history (t_Workflows)
        Workflow::create([
            'Source' => 'InterBranchRequisition',
            'SourceID' => $requisition->Id,
            'Stage' => $this->getApprovalLevelFromStatus($requisition->Status),
            'Status' => match ($request->action) {
                'APPROVED' => 'Ap',
                'REJECTED' => 'Re',
                'COMMENTED' => 'Cm',
            },
            'Notes' => $request->comments,
            'CreatedBy' => $user->Id,
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => $user->Id,
            'ModifiedOn' => Carbon::now(),
        ]);

        // 4. Update/remove pending workflow (t_PendingWorkflow)
        $pending = PendingWorkflow::where([
            'Source'   => 'InterBranchRequisition',
            'SourceID' => $requisition->Id,
        ])->first();

        if ($pending) {
            if ($request->action === 'APPROVED' && $this->hasNextApprovalLevel($requisition)) {
                // Assign to next approver
                $pending->Stage = $this->getNextApprovalLevel($requisition);
                $pending->AssignedTo = $this->getNextApproverId($requisition); // Implement this logic as needed
                $pending->ModifiedBy = $user->Id;
                $pending->ModifiedOn = Carbon::now();
                $pending->save();
            } else {
                // Remove pending workflow (final approval or rejection)
                $pending->delete();
            }
        }

        // 5. Audit log
        activity()
            ->causedBy($user)
            ->performedOn($requisition)
            ->event(strtolower($request->action))
            ->log("{$request->action} inter-branch requisition (ID: {$requisition->Id}) with comment: '{$request->comments}'");

        return redirect()->route('interbranchrequisitionapproval.index')
            ->with('success', 'Your decision has been recorded.');
    }

    // Placeholder: determine if there's another approval level
    private function hasNextApprovalLevel($requisition)
    {
        // Implement your business logic for multi-level approval here
        // Example: return false if only one level, true if more levels remain
        return false;
    }

    // Placeholder: get next approval level name/identifier
    private function getNextApprovalLevel($requisition)
    {
        // Implement your business logic for next level here
        return 'Level 2';
    }

    // Placeholder: get next approver's user id
    private function getNextApproverId($requisition)
    {
        // Implement your business logic for assigning to the next approver
        // Example: find user with role 'BranchManager' at destination branch, etc.
        return null;
    }

    public function create()
    {
        return view('inventory.interbranchrequisition.approval.create');
    }
}