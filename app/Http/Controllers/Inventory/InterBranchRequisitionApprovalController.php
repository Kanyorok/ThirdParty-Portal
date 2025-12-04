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

    $branchId = auth()->user()->employee?->BranchId;
    $currentBranch = Branch::findOrFail($branchId);
    $isHeadOffice = $currentBranch->IsHQ;

    $query = InterBranchRequisition::where('Status', '!=', InterBranchRequisitionEnum::Approved->value)
                ->where('Status', '!=', InterBranchRequisitionEnum::Rejected->value)->get();

    if (!$isHeadOffice) {
        $query->where('ToBranch', $branchId);
    }

    $pendingRequisitions = $query;
    
    $requisition = null;
    
    // Load selected requisition if ID is provided
    if ($request->filled('ReqId')) {
        $requisition = InterBranchRequisition::with(['fromBranch', 'toBranch', 'creator', 'items', 'items.item'])
            ->find($request->ReqId);
    }

    return view('inventory.interbranchrequisition.approval.index', [
        'pendingRequisitions' => $pendingRequisitions,
        'requisition' => $requisition,
        'isHeadOffice' => $isHeadOffice,
    ]);
}

    public function show($Id)
    {
        $requisition = InterBranchRequisition::findOrFail($Id);
        
        $this->authorize('view', $requisition);

        $branchId = auth()->user()->employee?->BranchId;
        $currentBranch = Branch::findOrFail($branchId);
        $isHeadOffice = $currentBranch->IsHQ;

        // Authorization check for non-HQ users
        if (!$isHeadOffice && $requisition->ToBranch != $branchId) {
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
            'isHeadOffice' => $isHeadOffice,
            'history' => $this->workflow->historyForModel($requisition),
        ]);
    }

    public function approve($Id)
    {
        $requisition = InterBranchRequisition::findOrFail($Id);
        
        $this->authorize('approve', $requisition);

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
                $this->workflow->approve($requisition, $user, InterBranchRequisitionEnum::Approved, 'Approved via UI',);
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error approving requisition: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unexpected error, try again later.');
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
            return redirect()->back()->with('error', 'Unexpected error, try again later.');
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
            'comments' => 'required|string|max:1000',
            'approved_qty' => 'array',
            'item_remarks' => 'array',
        ]);

        $user = Auth::user();
        $requisition = InterBranchRequisition::findOrFail($request->ReqId);
        
        $this->authorize('approve', $requisition);

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
            return redirect()->back()->with('error', 'Unexpected error, try again later.');
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