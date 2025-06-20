<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\TransactionTransfer;
use Illuminate\Http\Request;
use App\Enums\Inventory\Transfers;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\StockAdjustment;
use App\Services\Inventory\StockAdjustmentService;
use App\Services\Inventory\TransactionTransferService;

class TransactionApprovalController extends Controller
{
    protected $adjustmentService;
    protected $transferService;

    public function __construct(
        StockAdjustmentService     $adjustmentService,
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
            $query = \App\Models\Inventory\StockIssue::with(['branch', 'creator'])
                ->where('Status', 'Pending');

            if ($branch) {
                $query->whereHas('branch', function ($q) use ($branch) {
                    $q->where('Name', 'like', "%$branch%")
                        ->orWhere('Id', $branch);
                });
            }
        } elseif ($transactionType === 'Stock Adjustment') {
            $query = \App\Models\Inventory\StockAdjustment::with('branch')
                ->where('Status', Transfers::Pending);

            if ($branch) {
                $query->whereHas('branch', function ($q) use ($branch) {
                    $q->where('Name', 'like', "%$branch%")
                        ->orWhere('Id', $branch);
                });
            }

        } else {
            $query = collect();
        }

        if (is_a($query, \Illuminate\Database\Eloquent\Builder::class)) {
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
        if ($transactionType === 'Stock Transfer') {
            //$this->authorize('approve', TransactionTransfer::class);
            $this->transferService->approve($id);

            return redirect()->back()->with('success', 'Stock Transfer approved.');
        }

        if ($transactionType === 'Stock Issue') {
            $issue = \App\Models\Inventory\StockIssue::findOrFail($id);
            $issue->Status = 'Approved';
            $issue->save();
            return redirect()->back()->with('success', 'Stock Issue approved.');
        }
        if ($transactionType === 'Stock Adjustment') {
            $this->adjustmentService->approve($id);
            return redirect()->back()->with('success', 'Stock Adjustment approved.');
        }
        if ($transactionType === 'Stock Adjustment') {
            $this->adjustmentService->reject($id);
            return redirect()->back()->with('success', 'Stock Adjustment rejected.');
        }


        return redirect()->back()->with('error', 'Unknown transaction type.');
    }

    public function reject(Request $request, $id)
    {
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

}
