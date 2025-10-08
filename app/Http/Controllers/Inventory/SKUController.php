<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockItemRequest;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\Store;
use App\Models\Core\Branch;
use Illuminate\Support\Facades\Auth;
use App\Services\Inventory\StockItemService;
use Illuminate\Http\Request;

class SKUController extends Controller
{
    protected StockItemService $stockItemService;

    public function __construct(StockItemService $stockItemService)
    {
        $this->stockItemService = $stockItemService;
    }

    public function index()
    {
     $branchId = auth()->user()->employee?->BranchId;
        $this->authorize('viewAny', StockItem::class);

        $items = StockItem::with(['item', 'store', 'uom'])
        ->where('Branch', $branchId)
        ->get();
        return view('inventory.itemmaster.sku.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', StockItem::class);

        $branches = auth()->user()->employee?->BranchId;

        $categories = ItemCategories::whereNull('ParentId')
            ->whereHas('status', fn($q) => $q->where('Description', 'Active'))
            ->get();
        $stores = Store::where('BranchID', $branches)->get();

        return view('inventory.itemmaster.sku.create', compact('branches', 'stores', 'categories'));
    }

    public function store(StockItemRequest $request)
    {
        $this->authorize('create', StockItem::class);

        $data = $request->validated();
        $data['Status'] = 1; 

        try {
            $skuCode = $this->stockItemService->create($data);
            return redirect()->route('sku.index')->with('success', "Stock item added successfully with SKU: $skuCode");
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create stock item: ' . $e->getMessage())->withInput();
        }
    }


    public function show($id)
    {
        $item = StockItem::with(['item', 'store', 'uom'])->findOrFail($id);
        $categories = ItemCategories::whereNull('ParentId')
            ->whereHas('status', fn($q) => $q->where('Description', 'Active'))
            ->get();
        $this->authorize('view', $item);

        return view('inventory.itemmaster.sku.show', compact('item', 'categories'));
    }

    public function edit($id)
    {
        $item = StockItem::with('item.category.parent')->findOrFail($id);
        $this->authorize('update', $item);

        $branches = auth()->user()->employee?->BranchId;
        $categories = ItemCategories::whereNull('ParentId')
            ->whereHas('status', fn($q) => $q->where('Description', 'Active'))
            ->get();
        $stores = Store::where('BranchID', $branches)->get();

        $category = $item->item->category;
        $parentCategoryId = $category->parent ? $category->parent->Id : $category->Id;
        $subcategoryId = $category->parent ? $category->Id : null;

        $items = ItemMasterList::where('Category', $subcategoryId ?? $parentCategoryId)
            ->whereHas('status', fn($q) => $q->where('Description', 'Active'))
            ->get();

        return view('inventory.itemmaster.sku.edit', compact('item', 'branches', 'stores', 'categories', 'items'));
    }

    public function update(StockItemRequest $request, $id)
    {
        $item = StockItem::findOrFail($id);
        $this->authorize('update', $item);

        $data = $request->validated();

        try {
            $this->stockItemService->update($item, $data);
            return redirect()->route('sku.index')->with('success', 'Stock item updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update stock item: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $item = StockItem::findOrFail($id);
        $this->authorize('destroy', $item);

        try {
            $this->stockItemService->delete($item);
            return redirect()->route('sku.index')->with('success', '🗑️ Stock item deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete stock item: ' . $e->getMessage());
        }
    }

    public function getStores(Request $request)
    {
        $branchId = $request->get('BranchID');
        if (!$branchId) {
            return response()->json([], 400);
        }

        $stores = Store::where('BranchID', $branchId)->get(['Id', 'StoreName']);
        return response()->json($stores);
    }

    public function getItemsByCategoryOrSubcategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategoryId = $request->get('subcategory_id');

        $items = ItemMasterList::where('Category', $subcategoryId ?? $categoryId)
            ->whereHas('status', fn($q) => $q->where('Description', 'Active'))
            ->get(['Id', 'ItemName']);
        return response()->json($items);
    }

    

    public function getItemDetails(Request $request)
    {
        $itemId = $request->get('item_id');

        if (!$itemId) {
            return response()->json(['error' => 'Item ID is required'], 400);
        }

        $item = ItemMasterList::with('uom')->find($itemId);

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json([
            'UnitCost' => $item->price?->ActualPrice ?? 0,
            'PriceID' => $item->price?->Id ?? null,
            'UOM' => [
                'id' => $item->UOM,
                'name' => $item->uom?->Name ?? 'N/A'
            ]
        ]);
    }
}
