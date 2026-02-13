<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\Inventory\Transfers;
use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\TransactionTransfer;
use App\Services\Inventory\StockAdjustmentService;
use App\Services\Inventory\TransactionTransferService;
use App\Services\Workflow\ApprovalWorkflow;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Throwable;

class TransactionApprovalController extends Controller
{
    protected TransactionTransferService $transferService;
    protected StockAdjustmentService $adjustmentService;
    protected ApprovalWorkflow $workflow;

    public function __construct(
        TransactionTransferService $transferService,
        StockAdjustmentService $adjustmentService,
        ApprovalWorkflow $workflow
    ) {
        $this->transferService = $transferService;
        $this->adjustmentService = $adjustmentService;
        $this->workflow = new ApprovalWorkflow('TransferStatus', 'Status');
    }

    public function index(Request $request)
    {

        $transactionType = $request->query('transaction_type', 'Stock Transfer');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
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
            $query = StockAdjustment::with(['branch', 'creator','adjustedBy'])
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


        $user = Auth::user();
        $records = $records->map(function ($record) use ($user) {
            $record->canApprove = $this->workflow->canApproveModel($record, $user);

            return $record;
        });

        return view('inventory.transactions.transactionsapprovals.index', [
            'transactionType' => $transactionType,
            'records' => $records,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }

    public function show($id, Request $request)
    {

        $transactionType = $request->query('transaction_type');

        if ($transactionType === 'Stock Transfer') {
            $record = TransactionTransfer::with([
                'fromBranch', 'toBranch', 'creator', 'items.item',
            ])->findOrFail($id);

            $this->authorize('view', $record);

            $currentBranch = Auth::user()->branch;
            $branchId = $currentBranch->Id;

            if ($record->FromBranch != $branchId) {
                abort(403, 'You are not authorized to view this transfer.');
            }

            $user = Auth::user();
            $canApprove = $this->workflow->canApproveModel($record, $user);
        } elseif ($transactionType === 'Stock Adjustment') {
            $record = StockAdjustment::with([
                'branch', 'creator', 'items.item',
            ])->findOrFail($id);

            $this->authorize('view', $record);

            $currentBranch = Auth::user()->branch;
            $branchId = $currentBranch->Id;

            if ($record->Branch != $branchId) {
                abort(403, 'You are not authorized to view this adjustment.');
            }

            $user = Auth::user();
            $canApprove = $this->workflow->canApproveModel($record, $user);
        } else {
            $record = TransactionTransfer::with([
                'fromBranch', 'toBranch', 'creator', 'items.item',
            ])->find($id);

            if ($record) {
                $transactionType = 'Stock Transfer';
                $this->authorize('view', $record);
                $user = Auth::user();
                $canApprove = $this->workflow->canApproveModel($record, $user);
            } else {
                $record = StockAdjustment::with([
                    'branch', 'creator', 'items.item',
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
            try {
                $record = TransactionTransfer::findOrFail($id);
                $this->authorize('approve', $record);

                $currentBranch = Auth::user()->branch;
                if ($record->FromBranch != $currentBranch->Id) {
                    $message = 'You can only approve transfers from your branch.';

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 403);
                    }

                    return redirect()->back()->withErrors(['error' => $message]);
                }

                $user = Auth::user();

                if (! $this->workflow->canApproveModel($record, $user)) {
                    $message = 'You are not authorized to approve this transfer.';

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 403);
                    }

                    return redirect()->back()->withErrors(['error' => $message]);
                }

                $lock = Cache::lock('approve-TransactionTransfer-' . $record->Id, 10);
                if (! $lock->get()) {
                    $message = 'Transfer has been approved, or another user is working on it.';
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 409);
                    }

                    return redirect()->back()->with('error', $message);
                }

                try {
                    $this->transferService->approve($record->Id, 'Transfer approved');


                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Stock Transfer approved successfully.',
                        ]);
                    }

                    return redirect()->route('transactionsapproval.index', ['transaction_type' => 'Stock Transfer'])
                        ->with('success', 'Stock Transfer approved successfully.');
                } finally {
                    optional($lock)->release();
                }
            } catch (Throwable $e) {
                $errorMessage = $this->getErrorMessage($e);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage,
                        'debug' => $e->getMessage(),
                    ], 400);
                }

                return redirect()->back()->with('error', $errorMessage);
            }
        }

        if ($transactionType === 'Stock Adjustment') {
            try {
                $record = StockAdjustment::findOrFail($id);
                $this->authorize('approve', $record);

                $currentBranch = Auth::user()->branch;
                if ($record->Branch != $currentBranch->Id) {
                    $message = 'You can only approve adjustments for your branch.';

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 403);
                    }

                    return redirect()->back()->withErrors(['error' => $message]);
                }

                $user = Auth::user();

                if (! $this->workflow->canApproveModel($record, $user)) {
                    $message = 'You are not authorized to approve this adjustment.';

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 403);
                    }

                    return redirect()->back()->withErrors(['error' => $message]);
                }

                $lock = Cache::lock('approve-StockAdjustment-' . $record->Id, 10);
                if (! $lock->get()) {
                    $message = 'Adjustment has been approved, or another user is working on it.';
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 409);
                    }

                    return redirect()->back()->with('error', $message);
                }

                try {
                    $this->adjustmentService->approve($record->Id, 'Adjustment approved');

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Stock Adjustment approved successfully.',
                        ]);
                    }

                    return redirect()->route('transactionsapproval.index', ['transaction_type' => 'Stock Adjustment'])
                        ->with('success', 'Stock Adjustment approved successfully.');
                } finally {
                    optional($lock)->release();
                }
            } catch (Throwable $e) {
                $errorMessage = $this->getErrorMessage($e);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage,
                    ], 400);
                }

                return redirect()->back()->with('error', $errorMessage);
            }
        }

        $message = 'Unknown transaction type.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $message,
            ], 400);
        }

        return redirect()->back()->with('error', $message);
    }

    public function reject(Request $request, $id)
    {

        $transactionType = $request->input('transaction_type');

        if ($transactionType === 'Stock Transfer') {
            try {
                $record = TransactionTransfer::findOrFail($id);
                $this->authorize('reject', $record);

                $currentBranch = Auth::user()->branch;
                if ($record->FromBranch != $currentBranch->Id) {
                    $message = 'You can only reject transfers from your branch.';

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 403);
                    }

                    return redirect()->back()->withErrors(['error' => $message]);
                }

                $user = Auth::user();

                if (! $this->workflow->canApproveModel($record, $user)) {
                    $message = 'You are not authorized to reject this transfer.';

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 403);
                    }

                    return redirect()->back()->withErrors(['error' => $message]);
                }

                $lock = Cache::lock('reject-TransactionTransfer-' . $record->Id, 10);
                if (! $lock->get()) {
                    $message = 'Transfer has been processed, or another user is working on it.';
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 409);
                    }

                    return redirect()->back()->with('error', $message);
                }

                try {
                    $this->transferService->reject($record->Id, 'Transfer Rejected');

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Stock Transfer rejected successfully.',
                        ]);
                    }

                    return redirect()->route('transactionsapproval.index', ['transaction_type' => 'Stock Transfer'])
                        ->with('success', 'Stock Transfer rejected successfully.');
                } finally {
                    optional($lock)->release();
                }
            } catch (Throwable $e) {
                $errorMessage = $this->getErrorMessage($e);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage,
                    ], 400);
                }

                return redirect()->back()->with('error', $errorMessage);
            }
        }

        if ($transactionType === 'Stock Adjustment') {
            try {
                $record = StockAdjustment::findOrFail($id);
                $this->authorize('reject', $record);

                $currentBranch = Auth::user()->branch;
                if ($record->Branch != $currentBranch->Id) {
                    $message = 'You can only reject adjustments for your branch.';

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 403);
                    }

                    return redirect()->back()->withErrors(['error' => $message]);
                }

                $user = Auth::user();

                if (! $this->workflow->canApproveModel($record, $user)) {
                    $message = 'You are not authorized to reject this adjustment.';

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 403);
                    }

                    return redirect()->back()->withErrors(['error' => $message]);
                }

                $lock = Cache::lock('reject-StockAdjustment-' . $record->Id, 10);
                if (! $lock->get()) {
                    $message = 'Adjustment has been processed, or another user is working on it.';
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 409);
                    }

                    return redirect()->back()->with('error', $message);
                }

                try {
                    $this->adjustmentService->reject($record->Id, 'Transfer Rejected');

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Stock Adjustment rejected successfully.',
                        ]);
                    }

                    return redirect()->route('transactionsapproval.index', ['transaction_type' => 'Stock Adjustment'])
                        ->with('success', 'Stock Adjustment rejected successfully.');
                } finally {
                    optional($lock)->release();
                }
            } catch (Exception $e) {
                $errorMessage = $this->getErrorMessage($e);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage,
                        'debug' => $e->getMessage(),
                    ], 400);
                }

                return redirect()->back()->with('error', $errorMessage);
            }
        }

        $message = 'Unknown transaction type.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $message,
            ], 400);
        }

        return redirect()->back()->with('error', $message);
    }

    private function getErrorMessage(Exception $e): string
    {
        $errorLower = strtolower($e->getMessage());

        if (
            str_contains($errorLower, 'cannot approve your own submission') ||
            str_contains($errorLower, 'maker-checker')
        ) {
            return 'You cannot approve your own submission. Please have another user approve this transaction.';
        } elseif (
            str_contains($errorLower, 'already approved') ||
                 str_contains($errorLower, 'already rejected') ||
                 str_contains($errorLower, 'already actioned')
        ) {
            return 'This transaction has already been processed.';
        } elseif (
            str_contains($errorLower, 'not in approvable status') ||
                 str_contains($errorLower, 'not in rejectable status') ||
                 str_contains($errorLower, 'no pending approval found')
        ) {
            return 'This transaction cannot be processed in its current status or no pending approval found for your user.';
        } elseif (str_contains($errorLower, 'insufficient stock')) {
            return 'Insufficient stock available for one or more items.';
        }

        return 'An unexpected error occurred. Please try again or contact support.';
    }
}
