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
            'action' => 'required|in:APPROVED,REJECTED,COMMENTED',
            'comments' => 'required|string|max:1000',
            'approved_qty' => 'array',
            'item_remarks' => 'array',
        ]);

        $requisition = InterBranchRequisition::findOrFail($request->ReqId);

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

        switch ($request->action) {
            case 'APPROVED':
                $requisition->Status = 'Approved';
                break;
            case 'REJECTED':
                $requisition->Status = 'Rejected';
                break;
            case 'COMMENTED':
                
                break;
        }
        
        $requisition->ModifiedBy = $user->Id;
        $requisition->ModifiedOn = Carbon::now();
        $requisition->save();

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

       
        $pending = PendingWorkflow::where([
            'Source'   => 'InterBranchRequisition',
            'SourceID' => $requisition->Id,
        ])->first();

        
        if (!$pending && in_array($requisition->Status, ['Pending Approval', 'Submitted'])) {
            $pending = PendingWorkflow::create([
                'Source'     => 'InterBranchRequisition',
                'SourceID'   => $requisition->Id,
                'Stage'      => $this->getApprovalLevelFromStatus($requisition->Status),
                'Status'     => $request->action,
                'CreatedBy'  => $user->Id,
                'CreatedOn'  => Carbon::now(),
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => Carbon::now(),
            ]);
        }

       
        if ($pending) {
            $pending->Stage = $this->getApprovalLevelFromStatus($requisition->Status);
            $pending->Status = $request->action;
            $pending->ModifiedBy = $user->Id;
            $pending->ModifiedOn = Carbon::now();
            $pending->save();
        }

        activity()
            ->causedBy($user)
            ->performedOn($requisition)
            ->event(strtolower($request->action))
            ->log("{$request->action} inter-branch requisition (ID: {$requisition->Id}) with comment: '{$request->comments}'");

        return redirect()->route('interbranchrequisitionapproval.index')
            ->with('success', 'Your decision has been recorded.');
    }


    public static function createPendingWorkflowForRequisition($requisition, $user)
    {
        return PendingWorkflow::create([
            'Source'     => 'InterBranchRequisition',
            'SourceID'   => $requisition->Id,
            'Stage'      => 'Pending Approval',
            'Status'     => 'PENDING',
            'CreatedBy'  => $user->Id,
            'CreatedOn'  => Carbon::now(),
            'ModifiedBy' => $user->Id,
            'ModifiedOn' => Carbon::now(),
        ]);
    }

    
    public function create()
    {
        return view('inventory.interbranchrequisition.approval.create');
    }
}