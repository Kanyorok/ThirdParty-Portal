<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\Inventory\Transfers;
use App\Http\Controllers\Controller;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\TransactionTransfer;
use App\Services\Inventory\StockAdjustmentService;
use App\Services\Inventory\TransactionTransferService;
use App\Services\Workflow\ApprovalWorkflow;
use App\Policies\Inventory\TransactionTransferPolicy;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Core\Branch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ErroredException;

class TransactionApprovalController extends Controller
{
    protected TransactionTransferService $transferService;
    protected StockAdjustmentService $adjustmentService;
    protected ApprovalWorkflow $workflow;

    public function __construct(TransactionTransferService $transferService, StockAdjustmentService $adjustmentService)
    {
        $this->transferService = $transferService;
        $this->adjustmentService = $adjustmentService;
        $this->workflow = new ApprovalWorkflow('TransferStatus','Status');
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
            $query = TransactionTransfer::where('FromBranch', $branchId)
            ->where('Status', 'PE') 
            ->orderBy('CreatedOn', 'desc')
            ->get();
        
        $transfer = null;

        } elseif ($transactionType === 'Stock Adjustment') {
            $query = StockAdjustment::with('branch')
                ->where('Status', Transfers::Pending->value)
                ->where('Branch', $branchId);
        } else {
            $query = collect(); 
        }

        if ($query instanceof \Illuminate\Database\Query\Builder || $query instanceof \Illuminate\Database\Eloquent\Builder) {
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

        $record = null;
        
        if ($request->filled('id')) {
            if ($transactionType === 'Stock Transfer') {
                $record = TransactionTransfer::with(['fromBranch', 'toBranch', 'creator', 'items', 'items.item'])
                    ->find($request->id);
                    
                if ($record && $record->FromBranch != $branchId) {
                    return redirect()->route('transactionapproval.index')
                        ->with('error', 'You can only view transfers from your branch.');
                }
            } elseif ($transactionType === 'Stock Adjustment') {
                $record = StockAdjustment::with(['branch', 'creator', 'items', 'items.item'])
                    ->find($request->id);
                    
                if ($record && $record->Branch != $branchId) {
                    return redirect()->route('transactionapproval.index')
                        ->with('error', 'You can only view adjustments for your branch.');
                }
            }
        }

        return view('inventory.transactions.transactionsapprovals.index', [
            'transactionType' => $transactionType,
            'records' => $records,
            'record' => $record,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $transactionType = $request->input('transaction_type');
        $comments = $request->input('comments', 'Approved via UI');

        if ($transactionType === 'Stock Transfer') {
            $transfer = TransactionTransfer::findOrFail($id);
            $this->authorize('approve', $transfer);

            $currentBranch = Auth::user()->branch;
            if ($transfer->FromBranch != $currentBranch->Id) {
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

            if (!$this->workflow->canApproveModel($transfer, $user)) {
                $message = 'You are not authorized to approve this transfer.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $lock = Cache::lock('approve-TransactionTransfer-' . $transfer->Id, 5);
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
                DB::transaction(function () use ($transfer, $user, $comments) {
                    $this->workflow->approve($transfer, $user, Transfers::Approved, $comments);
                });
                
                Log::info('Stock Transfer approved successfully', [
                    'transfer_id' => $transfer->Id,
                    'user_id' => $user->Id
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Stock Transfer approved successfully.'
                    ]);
                }

                return redirect()->route('transactionapproval.index', ['transaction_type' => 'Stock Transfer'])
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
            $adjustment = StockAdjustment::findOrFail($id);
            $this->authorize('approve', $adjustment);

            $currentBranch = Auth::user()->branch;
            if ($adjustment->Branch != $currentBranch->Id) {
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

            if (!$this->workflow->canApproveModel($adjustment, $user)) {
                $message = 'You are not authorized to approve this adjustment.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $lock = Cache::lock('approve-StockAdjustment-' . $adjustment->Id, 5);
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
                DB::transaction(function () use ($adjustment, $user, $comments) {
                    $this->workflow->approve($adjustment, $user, Transfers::Approved, $comments);
                });
                
                Log::info('Stock Adjustment approved successfully', [
                    'adjustment_id' => $adjustment->Id,
                    'user_id' => $user->Id
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Stock Adjustment approved successfully.'
                    ]);
                }

                return redirect()->route('transactionapproval.index', ['transaction_type' => 'Stock Adjustment'])
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
        $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $transactionType = $request->input('transaction_type');
        $reason = $request->input('reason');

        if ($transactionType === 'Stock Transfer') {
            $transfer = TransactionTransfer::findOrFail($id);
            $this->authorize('reject', $transfer);

            $currentBranch = Auth::user()->branch;
            if ($transfer->FromBranch != $currentBranch->Id) {
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

            if (!$this->workflow->canApproveModel($transfer, $user)) {
                $message = 'You are not authorized to reject this transfer.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $lock = Cache::lock('reject-TransactionTransfer-' . $transfer->Id, 5);
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
                DB::transaction(function () use ($transfer, $user, $reason) {
                    $this->workflow->reject($transfer, $user, Transfers::Rejected, $reason);
                });
                
                Log::info('Stock Transfer rejected successfully', [
                    'transfer_id' => $transfer->Id,
                    'user_id' => $user->Id,
                    'reason' => $reason
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Stock Transfer rejected successfully.'
                    ]);
                }

                return redirect()->route('transactionapproval.index', ['transaction_type' => 'Stock Transfer'])
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
            $adjustment = StockAdjustment::findOrFail($id);
            $this->authorize('reject', $adjustment);

            $currentBranch = Auth::user()->branch;
            if ($adjustment->Branch != $currentBranch->Id) {
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

            if (!$this->workflow->canApproveModel($adjustment, $user)) {
                $message = 'You are not authorized to reject this adjustment.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                return redirect()->back()->withErrors(['error' => $message]);
            }

            $lock = Cache::lock('reject-StockAdjustment-' . $adjustment->Id, 5);
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
                DB::transaction(function () use ($adjustment, $user, $reason) {
                    $this->workflow->reject($adjustment, $user, Transfers::Rejected, $reason);
                });
                
                Log::info('Stock Adjustment rejected successfully', [
                    'adjustment_id' => $adjustment->Id,
                    'user_id' => $user->Id,
                    'reason' => $reason
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Stock Adjustment rejected successfully.'
                    ]);
                }

                return redirect()->route('transactionapproval.index', ['transaction_type' => 'Stock Adjustment'])
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
}