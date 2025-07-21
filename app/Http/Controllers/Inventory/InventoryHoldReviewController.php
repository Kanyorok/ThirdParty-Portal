<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InventoryHoldReviewRequest;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\InventoryHoldReview;
use App\Services\Inventory\InventoryHoldReviewService;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class InventoryHoldReviewController extends Controller
{
    protected InventoryHoldReviewService $service;

    public function __construct(InventoryHoldReviewService $service)
    {
        $this->service = $service;
    }

    public function index()
    {

        $holds = InventoryHoldReview::with([
            'item.uom',
            'fromBranch',
            'store',
            'creator',
            'conditionDetail',
            'defectDetail',
            'inventoryHold'
        ])->whereNull('DeletedOn')->get();
        $holds->each(function ($hold) {
            $hold->Condition = $hold->conditionDetail?->Description ?? null;
            $hold->Defect = $hold->defectDetail?->Description ?? null;

        });
        return view('inventory.inventoryholdreview.index', compact('holds'));
    }

    public function create()
    {
        $this->authorize('create', InventoryHoldReview::class);
        $holds = InventoryHold::whereNull('DeletedOn')->get();
        $holds->each(function ($hold) {
            $hold->Condition = $hold->conditionDetail?->Description ?? null;
            $hold->Defect = $hold->defectDetail?->Description ?? null;
        });
        return view('inventory.inventoryholdreview.create', compact('holds'));
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

    public function show($id)
    {
        $this->authorize('view', InventoryHoldReview::class);
        $holds = InventoryHoldReview::with([
            'item.uom',
            'fromBranch',
            'store',
            'creator',
            'conditionDetail',
            'defectDetail',
            'inventoryHold'
        ])->where('Id', $id)->whereNull('DeletedOn')->firstOrFail();

        // Set readable attributes
        $holds->Condition = $holds->conditionDetail?->Description ?? null;
        $holds->Defect = $holds->defectDetail?->Description ?? null;

        return view('inventory.inventoryholdreview.show', compact('holds'));
    }

    public function resolve(Request $request, $id)
    {
        $this->authorize('update', InventoryHoldReview::class);
        $action = $request->input('Action');

        $extras = [
            'Condition' => $request->input('Condition'),
            'Notes' => $request->input('Notes'),
        ];

        try {
            match ($action) {
                'dispose' => $this->service->dispose($id, $extras),
                'return' => $this->service->returnToSender($id),
                default => throw new Exception('Unknown action')
            };

            return redirect()->route('inventoryholdreview.index')
                ->with('success', "Item marked as {$action} successfully.");
        } catch (Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }


    public function destroy($id)
    {
        $this->authorize('destroy', InventoryHoldReview::class);
        $hold = InventoryHoldReview::findOrFail($id);
        $this->service->delete($hold);

        return redirect()->back()->with('success', 'Inventory hold deleted successfully.');
    }

    public function getDetails($id)
    {
        $hold = InventoryHold::with(['item', 'branch', 'store'])->findOrFail($id);

        return response()->json([
            'ItemID' => $hold->ItemID,
            'Quantity' => $hold->Quantity,
            'FromBranchID' => $hold->BranchID,
            'ItemName' => $hold->item->ItemName ?? null,
            'FromBranch' => $hold->branch->Name ?? null,
            'Store' => $hold->store->StoreName ?? null,
            'Defect' => $hold->Reason,

        ]);
    }
}
