<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\Inventory\Transfers;
use App\Http\Controllers\Controller;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\TransactionTransfer;
use App\Services\Inventory\StockAdjustmentService;
use App\Services\Inventory\TransactionTransferService;
use App\Policies\Inventory\TransactionTransferPolicy;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionApprovalController extends Controller
{
    protected StockAdjustmentService $adjustmentService;
    protected TransactionTransferService $transferService;

    public function __construct(StockAdjustmentService $adjustmentService, TransactionTransferService $transferService)
    {
        $this->adjustmentService = $adjustmentService;
        $this->transferService = $transferService;
    }
  
    public function index(Request $request)
    {
        $transactionType = $request->get('transaction_type', 'Stock Transfer');
        $branch = $request->get('branch');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $branchId = auth()->user()->employee?->BranchId;

        if ($transactionType === 'Stock Transfer') {
            $query = TransactionTransfer::with(['fromBranch', 'toBranch', 'creator'])
                ->where('Status', Transfers::Pending->value)
                ->where('FromBranch', $branchId);

            if ($branch) {
                $query->whereHas('fromBranch', function ($q) use ($branch) {
                    $q->where('Name', 'like', "%$branch%")
                        ->orWhere('Id', $branch);
                });
            }
        } elseif ($transactionType === 'Stock Adjustment') {
            $query = StockAdjustment::with('branch')
                ->where('Status', Transfers::Pending->value)
                ->where('Branch', $branchId);

            if ($branch) {
                $query->whereHas('branch', function ($q) use ($branch) {
                    $q->where('Name', 'like', "%$branch%")
                        ->orWhere('Id', $branch);
                });
            }
        } else {
            $query = collect(); // fallback if type is unknown
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

        return view('inventory.transactions.transactionsapprovals.index', compact('transactionType', 'records'));
    }

    public function approve(Request $request, $id)
    {
        $transactionType = $request->input('transaction_type');
        $comments = $request->input('comments', null);

        try {
            if ($transactionType === 'Stock Transfer') {
                $transfer = TransactionTransfer::findOrFail($id);
                $this->authorize('approve', $transfer);

                $this->transferService->approve($id, $comments);
                return redirect()->back()->with('success', 'Stock Transfer approved.');
            }

            if ($transactionType === 'Stock Adjustment') {
                $adjustment = StockAdjustment::findOrFail($id);
                $this->authorize('approve', $adjustment);

                $this->adjustmentService->approve($id, $comments);
                return redirect()->back()->with('success', 'Stock Adjustment approved.');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('error', 'Unknown transaction type.');
    }

    public function reject(Request $request, $id)
    {
        $transactionType = $request->input('transaction_type');
        $comments = $request->input('comments', null);

        try {
            if ($transactionType === 'Stock Transfer') {
                $transfer = TransactionTransfer::findOrFail($id);
                $this->authorize('approve', $transfer);

                $this->transferService->reject($id, $comments);
                return redirect()->back()->with('success', 'Stock Transfer rejected.');
            }

            if ($transactionType === 'Stock Adjustment') {
                $adjustment = StockAdjustment::findOrFail($id);
                $this->authorize('approve', $adjustment);

                $this->adjustmentService->reject($id, $comments);
                return redirect()->back()->with('success', 'Stock Adjustment rejected.');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('error', 'Reject not supported for this transaction type.');
    }

    public function show($id, Request $request)
    {
        $transactionType = $request->get('transaction_type');

        if ($transactionType === 'Stock Transfer') {
            $record = TransactionTransfer::with([
                'fromBranch', 'toBranch', 'transferredBy', 'items.item.uom'
            ])->findOrFail($id);
            
            // Check if current user can approve this transfer
            $user = Auth::user();
            $canApprove = $this->transferService->getWorkflow()->canApproveModel($record, $user);
            
        } elseif ($transactionType === 'Stock Adjustment') {
            $record = StockAdjustment::with([
                'branch', 'adjustedBy', 'items.item.uom'
            ])->findOrFail($id);
            
            $canApprove = false; // You'll need to implement this for StockAdjustment
        } else {
            // Try to find as Transfer
            $record = TransactionTransfer::with([
                'fromBranch', 'toBranch', 'transferredBy', 'items.item.uom'
            ])->find($id);

            if ($record) {
                $transactionType = 'Stock Transfer';
                $user = Auth::user();
                $canApprove = $this->transferService->getWorkflow()->canApproveModel($record, $user);
            } else {
                // Try to find as Adjustment
                $record = StockAdjustment::with([
                    'branch', 'adjustedBy', 'items.item.uom'
                ])->find($id);

                if ($record) {
                    $transactionType = 'Stock Adjustment';
                    $canApprove = false;
                } else {
                    abort(404, 'Transaction type not found');
                }
            }
        }

        return view('inventory.transactions.transactionsapprovals.show', compact('record', 'transactionType', 'canApprove'));
    }
}