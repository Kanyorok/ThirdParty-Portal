<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\InterBranchRequisition;
use App\Policies\Inventory\InterBranchRequisitionPolicy;
use App\Services\Inventory\InterBranchRequisitionService;
use Illuminate\Support\Facades\Auth;
use App\Enums\Inventory\InterBranchRequisitionEnum;

class InterBranchRequisitionApprovalController extends Controller
{
    protected InterBranchRequisitionService $service;

    public function __construct(InterBranchRequisitionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        
        $pendingStatus = InterBranchRequisitionEnum::Submitted->value;
        $pendingRequisitions = InterBranchRequisition::where('Status', $pendingStatus)->get();

        $requisition = null;
        if ($request->has('ReqId') && !empty($request->ReqId)) {
            $requisition = InterBranchRequisition::where('Id', $request->ReqId)->first();

            if ($requisition) {
                $requisition->load(['fromBranch', 'toBranch', 'creator', 'items', 'items.item']);
                $requisition->CurrentApprLevel = $this->service->getApprovalLevelFromStatus($requisition->Status);
            } else {
                return redirect()->back()->with('error', 'Selected requisition not found.');
            }
        }

        return view('inventory.interbranchrequisition.approval.index', [
            'pendingRequisitions' => $pendingRequisitions,
            'requisition' => $requisition,
        ]);
    }

    
    public function submitDecision(Request $request)
    {
        $request->validate([
            'ReqId' => 'required|numeric',
            'action' => 'required|in:APPROVED,REJECTED',
            'comments' => 'required|string|max:1000',
            'approved_qty' => 'array',
            'item_remarks' => 'array',
        ]);

        $user = Auth::user();
        $requisition = InterBranchRequisition::findOrFail($request->ReqId);
        $this->authorize('approve', $requisition);
        $this->service->submitDecision(
            $requisition,
            $request->action,
            $request->comments,
            $request->approved_qty ?? [],
            $request->item_remarks ?? [],
            $user
        );

        return redirect()->route('interbranchrequisitionapproval.index')
            ->with('success', 'Your decision has been recorded.');
    }

    public function create()
    {
        return view('inventory.interbranchrequisition.approval.create');
    }
}