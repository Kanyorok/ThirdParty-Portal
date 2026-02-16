<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\Inventory\InterBranchRequisitionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InterBranchRequisitionRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Branch;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure;
use App\Services\Inventory\InterBranchRequisitionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InterBranchRequisitionController extends Controller
{
    protected $service;

    public function __construct(InterBranchRequisitionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', InterBranchRequisition::class);

        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $isHeadOffice = $currentBranch->IsHQ;
        $branchId = $currentBranch->Id;

        $baseQuery = InterBranchRequisition::with(['fromBranch', 'toBranch', 'items', 'creator']);

        if ($isHeadOffice) {
            $allQuery = clone $baseQuery;
            $incomingQuery = clone $baseQuery;
            $otherQuery = clone $baseQuery;

            if ($request->filled('status')) {
                $enum = InterBranchRequisitionEnum::tryFrom($request->status);
                $status = $enum ? $enum->value : $request->status;
                $allQuery->where('Status', $status);
                $incomingQuery->where('Status', $status);
                $otherQuery->where('Status', $status);
            }

            $allRequisitions = $allQuery->orderBy('CreatedOn', 'desc')->get();

            $incomingRequisitions = $incomingQuery
                ->where('FromBranch', $branchId)
                ->orderBy('CreatedOn', 'desc')
                ->get();

            $otherRequisitions = $otherQuery
                ->where('FromBranch', '!=', $branchId)
                ->where('ToBranch', '!=', $branchId)
                ->orderBy('CreatedOn', 'desc')
                ->get();

            $groupedRequisitions = $allRequisitions;
            $outgoingRequisitions = collect();
        } else {
            $incomingQuery = clone $baseQuery;
            $outgoingQuery = clone $baseQuery;
            $allQuery = clone $baseQuery;

            if ($request->filled('status')) {
                $enum = InterBranchRequisitionEnum::tryFrom($request->status);
                $status = $enum ? $enum->value : $request->status;
                $incomingQuery->where('Status', $status);
                $outgoingQuery->where('Status', $status);
                $allQuery->where('Status', $status);
            }

            $incomingRequisitions = $incomingQuery
                ->where('FromBranch', $branchId)
                ->orderBy('CreatedOn', 'desc')
                ->get();

            $outgoingRequisitions = $outgoingQuery
                ->where('ToBranch', $branchId)
                ->orderBy('CreatedOn', 'desc')
                ->get();

            $allRequisitions = $allQuery
                ->where(function ($q) use ($branchId) {
                    $q->where('FromBranch', $branchId)
                      ->orWhere('ToBranch', $branchId);
                })
                ->orderBy('CreatedOn', 'desc')
                ->get();

            $otherRequisitions = collect();
            $groupedRequisitions = $allRequisitions;
        }

        return view('inventory.interbranchrequisition.index', compact(
            'groupedRequisitions',
            'allRequisitions',
            'incomingRequisitions',
            'outgoingRequisitions',
            'otherRequisitions',
            'isHeadOffice',
            'currentBranch'
        ));
    }

    public function create(Request $request)
    {
        $this->authorize('create', InterBranchRequisition::class);

        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $isHeadOffice = $currentBranch->IsHQ;

        if ($isHeadOffice) {
            $fromBranch = $currentBranch;
            $branches = Branch::where('Id', '!=', $currentBranch->Id)->get();
        } else {
            $fromBranch = null;
            $branches = Branch::where('Id', '!=', $currentBranch->Id)->get();
        }

        $uoms = UnitOfMeasure::all();

        return view('inventory.interbranchrequisition.create', compact(
            'fromBranch',
            'branches',
            'uoms',
            'isHeadOffice',
            'currentBranch'
        ));
    }

    public function store(InterBranchRequisitionRequest $request)
    {
        $this->authorize('create', InterBranchRequisition::class);
        $data = $request->validated();

        $fromBranchId = $data['FromBranch'];
        $fromBranch = Branch::find($fromBranchId);

        foreach ($data['items'] as $itemData) {
            $itemId = $itemData['Item'];
            $requestedQty = $itemData['RequestedQty'];

            // FIXED: Use SUM instead of first() to aggregate stock from all stores in the branch
            $totalStock = DB::table('t_Stockitems')
                ->where('Branch', $fromBranchId)
                ->where('ItemId', $itemId)
                ->sum('CurrentQty');

            if ($totalStock < $requestedQty) {
                $itemName = $itemData['Item'] ?? 'Unknown Item';
                $branchName = $fromBranch ? $fromBranch->Name : 'Unknown Branch';

                return back()->withErrors([
                    'items' => "The requested quantity for the selected item exceeds available stock at the {$branchName} branch. Requested {$requestedQty}, available {$totalStock}.",
                ])->withInput();
            }
        }

        if (! isset($data['Status'])) {
            $data['Status'] = InterBranchRequisitionEnum::Pending->value;
        }

        try {
            $this->service->create($data);

            return redirect()->route('interbranchrequisition.index')
                ->with('success', 'Requisition submitted successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create requisition: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show($Id, Request $request)
    {
        $this->authorize('view', InterBranchRequisition::class);
        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $isHeadOffice = $currentBranch->IsHQ;
        $item = InterBranchRequisition::with([
            'fromBranch',
            'toBranch',
            'creator',
            'items',
            'items.item.category.parent',
        ])->findOrFail($Id);

        return view('inventory.interbranchrequisition.show', compact('item', 'currentBranch', 'isHeadOffice'));
    }

    public function edit($Id, Request $request)
    {
        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $isHeadOffice = $currentBranch->IsHQ;
        $item = InterBranchRequisition::with([
            'fromBranch',
            'toBranch',
            'creator',
            'items',
            'items.item.category.parent',
        ])->findOrFail($Id);

        if ($isHeadOffice) {
            return redirect()->route('interbranchrequisition.index')
                ->with('error', 'HQ cannot edit any requisitions.');
        }

        if ($item->ToBranch != $currentBranch->Id) {
            return redirect()->route('interbranchrequisition.index')
                ->with('error', 'You can only edit requisitions created by your branch.');
        }

        if ($item->Status !== InterBranchRequisitionEnum::Pending->value) {
            return redirect()->route('interbranchrequisition.index')
                ->with('error', 'Only pending requisitions can be edited.');
        }

        $this->authorize('update', InterBranchRequisition::class);

        $categories = ItemCategories::whereNull('ParentId')->get();
        $subcategories = ItemCategories::whereNotNull('ParentId')->get();

        $items = ItemMasterList::with('uom', 'category')
            ->whereIn('Status', function ($q) {
                $q->select('ID')->from('t_CodeDetails')
                  ->where('CodeID', 'ItemStatus')
                  ->where('Description', 'Active');
            })
            ->get();

        $fromBranch = Branch::findOrFail($item->FromBranch);
        $branches = Branch::where('Id', '!=', $currentBranch->Id)->get();
        $uoms = UnitOfMeasure::all();

        return view('inventory.interbranchrequisition.edit', compact(
            'item',
            'branches',
            'uoms',
            'categories',
            'subcategories',
            'items',
            'fromBranch',
            'isHeadOffice',
            'currentBranch'
        ));
    }

    public function update(InterBranchRequisitionRequest $request, $Id)
    {
        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $item = InterBranchRequisition::with('items')->findOrFail($Id);

        if ($currentBranch->IsHQ) {
            return redirect()->route('interbranchrequisition.index')
                ->with('error', 'HQ cannot update any requisitions.');
        }

        if ($item->ToBranch != $currentBranch->Id) {
            return redirect()->route('interbranchrequisition.index')
                ->with('error', 'You can only update requisitions created by your branch.');
        }

        if ($item->Status !== InterBranchRequisitionEnum::Pending->value) {
            return redirect()->route('interbranchrequisition.index')
                ->with('error', 'Only pending requisitions can be updated.');
        }

        $this->authorize('update', InterBranchRequisition::class);

        $data = $request->validated();

        $fromBranchId = $data['FromBranch'];
        foreach ($data['items'] as $itemData) {
            $itemId = $itemData['Item'];
            $requestedQty = $itemData['RequestedQty'];

            $totalStock = DB::table('t_Stockitems')
                ->where('Branch', $fromBranchId)
                ->where('ItemId', $itemId)
                ->sum('CurrentQty');


            if ($totalStock < $requestedQty) {
                $itemName = $itemData['item_name'] ?? 'Unknown Item';

                return back()->withErrors(['items' => "Item '{$itemName}' (ID: {$itemId}) is not sufficiently in stock at the From Branch. Requested {$requestedQty}, available {$totalStock}."])->withInput();
            }
        }

        if (! isset($data['Status']) && $item->Status) {
            $data['Status'] = $item->Status;
        }

        try {
            $this->service->update($item, $data);

            return redirect()->route('interbranchrequisition.index')->with('success', 'Requisition updated successfully!');
        } catch (Exception $e) {
            return back()->withErrors('Failed to update Requisition: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($Id, Request $request)
    {
        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $item = InterBranchRequisition::findOrFail($Id);

        if ($currentBranch->IsHQ) {
            return redirect()->route('interbranchrequisition.index')
                ->with('error', 'HQ cannot delete any requisitions.');
        }

        if ($item->ToBranch != $currentBranch->Id) {
            return redirect()->route('interbranchrequisition.index')
                ->with('error', 'You can only delete requisitions created by your branch.');
        }

        if ($item->Status !== InterBranchRequisitionEnum::Pending->value) {
            return redirect()->route('interbranchrequisition.index')
                ->with('error', 'Only pending requisitions can be deleted.');
        }

        $this->authorize('destroy', InterBranchRequisition::class);
        $this->service->delete($item);

        return redirect()->route('interbranchrequisition.index')->with('success', 'Requisition deleted successfully.');
    }

    public function getSubcategories(Request $request)
    {
        $categoryId = $request->get('category_id');
        if (! $categoryId) {
            return response()->json([]);
        }

        $subcategories = ItemCategories::where('ParentId', $categoryId)
            ->select('Id', 'Name')
            ->get();

        return response()->json($subcategories);
    }

    public function getItemCode($Id)
    {
        $item = ItemMasterList::with('uom')->select('Id', 'ItemCode', 'UOM')->find($Id);

        if (! $item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json([
            'item_code' => $item->ItemCode,
            'item_uom' => optional($item->uom)->Code ?? 'N/A',
        ]);
    }

    public function getCategoriesByBranch(Request $request)
    {
        $fromBranchId = $request->get('from_branch_id');

        if (! is_numeric($fromBranchId)) {
            return response()->json(['message' => 'Invalid branch selected.', 'categories' => []]);
        }

        $activeStatusId = CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Active')
            ->value('ID');

        $categories = DB::table('t_Items')
            ->join('t_Stockitems', 't_Items.Id', '=', 't_Stockitems.ItemId')
            ->join('t_ItemCategories', 't_Items.Category', '=', 't_ItemCategories.Id')
            ->leftJoin('t_ItemCategories as parent_category', 't_ItemCategories.ParentId', '=', 'parent_category.Id')
            ->select(
                't_ItemCategories.Id',
                't_ItemCategories.Name',
                't_ItemCategories.ParentId',
                'parent_category.Name as ParentName'
            )
            ->where('t_Stockitems.Branch', $fromBranchId)
            ->where('t_Items.Status', $activeStatusId)
            ->distinct('t_ItemCategories.Id')
            ->get();

        $result = [];
        $uniqueTopLevelCategories = [];

        foreach ($categories as $category) {
            if ($category->ParentId === null) {
                $uniqueTopLevelCategories[$category->Id] = ['Id' => $category->Id, 'Name' => $category->Name];
            } else {
                if (! isset($uniqueTopLevelCategories[$category->ParentId])) {
                    $uniqueTopLevelCategories[$category->ParentId] = ['Id' => $category->ParentId, 'Name' => $category->ParentName];
                }
            }
        }

        usort($uniqueTopLevelCategories, fn ($a, $b) => strcmp($a['Name'], $b['Name']));

        if (empty($uniqueTopLevelCategories)) {
            return response()->json(['message' => 'No categories with available items in this branch.', 'categories' => []]);
        }

        return response()->json(['message' => 'Categories retrieved successfully.', 'categories' => array_values($uniqueTopLevelCategories)]);
    }

    public function getSubcategoriesByBranchAndCategory(Request $request)
    {
        $fromBranchId = $request->get('from_branch_id');
        $categoryId = $request->get('category_id');

        if (! is_numeric($fromBranchId) || ! is_numeric($categoryId)) {
            return response()->json(['message' => 'Invalid branch or category selected.', 'subcategories' => []]);
        }

        $activeStatusId = CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Active')
            ->value('ID');

        $subcategories = DB::table('t_Items')
            ->join('t_Stockitems', 't_Items.Id', '=', 't_Stockitems.ItemId')
            ->join('t_ItemCategories', 't_Items.Category', '=', 't_ItemCategories.Id')
            ->select('t_ItemCategories.Id', 't_ItemCategories.Name')
            ->where('t_Stockitems.Branch', $fromBranchId)
            ->where('t_ItemCategories.ParentId', $categoryId)
            ->where('t_Items.Status', $activeStatusId)
            ->distinct('t_ItemCategories.Id')
            ->get();

        if ($subcategories->isEmpty()) {
            return response()->json(['message' => 'No subcategories with available items for this category in this branch.', 'subcategories' => []]);
        }

        return response()->json(['message' => 'Subcategories retrieved successfully.', 'subcategories' => $subcategories]);
    }

    public function getItemsByBranchAndCategoryOrSubcategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategoryId = $request->get('subcategory_id');
        $fromBranchId = $request->get('from_branch_id');

        if (
            (! is_null($subcategoryId) && ! is_numeric($subcategoryId)) ||
            (! is_null($categoryId) && ! is_numeric($categoryId)) ||
            (! is_null($fromBranchId) && ! is_numeric($fromBranchId))
        ) {
            return response()->json(['message' => 'Invalid input provided.', 'items' => []]);
        }

        if (empty($fromBranchId)) {
            return response()->json([
                'message' => 'Please select a "Requesting Branch" first to view available items.',
                'items' => [],
            ]);
        }

        $activeItemStatusId = CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Active')
            ->value('ID');

        $itemsQuery = DB::table('t_Items')
            ->join('t_Stockitems', 't_Items.Id', '=', 't_Stockitems.ItemId')
            ->select('t_Items.Id', 't_Items.ItemName')
            ->where('t_Items.Status', $activeItemStatusId)
            ->where('t_Stockitems.Branch', $fromBranchId);

        $itemsQuery->where('t_Stockitems.Status', 1);

        if ($subcategoryId) {
            $itemsQuery->where('t_Items.Category', $subcategoryId);
        } elseif ($categoryId) {
            $itemsQuery->where('t_Items.Category', $categoryId);
        } else {
            return response()->json(['message' => 'Please select a category or subcategory.', 'items' => []]);
        }

        $items = $itemsQuery->distinct('t_Items.Id')->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'No active items in stock for this selection in this branch.', 'items' => []]);
        }

        return response()->json([
            'message' => 'Items retrieved successfully.',
            'items' => $items,
        ]);
    }
}
