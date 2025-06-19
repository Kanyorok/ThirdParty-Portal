<?php

namespace App\Http\Controllers\Inventory;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InterBranchRequisitionRequest;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\ItemCategories;
use App\Models\Core\Branch;
use App\Services\Inventory\InterBranchRequisitionService;
use Illuminate\Http\Request;
use App\Providers\Inventory\InterBranchRequisitionPolicy;
use App\Enums\Inventory\InterBranchRequisitionEnum;
use Illuminate\Support\Facades\DB; // Import DB facade

class InterBranchRequisitionController extends Controller
{
    protected $service;

    public function __construct(InterBranchRequisitionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $query = InterBranchRequisition::with(['fromBranch', 'toBranch', 'items']);
        if ($request->filled('status')) {
            $enum = InterBranchRequisitionEnum::tryFrom($request->status);
            $status = $enum ? $enum->value : $request->status;
            $query->where('Status', $status);
        }
        if ($request->filled('status')) {
            $groupedRequisitions = $query->latest()->get();
        } else {
            $groupedRequisitions = $query->latest()->get();
        }
        return view('inventory.interbranchrequisition.index', compact('groupedRequisitions'));
    }

    public function create()
    {
        // $this->authorize('create', InterBranchRequisition::class);
        // Categories are no longer passed directly, they are dynamically loaded by JS
        $branches = Branch::all();
        $uoms = UnitOfMeasure::all(); // Not directly used for dropdowns, but good to keep if needed elsewhere

        return view('inventory.interbranchrequisition.create', compact('branches', 'uoms'));
    }

    public function store(InterBranchRequisitionRequest $request)
    {
        // $this->authorize('create', InterBranchRequisition::class);
        $data = $request->validated();

        // Additional Server-Side Stock Validation before creating the requisition
        $fromBranchId = $data['FromBranch'];
        foreach ($data['items'] as $itemData) {
            $itemId = $itemData['Item'];
            $requestedQty = $itemData['RequestedQty'];

            $stock = DB::table('t_Stockitems')
                        ->where('Branch', $fromBranchId)
                        ->where('ItemId', $itemId)
                        ->first();

            if (!$stock || $stock->CurrentQty < $requestedQty) {
                $itemName = $itemData['item_name'] ?? 'Unknown Item';
                return back()->withErrors(['items' => "Item '{$itemName}' (ID: {$itemId}) is not sufficiently in stock at the From Branch."])->withInput();
            }
        }

        if (!isset($data['Status'])) {
            $data['Status'] = InterBranchRequisitionEnum::Submitted->value;
        }
        $this->service->create($data);
        return redirect()->route('interbranchrequisition.index')->with('success', 'Requisition submitted successfully.');
    }

    public function show($Id)
    {
        $item = InterBranchRequisition::with([
            'fromBranch',
            'toBranch',
            'creator',
            'items',
            'items.item.category.parent',
        ])->findOrFail($Id);

        return view('inventory.interbranchrequisition.show', compact('item'));
    }

    public function edit($Id)
    {
        $this->authorize('update', InterBranchRequisition::class);
        $item = InterBranchRequisition::with([
            'fromBranch',
            'toBranch',
            'creator',
            'items',
            'items.item.category.parent',
        ])->findOrFail($Id);

        // Categories are no longer passed directly, they are dynamically loaded by JS
        $categories = ItemCategories::whereNull('ParentId')->get(); // Keep this for now for the loop in the template
        $branches = Branch::all();
        $uoms = UnitOfMeasure::all();

        return view('inventory.interbranchrequisition.edit', compact('item', 'branches', 'uoms', 'categories'));
    }

    public function update(InterBranchRequisitionRequest $request, $Id)
    {
        $this->authorize('update', InterBranchRequisition::class);
        $item = InterBranchRequisition::with('items')->findOrFail($Id);
        $data = $request->validated();

        // Additional Server-Side Stock Validation during update
        $fromBranchId = $data['FromBranch'];
        foreach ($data['items'] as $itemData) {
            $itemId = $itemData['Item'];
            $requestedQty = $itemData['RequestedQty'];

            $stock = DB::table('t_Stockitems')
                        ->where('Branch', $fromBranchId)
                        ->where('ItemId', $itemId)
                        ->first();

            if (!$stock || $stock->CurrentQty < $requestedQty) {
                $itemName = $itemData['item_name'] ?? 'Unknown Item';
                return back()->withErrors(['items' => "Item '{$itemName}' (ID: {$itemId}) is not sufficiently in stock at the From Branch for update."])->withInput();
            }
        }

        if (!isset($data['Status']) && $item->Status) {
            $data['Status'] = $item->Status;
        }

        try {
            $this->service->update($item, $data);
            return redirect()->route('interbranchrequisition.index')->with('success', 'Requisition updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update Requisition: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($Id)
    {
        $item = InterBranchRequisition::findOrFail($Id);
        $this->authorize('destroy', $item);
        $this->service->delete($item);
        return redirect()->route('interbranchrequisition.index')->with('success', 'Requisition deleted successfully.');
    }

    /**
     * Original getSubcategories - still included per routes but new JS will use branch-aware version.
     */
    public function getSubcategories(Request $request)
    {
        $categoryId = $request->get('category_id');
        if (!$categoryId) {
            return response()->json([]);
        }

        $subcategories = ItemCategories::where('ParentId', $categoryId)
            ->select('Id', 'Name')
            ->get();

        return response()->json($subcategories);
    }

    /**
     * Get item code and UOM for a given Item ID.
     */
    public function getItemCode($Id)
    {
        $item = ItemMasterList::with('uom')->select('Id', 'ItemCode', 'UOM')->find($Id);

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json([
            'item_code' => $item->ItemCode,
            'item_uom' => optional($item->uom)->Code ?? 'N/A',
        ]);
    }

    /**
     * Get categories that have items in stock for a given branch.
     */
    public function getCategoriesByBranch(Request $request)
    {
        $fromBranchId = $request->get('from_branch_id');

        if (!is_numeric($fromBranchId)) {
            return response()->json(['message' => 'Invalid branch selected.', 'categories' => []]);
        }

        $categories = DB::table('t_Items')
            ->join('t_Stockitems', 't_Items.Id', '=', 't_Stockitems.ItemId')
            ->join('t_ItemCategories', 't_Items.Category', '=', 't_ItemCategories.Id')
            ->leftJoin('t_ItemCategories as parent_category', 't_ItemCategories.ParentId', '=', 'parent_category.Id')
            ->select(
                't_ItemCategories.Id',
                't_ItemCategories.Name',
                't_ItemCategories.ParentId', // This is the actual ParentId of the current category
                'parent_category.Name as ParentName'
            )
            ->where('t_Stockitems.Branch', $fromBranchId)
            ->where('t_Stockitems.CurrentQty', '>', 0)
            ->where('t_Items.Status', 1)
            ->distinct('t_ItemCategories.Id')
            ->get();

        $result = [];
        $uniqueTopLevelCategories = [];

        foreach ($categories as $category) {
            if ($category->ParentId === null) { // This is a top-level category itself
                $uniqueTopLevelCategories[$category->Id] = ['Id' => $category->Id, 'Name' => $category->Name];
            } else { // This is a subcategory, add its parent if not already added
                if (!isset($uniqueTopLevelCategories[$category->ParentId])) {
                    $uniqueTopLevelCategories[$category->ParentId] = ['Id' => $category->ParentId, 'Name' => $category->ParentName];
                }
            }
        }

        usort($uniqueTopLevelCategories, function($a, $b) {
            return strcmp($a['Name'], $b['Name']);
        });

        if (empty($uniqueTopLevelCategories)) {
            return response()->json(['message' => 'No categories with available items in this branch.', 'categories' => []]);
        }

        return response()->json(['message' => 'Categories retrieved successfully.', 'categories' => array_values($uniqueTopLevelCategories)]);
    }

    /**
     * Get subcategories that have items in stock for a given branch and parent category.
     */
    public function getSubcategoriesByBranchAndCategory(Request $request)
    {
        $fromBranchId = $request->get('from_branch_id');
        $categoryId = $request->get('category_id'); // This is the parent category ID

        if (!is_numeric($fromBranchId) || !is_numeric($categoryId)) {
            return response()->json(['message' => 'Invalid branch or category selected.', 'subcategories' => []]);
        }

        $subcategories = DB::table('t_Items')
            ->join('t_Stockitems', 't_Items.Id', '=', 't_Stockitems.ItemId')
            ->join('t_ItemCategories', 't_Items.Category', '=', 't_ItemCategories.Id')
            ->select('t_ItemCategories.Id', 't_ItemCategories.Name')
            ->where('t_Stockitems.Branch', $fromBranchId)
            ->where('t_ItemCategories.ParentId', $categoryId) // Filter by parent category
            ->where('t_Stockitems.CurrentQty', '>', 0)
            ->where('t_Items.Status', 1)
            ->distinct('t_ItemCategories.Id')
            ->get();

        if ($subcategories->isEmpty()) {
            return response()->json(['message' => 'No subcategories with available items for this category in this branch.', 'subcategories' => []]);
        }

        return response()->json(['message' => 'Subcategories retrieved successfully.', 'subcategories' => $subcategories]);
    }


    /**
     * Get items available in stock for a given branch and category/subcategory.
     */
    public function getItemsByBranchAndCategoryOrSubcategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategoryId = $request->get('subcategory_id');
        $fromBranchId = $request->get('from_branch_id');

        if ((!is_null($subcategoryId) && !is_numeric($subcategoryId)) ||
            (!is_null($categoryId) && !is_numeric($categoryId)) ||
            (!is_null($fromBranchId) && !is_numeric($fromBranchId))) {
            return response()->json(['message' => 'Invalid input provided.', 'items' => []]);
        }

        if (empty($fromBranchId)) {
            return response()->json(['message' => 'Please select a "Requesting Branch" first to view available items.', 'items' => []]);
        }

        $itemsQuery = DB::table('t_Items')
            ->select('t_Items.Id', 't_Items.ItemName')
            ->where('t_Items.Status', 1);

        if ($subcategoryId) {
            $itemsQuery->where('t_Items.Category', $subcategoryId);
        } elseif ($categoryId) {
            $itemsQuery->where('t_Items.Category', $categoryId);
        } else {

            return response()->json(['message' => 'Please select a category or subcategory.', 'items' => []]);
        }

        $items = $itemsQuery->join('t_Stockitems', 't_Items.Id', '=', 't_Stockitems.ItemId')
                            ->where('t_Stockitems.Branch', $fromBranchId)
                            ->where('t_Stockitems.CurrentQty', '>', 0)
                            ->distinct('t_Items.Id')
                            ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'No items available in stock for the selected category/branch.', 'items' => []]);
        }

        return response()->json(['message' => 'Items retrieved successfully.', 'items' => $items]);
    }
}