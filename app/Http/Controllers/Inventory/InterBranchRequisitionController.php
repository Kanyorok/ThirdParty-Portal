<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInterBranchRequisitionRequest;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\ItemCategories;
use App\Models\Core\Branch;
use App\Services\Inventory\InterBranchRequisitionService;
use Illuminate\Http\Request;

class InterBranchRequisitionController extends Controller
{
    protected $service;

    public function __construct(InterBranchRequisitionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $groupedRequisitions = InterBranchRequisition::with(['fromBranch', 'toBranch'])
            ->orderBy('CreatedOn', 'desc')
            ->get()
            ->groupBy('ReqNo')
            ->map(function ($group) {
                $first = $group->first();
                $first->Items = $group->count();
                return $first;
            })
            ->values();

        return view('inventory.interbranchrequisition.index', compact('groupedRequisitions'));
    }

    public function create()
    {
        $branches = Branch::all();
        $uoms = UnitOfMeasure::all();
        $categories = ItemCategories::whereNull('ParentId')->get();

        return view('inventory.interbranchrequisition.create', compact('branches', 'uoms', 'categories'));
    }

    public function store(InterBranchRequisitionRequest $request)
    {
        $this->service->create($request->validated());
        return redirect()->route('interbranchrequisition.index')->with('success', 'Requisition submitted successfully.');
    }

    public function show(string $reqNo)
    {
        $items = InterBranchRequisition::where('ReqNo', $reqNo)->with(['item', 'uom', 'fromBranch', 'toBranch'])->get();
        return view('inventory.interbranchrequisition.show', compact('items', 'reqNo'));
    }

    public function destroy(string $reqNo)
    {
        $items = InterBranchRequisition::where('ReqNo', $reqNo)->get();
        foreach ($items as $item) {
            $this->service->delete($item);
        }
        return redirect()->route('interbranchrequisition.index')->with('success', 'Requisition deleted successfully.');
    }
    public function getItemsByCategoryOrSubcategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategoryId = $request->get('subcategory_id');

        $items = ItemMasterList::where('Category', $subcategoryId ?? $categoryId)->get(['Id', 'ItemName', 'iItemCode']);
        return response()->json($items);
    }
}
