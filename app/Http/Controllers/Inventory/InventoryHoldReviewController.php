<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InventoryHoldReviewRequest;
use App\Models\Core\Branch;
use App\Models\Core\Branch;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\InventoryHoldReview;
use App\Services\Inventory\InventoryHoldReviewService;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class InventoryHoldReviewController extends Controller
{
    protected $service;

    public function __construct(InventoryHoldReviewService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', InventoryHoldReview::class);

        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $branchId = $currentBranch->Id;

        $holds = InventoryHoldReview::with([
            'item.uom',
            'fromBranch',
            'store',
            'creator',
            'conditionDetail',
            'defectDetail',
            'inventoryHold',
        ])
            ->whereNull('DeletedOn')
            ->where('FromBranch', $branchId)
            ->where('FromBranch', $branchId)
            ->get();

        $holds->each(function ($hold) {
            $hold->Condition = $hold->conditionDetail?->Description ?? null;
            $hold->Defect = $hold->defectDetail?->Description ?? null;
        });

        return view('inventory.inventoryholdreview.index', compact('holds'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', InventoryHoldReview::class);

        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        $reviews = InventoryHoldReview::with([
            'item.uom',
            'fromBranch',
            'store',
            'creator',
            'conditionDetail',
            'defectDetail',
            'inventoryHold',
        ])
            ->whereNull('DeletedOn')
            ->where('FromBranch', $branchId)
            ->where('FromBranch', $branchId)
            ->get();

        $reviews->each(function ($review) {
            $review->Condition = $review->conditionDetail?->Description ?? null;
            $review->Defect = $review->defectDetail?->Description ?? null;
        });

        $holds = InventoryHold::with([
            'item.uom',
            'fromBranch',
            'branch',
            'store',
            'sourceDetail',
            'defectDetail',
            'sourceAdjustment', 
            'sourceReceipt'     
        ])
            ->whereNull('DeletedOn')
            ->where('BranchID', $branchId)
            ->whereHas('sourceDetail', function ($q) {
                $q->where('Description', '!=', 'Transaction Transfer');
            })
            ->whereDoesntHave('inventoryHoldReview')
            ->whereDoesntHave('inventoryHoldReview')
            ->get();

        $holds->each(function ($hold) {
            $hold->Condition = $hold->conditionDetail?->Description ?? null;
            $hold->Defect = $hold->defectDetail?->Description ?? null;
        });

        return view('inventory.inventoryholdreview.create', compact('holds', 'reviews'));
        return view('inventory.inventoryholdreview.create', compact('holds', 'reviews'));
    }

    public function store(InventoryHoldReviewRequest $request)
    {
        $this->authorize('create', InventoryHoldReview::class);

        $data = $request->validated();
        $action = $request->input('Action');

        if ($action) {
            return $this->resolve($request, $data['InventoryHoldID']);
        }

        $this->service->review($data);

        return redirect()->back()->with('success', 'Inventory item reviewed successfully.');
    }

    public function show($id, Request $request)
    {
        $this->authorize('view', InventoryHoldReview::class);

        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $branchId = $currentBranch->Id;

        $holds = InventoryHoldReview::with([
            'item.uom',
            'fromBranch',
            'store',
            'creator',
            'conditionDetail',
            'defectDetail',
            'inventoryHold',
        ])
            ->where('Id', $id)
            ->where('FromBranch', $branchId)
            ->where('FromBranch', $branchId)
            ->whereNull('DeletedOn')
            ->firstOrFail();

        $holds->Condition = $holds->conditionDetail?->Description ?? null;
        $holds->Defect = $holds->defectDetail?->Description ?? null;

        return view('inventory.inventoryholdreview.show', compact('holds'));
    }

    public function resolve(Request $request, $id)
    {
        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $action = $request->input('Action');

        $extras = [
            'Condition' => $request->input('Condition'),
            'Notes' => $request->input('Notes'),
        ];

        try {
            $holdReview = InventoryHoldReview::where('Id', $id)
                ->where('FromBranch', $branchId)
                ->first();

            if (!$holdReview) {
                $inventoryHold = InventoryHold::where('Id', $id)
                    ->where('BranchID', $branchId)
                    ->firstOrFail();

                $targetId = $inventoryHold->Id;
            } else {
                $targetId = $holdReview->Id;
            }

            match ($action) {
                'dispose' => $this->service->dispose($targetId, $extras),
                'return' => $this->service->returnToSender($targetId, $extras),
                default => throw new Exception('Unknown action')
            };
            match ($action) {
                'dispose' => $this->service->dispose($targetId, $extras),
                'return' => $this->service->returnToSender($targetId, $extras),
                default => throw new Exception('Unknown action')
            };

            return redirect()->route('inventoryholdreview.index')
                ->with('success', "Item marked as {$action} successfully.");
        } catch (Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

            return redirect()->route('inventoryholdreview.index')
                ->with('success', "Item marked as {$action} successfully.");
        } catch (Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function destroy($id, Request $request)
    {
        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $branchId = $currentBranch->Id;

        $hold = InventoryHoldReview::where('Id', $id)
            ->where('FromBranch', $branchId)
            ->where('FromBranch', $branchId)
            ->firstOrFail();

        $this->service->delete($hold);

        return redirect()->back()->with('success', 'Inventory hold deleted successfully.');
    }

    public function getDetails($id, Request $request)
    {
        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $branchId = $currentBranch->Id;

        $hold = InventoryHold::with(['item', 'branch', 'store', 'defectDetail'])
            ->where('BranchID', $branchId)
            ->findOrFail($id);

        return response()->json([
            'ItemID' => $hold->ItemID,
            'Quantity' => $hold->Quantity,
            'FromBranchID' => $hold->BranchID,
            'ItemName' => $hold->item->ItemName ?? null,
            'FromBranch' => $hold->branch->Name ?? null,
            'Store' => $hold->store->StoreName ?? null,
            'Defect' => $hold->defectDetail?->Description ?? $hold->Reason,
        ]);
    }
}