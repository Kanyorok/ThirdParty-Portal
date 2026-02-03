<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\Inventory\InterBranchRequisitionEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\Inventory\InterBranchRequisition;
use App\Services\Inventory\InterBranchRequisitionService;
use App\Services\Workflow\ApprovalWorkflow;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InterBranchRequisitionApprovalController extends Controller
{
    protected InterBranchRequisitionService $service;
    protected ApprovalWorkflow $workflow;

    public function __construct(InterBranchRequisitionService $service, ApprovalWorkflow $workflow)
    {
        $this->service = $service;
        $this->workflow = new ApprovalWorkflow('InterBranchRequisitionStatus', 'Status');
        $this->workflow = new ApprovalWorkflow('InterBranchRequisitionStatus', 'Status');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', InterBranchRequisition::class);

        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        $pendingRequisitions = InterBranchRequisition::where('ToBranch', $branchId)
            ->where('Status', 'P')
            ->orderBy('CreatedOn', 'desc')
            ->get();

        $user = Auth::user();
        $pendingRequisitions = $pendingRequisitions->map(function ($requisition) use ($user) {
            $requisition->canApprove = $this->workflow->canApproveModel($requisition, $user);

            return $requisition;
        });

        $requisition = null;

        if ($request->filled('ReqId')) {
            $requisition = InterBranchRequisition::with(['fromBranch', 'toBranch', 'creator', 'items', 'items.item'])
                ->find($request->ReqId);

            if ($requisition && $requisition->ToBranch != $branchId) {
                return redirect()->route('interbranchrequisitionapproval.index')
                    ->with('error', 'You can only view requisitions for your branch.');
            }

            if ($requisition) {
                $requisition->canApprove = $this->workflow->canApproveModel($requisition, $user);
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

        if ($requisition->ToBranch != $branchId) {
            abort(403, 'You are not authorized to view this requisition.');
        }

        $requisition->load(['fromBranch', 'toBranch', 'creator', 'items', 'items.item']);
        $requisition->CurrentApprLevel = $this->service->getApprovalLevelFromStatus($requisition->Status);

        $user = Auth::user();
        $canApprove = $this->workflow->canApproveModel($requisition, $user);


        return view('inventory.interbranchrequisition.approval.show', [
            'requisition' => $requisition,
            'canApprove' => $canApprove,
            'isHeadOffice' => $currentBranch->IsHQ,
            'history' => $this->workflow->historyForModel($requisition),
        ]);
    }

    public function approve(Request $request, $Id)
    {
        $requisition = InterBranchRequisition::findOrFail($Id);

        $this->authorize('approve', $requisition);

        $currentBranch = Auth::user()->branch;
        if ($requisition->ToBranch != $currentBranch->Id) {
            $message = 'You can only approve requisitions for your branch.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $message,
                ], 403);
            }

            return redirect()->back()->withErrors(['error' => $message]);
        }

        $user = Auth::user();

        if (! $this->workflow->canApproveModel($requisition, $user)) {
            $message = 'You are not authorized to approve this requisition.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $message,
                ], 403);
            }

            return redirect()->back()->withErrors(['error' => $message]);
        }

        $lock = Cache::lock('approve-InterBranchRequisition-' . $requisition->Id, 5);
        if (! $lock->get()) {
            $message = 'Requisition has been approved, or another user is working on it.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $message,
                ], 409);
            }

            return redirect()->back()->with('error', $message);
        }

        try {
            DB::transaction(function () use ($requisition, $user) {
                $this->workflow->approve($requisition, $user, InterBranchRequisitionEnum::Approved, 'Approved via UI', 'Status');
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Requisition approved successfully.',
                ]);
            }

            return redirect()->route('interbranchrequisitionapproval.index')->with('success', 'Requisition approved successfully.');
        } catch (ErroredException $e) {
            $errorMessage = $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 400);
            }

            return redirect()->back()->with('error', $errorMessage);
        } catch (Exception $e) {

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

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 400);
            }

            return redirect()->back()->with('error', $errorMessage);
        } finally {
            optional($lock)->release();
        }
    }

    public function reject(Request $request, $Id)
    {
        $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $requisition = InterBranchRequisition::findOrFail($Id);

        $this->authorize('reject', $requisition);

        $currentBranch = Auth::user()->branch;
        if ($requisition->ToBranch != $currentBranch->Id) {
            $message = 'You can only reject requisitions for your branch.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $message,
                ], 403);
            }

            return redirect()->back()->withErrors(['error' => $message]);
        }

        $user = Auth::user();

        if (! $this->workflow->canApproveModel($requisition, $user)) {
            $message = 'You are not authorized to reject this requisition.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $message,
                ], 403);
            }

            return redirect()->back()->withErrors(['error' => $message]);
        }

        $lock = Cache::lock('approve-InterBranchRequisition-' . $requisition->Id, 5);
        if (! $lock->get()) {
            $message = 'Requisition has been processed, or another user is working on it.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $message,
                ], 409);
            }

            return redirect()->back()->with('error', $message);
        }

        $reason = $request->input('reason');

        try {
            DB::transaction(function () use ($requisition, $user, $reason) {
                $this->workflow->reject($requisition, $user, InterBranchRequisitionEnum::Rejected, $reason);
            });


            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Requisition rejected successfully.',
                ]);
            }

            return redirect()->route('interbranchrequisitionapproval.index')->with('success', 'Requisition rejected successfully.');
        } catch (ErroredException $e) {
            $errorMessage = $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 400);
            }

            return redirect()->back()->with('error', $errorMessage);
        } catch (Exception $e) {

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

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 400);
            }

            return redirect()->back()->with('error', $errorMessage);
        } finally {
            optional($lock)->release();
        }
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

        $currentBranch = $user->branch;
        if ($requisition->ToBranch != $currentBranch->Id) {
            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You can only process requisitions for your branch.',
                ], 403);
            }

            return redirect()->back()->withErrors(['error' => 'You can only process requisitions for your branch.']);
        }

        $lock = Cache::lock('approve-InterBranchRequisition-' . $requisition->Id, 5);
        if (! $lock->get()) {
            $message = 'Requisition has been processed, or another user is working on it.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $message,
                ], 409);
            }

            return redirect()->back()->with('error', $message);
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

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your decision has been recorded.',
                ]);
            }

            return redirect()->route('interbranchrequisitionapproval.index')
                ->with('success', 'Your decision has been recorded.');

        } catch (Exception $e) {

            $errorMessage = 'Unexpected error: ' . $e->getMessage();
            $errorLower = strtolower($e->getMessage());

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

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 400);
            }

            return redirect()->back()->with('error', $errorMessage)->withInput();
        } finally {
            optional($lock)->release();
        }
    }
}
