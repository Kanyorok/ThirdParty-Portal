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

class InterBranchRequisitionController extends Controller
{
    protected $service;

    public function __construct(InterBranchRequisitionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request) {
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
        $this->authorize('create', InterBranchRequisition::class);
        $categories = ItemCategories::whereNull('ParentId')->get();
        $branches = Branch::all();
        $uoms = UnitOfMeasure::all();

        return view('inventory.interbranchrequisition.create', compact('branches', 'uoms', 'categories'));
    }

    public function store(InterBranchRequisitionRequest $request)
    {
        $this->authorize('create', InterBranchRequisition::class);
        $data = $request->validated();

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

        $categories = ItemCategories::whereNull('ParentId')->get();
        $branches = Branch::all();
        $uoms = UnitOfMeasure::all();

        return view('inventory.interbranchrequisition.edit', compact('item', 'branches', 'uoms', 'categories'));
    }

    public function update(InterBranchRequisitionRequest $request, $Id)
    {
        $this->authorize('update', InterBranchRequisition::class);
        $item = InterBranchRequisition::with('items')->findOrFail($Id);
        $data = $request->validated();

     
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
        $this->authorize('destroy', InterBranchRequisition::class);
        $this->service->delete($item);
        return redirect()->route('interbranchrequisition.index')->with('success', 'Requisition deleted successfully.');
    }

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

    public function getItemsByCategoryOrSubcategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategoryId = $request->get('subcategory_id');

        if ((!is_null($subcategoryId) && !is_numeric($subcategoryId)) ||
            (!is_null($categoryId) && !is_numeric($categoryId))) {
            return response()->json([]);
        }

        $items = \DB::table('t_Items')
            ->where('Category', $subcategoryId ?? $categoryId)
            ->select('Id', 'ItemName')
            ->get();

        return response()->json($items);
    }
    
}