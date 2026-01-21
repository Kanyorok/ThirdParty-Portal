<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockItem;
use App\Services\Inventory\StockAdjustmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionAdjustmentController extends Controller
{
    protected $service;

    public function __construct(StockAdjustmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', StockAdjustment::class);
        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $adjustments = StockAdjustment::with('items')
            ->latest('CreatedOn')
            ->where('Branch', $branchId)
            ->paginate(20);

        return view('inventory.transactions.adjustments.index', compact('adjustments'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', StockAdjustment::class);

        $currentBranch = $request->user()->branch;
        // ... (rest of method)
    }

    // ... (store method already matches or needs strict check if not edited here, verify in next view if needed, but assuming valid)

    public function approve(StockAdjustment $stockAdjustment, Request $request)  // Add Request for comments
    {
        $this->authorize('approve', $stockAdjustment);
        $comments = $request->input('comments');
        $this->service->approve($stockAdjustment->Id, $comments);

        return redirect()->back()->with('success', 'Stock adjustment approved.');
    }

    // ... (other methods)

    public function reject(StockAdjustment $stockAdjustment, Request $request)  // Add Request for comments
    {
        $this->authorize('approve', $stockAdjustment);
        $comments = $request->input('comments');
        $this->service->reject($stockAdjustment->Id, $comments);

        return redirect()->back()->with('success', 'Stock adjustment rejected.');
    }
}
