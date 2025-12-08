<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\Inventory\Transfers;
use App\Http\Controllers\Controller;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\TransactionTransfer;
use App\Services\Inventory\StockAdjustmentService;
use App\Services\Inventory\TransactionTransferService;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Http\Request;
use App\Models\Core\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ErroredException;
use Exception;

class TransactionApprovalController extends Controller
{
    protected TransactionTransferService $transferService;
    protected StockAdjustmentService $adjustmentService;
    protected ApprovalWorkflow $workflow;

    public function __construct(
        TransactionTransferService $transferService, 
        StockAdjustmentService $adjustmentService,
        ApprovalWorkflow $workflow
    )
    {
        $this->transferService = $transferService;
        $this->adjustmentService = $adjustmentService;
        $this->workflow = new ApprovalWorkflow('TransferStatus', 'Status');
    }
  
    public function index(Request $request)
    {
        $transactionType = $request->get('transaction_type', 'Stock Transfer');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        if ($transactionType === 'Stock Transfer') {
            $query = TransactionTransfer::with(['fromBranch', 'toBranch', 'creator'])
                ->where('FromBranch', $branchId)
                ->where('Status', Transfers::Pending->value);

            if ($fromDate) {
                $query->whereDate('CreatedOn', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('CreatedOn', '<=', $toDate);
            }

            $records = $query->orderByDesc('CreatedOn')->get();

        } elseif ($transactionType === 'Stock Adjustment') {
            $query = StockAdjustment::with(['branch', 'creator'])
                ->where('Status', Transfers::Pending->value)
                ->where('Branch', $branchId);

            if ($fromDate) {
                $query->whereDate('CreatedOn', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('CreatedOn', '<=', $toDate);
            }

            $records = $query->orderByDesc('CreatedOn')->get();
        } else {
            $records = collect();
        }

        return view('inventory.transactions.transactionsapprovals.index', [
            'transactionType' => $transactionType,
            'records' => $records,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }

    public function show($id, Request $request)
    {
        $transactionType = $request->get('transaction_type');

        if ($transactionType === 'Stock Transfer') {
            $record = TransactionTransfer::with([
                'fromBranch', 'toBranch', 'creator', 'items.item'
            ])->findOrFail($id);
            
            $this->authorize('view', $record);

            $currentBranch = Auth::user()->branch;
            $branchId = $currentBranch->Id;

            if ($record->FromBranch != $branchId) {
                abort(403, 'You are not authorized to view this transfer.');
            }

            $user = Auth::user();
            $canApprove = $this->workflow->canApproveModel($record, $user);
            
            Log::info("Can approve transfer {$record->Id} for user {$user->Id}: " . ($canApprove ? 'Yes' : 'No'));
            
        } elseif ($transactionType === 'Stock Adjustment') {
            $record = StockAdjustment::with([
                'branch', 'creator', 'items.item'
            ])->findOrFail($id);
            
            $this->authorize('view', $record);

            $currentBranch = Auth::user()->branch;
            $branchId = $currentBranch->Id;

            if ($record->Branch != $branchId) {
                abort(403, 'You are not authorized to view this adjustment.');
            }

            $user = Auth::user();
            $canApprove = $this->workflow->canApproveModel($record, $user);
            
            Log::info("Can approve adjustment {$record->Id} for user {$user->Id}: " . ($canApprove ? 'Yes' : 'No'));
            
        } else {
            // Try to find as Transfer
            $record = TransactionTransfer::with([
                'fromBranch', 'toBranch', 'creator', 'items.item'
            ])->find($id);

            if ($record) {
                $transactionType = 'Stock Transfer';
                $this->authorize('view', $record);
                $user = Auth::user();
                $canApprove = $this->workflow->canApproveModel($record, $user);
            } else {
                // Try to find as Adjustment
                $record = StockAdjustment::with([
                    'branch', 'creator', 'items.item'
                ])->find($id);

                if ($record) {
                    $transactionType = 'Stock Adjustment';
                    $this->authorize('view', $record);
                    $user = Auth::user();
                    $canApprove = $this->workflow->canApproveModel($record, $user);
                } else {
                    abort(404, 'Transaction type not found');
                }
            }
        }

        return view('inventory.transactions.transactionsapprovals.show', [
            'record' => $record,
            'transactionType' => $transactionType,
            'canApprove' => $canApprove,
            'history' => $this->workflow->historyForModel($record),
        ]);
    }

    public function approve(Request $request, $id)
    {
        $transactionType = $request->input('transaction_type');

        if ($transactionType === 'Stock Transfer') {
            $record = TransactionTransfer::findOrFail($id);
            $this->authorize('approve', $record);

            $currentBranch = Auth::user()->branch;
            if ($record->FromBranch != $currentBranch->Id) {
                $message = 'You can only approve transfers from your branch.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $user = Auth::user();

            if (!$this->workflow->canApproveModel($record, $user)) {
                $message = 'You are not authorized to approve this transfer.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $lock = Cache::lock('approve-TransactionTransfer-' . $record->Id, 5);
            if (!$lock->get()) {
                $message = 'Transfer has been approved, or another user is working on it.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 409);
                }
                return redirect()->back()->with('error', $message);
            }

            try {
                // Use the service's approve method which handles workflow and stock updates
                $this->transferService->approve($record->Id, 'Approved Transfer');
                
                Log::info('Stock Transfer approved successfully', [
                    'transfer_id' => $record->Id,
                    'user_id' => $user->Id
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Stock Transfer approved successfully.'
                    ]);
                }

                return redirect()->route('transactionsapproval.index', ['transaction_type' => 'Stock Transfer'])
                    ->with('success', 'Stock Transfer approved successfully.');
            } catch (ErroredException $e) {
                $errorMessage = $e->getMessage();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage
                    ], 400);
                }
                return redirect()->back()->with('error', $errorMessage);
            } catch (Exception $e) {
                Log::error('Error approving stock transfer: ' . $e->getMessage());
                
                $errorMessage = 'Unexpected error, try again later.';
                $errorLower = strtolower($e->getMessage());
                
                if (str_contains($errorLower, 'cannot approve your own submission') || 
                    str_contains($errorLower, 'maker-checker')) {
                    $errorMessage = 'You cannot approve your own submission. Please have another user approve this transfer.';
                } elseif (str_contains($errorLower, 'already approved') || 
                         str_contains($errorLower, 'already actioned')) {
                    $errorMessage = 'This transfer has already been approved or processed.';
                } elseif (str_contains($errorLower, 'not in approvable status') ||
                         str_contains($errorLower, 'no pending approval found')) {
                    $errorMessage = 'This transfer cannot be approved in its current status or no pending approval found for your user.';
                } elseif (str_contains($errorLower, 'insufficient stock')) {
                    $errorMessage = 'Insufficient stock available for one or more items.';
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage
                    ], 400);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            } finally {
                optional($lock)->release();
            }
        }

        if ($transactionType === 'Stock Adjustment') {
            $record = StockAdjustment::findOrFail($id);
            $this->authorize('approve', $record);

            $currentBranch = Auth::user()->branch;
            if ($record->Branch != $currentBranch->Id) {
                $message = 'You can only approve adjustments for your branch.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $user = Auth::user();

            if (!$this->workflow->canApproveModel($record, $user)) {
                $message = 'You are not authorized to approve this adjustment.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $lock = Cache::lock('approve-StockAdjustment-' . $record->Id, 5);
            if (!$lock->get()) {
                $message = 'Adjustment has been approved, or another user is working on it.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 409);
                }
                return redirect()->back()->with('error', $message);
            }

            try {
                // Use the adjustment service's approve method if it exists
                // If not, use the workflow directly
                if (method_exists($this->adjustmentService, 'approve')) {
                    $this->adjustmentService->approve($record->Id, 'Approved Adjustment');
                } else {
                    // Fallback to workflow if service doesn't have approve method
                    DB::transaction(function () use ($record, $user) {
                        $this->workflow->approve($record, $user, Transfers::Approved, 'Approved Adjustment');
                    });
                }
                
                Log::info('Stock Adjustment approved successfully', [
                    'adjustment_id' => $record->Id,
                    'user_id' => $user->Id
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Stock Adjustment approved successfully.'
                    ]);
                }

                return redirect()->route('transactionsapproval.index', ['transaction_type' => 'Stock Adjustment'])
                    ->with('success', 'Stock Adjustment approved successfully.');
            } catch (ErroredException $e) {
                $errorMessage = $e->getMessage();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage
                    ], 400);
                }
                return redirect()->back()->with('error', $errorMessage);
            } catch (Exception $e) {
                Log::error('Error approving stock adjustment: ' . $e->getMessage());
                
                $errorMessage = 'Unexpected error, try again later.';
                $errorLower = strtolower($e->getMessage());
                
                if (str_contains($errorLower, 'cannot approve your own submission') || 
                    str_contains($errorLower, 'maker-checker')) {
                    $errorMessage = 'You cannot approve your own submission. Please have another user approve this adjustment.';
                } elseif (str_contains($errorLower, 'already approved') || 
                         str_contains($errorLower, 'already actioned')) {
                    $errorMessage = 'This adjustment has already been approved or processed.';
                } elseif (str_contains($errorLower, 'not in approvable status') ||
                         str_contains($errorLower, 'no pending approval found')) {
                    $errorMessage = 'This adjustment cannot be approved in its current status or no pending approval found for your user.';
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage
                    ], 400);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            } finally {
                optional($lock)->release();
            }
        }

        $message = 'Unknown transaction type.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $message
            ], 400);
        }
        return redirect()->back()->with('error', $message);
    }

    public function reject(Request $request, $id)
    {
        $transactionType = $request->input('transaction_type');

        if ($transactionType === 'Stock Transfer') {
            $record = TransactionTransfer::findOrFail($id);
            $this->authorize('reject', $record);

            $currentBranch = Auth::user()->branch;
            if ($record->FromBranch != $currentBranch->Id) {
                $message = 'You can only reject transfers from your branch.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $user = Auth::user();

            if (!$this->workflow->canApproveModel($record, $user)) {
                $message = 'You are not authorized to reject this transfer.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $lock = Cache::lock('reject-TransactionTransfer-' . $record->Id, 5);
            if (!$lock->get()) {
                $message = 'Transfer has been processed, or another user is working on it.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 409);
                }
                return redirect()->back()->with('error', $message);
            }

            try {
                // Use the service's reject method
                $this->transferService->reject($record->Id, 'Rejected via UI');
                
                Log::info('Stock Transfer rejected successfully', [
                    'transfer_id' => $record->Id,
                    'user_id' => $user->Id
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Stock Transfer rejected successfully.'
                    ]);
                }

                return redirect()->route('transactionsapproval.index', ['transaction_type' => 'Stock Transfer'])
                    ->with('success', 'Stock Transfer rejected successfully.');
            } catch (ErroredException $e) {
                $errorMessage = $e->getMessage();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage
                    ], 400);
                }
                return redirect()->back()->with('error', $errorMessage);
            } catch (Exception $e) {
                Log::error('Error rejecting stock transfer: ' . $e->getMessage());
                
                $errorMessage = 'Unexpected error, try again later.';
                $errorLower = strtolower($e->getMessage());
                
                if (str_contains($errorLower, 'cannot approve your own submission') || 
                    str_contains($errorLower, 'maker-checker')) {
                    $errorMessage = 'You cannot reject your own submission. Please have another user review this transfer.';
                } elseif (str_contains($errorLower, 'already rejected') || 
                         str_contains($errorLower, 'already actioned')) {
                    $errorMessage = 'This transfer has already been rejected or processed.';
                } elseif (str_contains($errorLower, 'not in rejectable status') ||
                         str_contains($errorLower, 'no pending approval found')) {
                    $errorMessage = 'This transfer cannot be rejected in its current status or no pending approval found for your user.';
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage
                    ], 400);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            } finally {
                optional($lock)->release();
            }
        }

        if ($transactionType === 'Stock Adjustment') {
            $record = StockAdjustment::findOrFail($id);
            $this->authorize('reject', $record);

            $currentBranch = Auth::user()->branch;
            if ($record->Branch != $currentBranch->Id) {
                $message = 'You can only reject adjustments for your branch.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $user = Auth::user();

            if (!$this->workflow->canApproveModel($record, $user)) {
                $message = 'You are not authorized to reject this adjustment.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $lock = Cache::lock('reject-StockAdjustment-' . $record->Id, 5);
            if (!$lock->get()) {
                $message = 'Adjustment has been processed, or another user is working on it.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 409);
                }
                return redirect()->back()->with('error', $message);
            }

            try {
                // Use the adjustment service's reject method if it exists
                // If not, use the workflow directly
                if (method_exists($this->adjustmentService, 'reject')) {
                    $this->adjustmentService->reject($record->Id, 'Rejected via UI');
                } else {
                    // Fallback to workflow if service doesn't have reject method
                    DB::transaction(function () use ($record, $user) {
                        $this->workflow->reject($record, $user, Transfers::Rejected, 'Rejected via UI');
                    });
                }
                
                Log::info('Stock Adjustment rejected successfully', [
                    'adjustment_id' => $record->Id,
                    'user_id' => $user->Id
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Stock Adjustment rejected successfully.'
                    ]);
                }

                return redirect()->route('transactionsapproval.index', ['transaction_type' => 'Stock Adjustment'])
                    ->with('success', 'Stock Adjustment rejected successfully.');
            } catch (ErroredException $e) {
                $errorMessage = $e->getMessage();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage
                    ], 400);
                }
                return redirect()->back()->with('error', $errorMessage);
            } catch (Exception $e) {
                Log::error('Error rejecting stock adjustment: ' . $e->getMessage());
                
                $errorMessage = 'Unexpected error, try again later.';
                $errorLower = strtolower($e->getMessage());
                
                if (str_contains($errorLower, 'cannot approve your own submission') || 
                    str_contains($errorLower, 'maker-checker')) {
                    $errorMessage = 'You cannot reject your own submission. Please have another user review this adjustment.';
                } elseif (str_contains($errorLower, 'already rejected') || 
                         str_contains($errorLower, 'already actioned')) {
                    $errorMessage = 'This adjustment has already been rejected or processed.';
                } elseif (str_contains($errorLower, 'not in rejectable status') ||
                         str_contains($errorLower, 'no pending approval found')) {
                    $errorMessage = 'This adjustment cannot be rejected in its current status or no pending approval found for your user.';
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage
                    ], 400);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            } finally {
                optional($lock)->release();
            }
        }

        $message = 'Unknown transaction type.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $message
            ], 400);
        }
        return redirect()->back()->with('error', $message);
    }
}