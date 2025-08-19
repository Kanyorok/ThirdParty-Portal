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

class TransactionApprovalController extends Controller
{
    protected $adjustmentService;
    protected $transferService;

    public function __construct(
        StockAdjustmentService $adjustmentService,
        TransactionTransferService $transferService
    )
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

        if ($transactionType === 'Stock Transfer') {
            $query = TransactionTransfer::with(['fromBranch', 'toBranch', 'creator'])
                ->where('Status', Transfers::Pending->value);

            if ($branch) {
                $query->whereHas('fromBranch', function ($q) use ($branch) {
                    $q->where('Name', 'like', "%$branch%")
                        ->orWhere('Id', $branch);
                });
            }

        } elseif ($transactionType === 'Stock Issue') {
            $query = StockIssue::with(['branch', 'creator'])
                ->where('Status', 'Pending');

            if ($branch) {
                $query->whereHas('branch', function ($q) use ($branch) {
                    $q->where('Name', 'like', "%$branch%")
                        ->orWhere('Id', $branch);
                });
            }

        } elseif ($transactionType === 'Stock Adjustment') {
            $query = StockAdjustment::with('branch')
                ->where('Status', Transfers::Pending);

            if ($branch) {
                $query->whereHas('branch', function ($q) use ($branch) {
                    $q->where('Name', 'like', "%$branch%")
                        ->orWhere('Id', $branch);
                });
            }

        } else {
            $query = collect(); // fallback if type is unknown
        }

        if (is_a($query, Builder::class)) {
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
        $transfer = TransactionTransfer::findOrFail($id);
        $this->authorize('approve', $transfer);

        $transactionType = $request->input('transaction_type');

        try {
            if ($transactionType === 'Stock Transfer') {
                $this->transferService->approve($id);
                return redirect()->back()->with('success', 'Stock Transfer approved.');
            }

            if ($transactionType === 'Stock Adjustment') {
                $this->adjustmentService->approve($id);
                return redirect()->back()->with('success', 'Stock Adjustment approved.');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('error', 'Unknown transaction type.');
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('approve', TransactionTransfer::class);
        $transactionType = $request->input('transaction_type');

        if ($transactionType === 'Stock Adjustment') {
            $this->adjustmentService->reject($id);
            return redirect()->back()->with('success', 'Stock Adjustment rejected.');
        }

        if ($transactionType === 'Stock Transfer') {
            $this->transferService->reject($id);
            return redirect()->back()->with('success', 'Stock Transfer rejected.');
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
        } elseif ($transactionType === 'Stock Adjustment') {
            $record = StockAdjustment::with([
                'branch', 'adjustedBy', 'items.item.uom'
            ])->findOrFail($id);
        } else {
            // Try to find as Transfer
            $record = TransactionTransfer::with([
                'fromBranch', 'toBranch', 'transferredBy', 'items.item.uom'
            ])->find($id);

            if ($record) {
                $transactionType = 'Stock Transfer';
            } else {
                // Try to find as Adjustment
                $record = StockAdjustment::with([
                    'branch', 'adjustedBy', 'items.item.uom'
                ])->find($id);

                if ($record) {
                    $transactionType = 'Stock Adjustment';
                } else {
                    abort(404, 'Transaction type not found');
                }
            }
        }

        return view('inventory.transactions.transactionsapprovals.show', compact('record', 'transactionType'));
    }

}
