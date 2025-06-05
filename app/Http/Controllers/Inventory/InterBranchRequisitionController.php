<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InterBranchRequisitionRequest;
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
        $groupedRequisitions = InterBranchRequisition::with(['fromBranch', 'toBranch', 'items'])
            ->orderBy('CreatedOn', 'asc')
            ->get()
            ->groupBy('Id')
            ->map(function ($group) {
                $first = $group->first();
                $first->Items = $group->count();
                // Add status for display
                $first->StatusLabel = $first->Status ?? 'N/A';
                return $first;
            })
            ->values();

        return view('inventory.interbranchrequisition.index', compact('groupedRequisitions'));
    }

    public function create()
    {
        $categories = ItemCategories::whereNull('ParentId')->get();
        $branches = Branch::all();
        $uoms = UnitOfMeasure::all();

        return view('inventory.interbranchrequisition.create', compact('branches', 'uoms', 'categories'));
    }

    public function store(InterBranchRequisitionRequest $request)
    {
        $data = $request->validated();

        if (!isset($data['Status'])) {
            $data['Status'] = 'Pending Approval';
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
            'items.uom',
        ])->findOrFail($Id);

        return view('inventory.interbranchrequisition.show', compact('item'));
    }

    public function edit($Id)
    {
        $item = InterBranchRequisition::with([
            'fromBranch',
            'toBranch',
            'creator',
            'items',
            'items.item.category.parent',
            'items.uom',
        ])->findOrFail($Id);

        $categories = ItemCategories::whereNull('ParentId')->get();
        $branches = Branch::all();
        $uoms = UnitOfMeasure::all();

        return view('inventory.interbranchrequisition.edit', compact('item', 'branches', 'uoms', 'categories'));
    }

    public function update(InterBranchRequisitionRequest $request, $Id)
    {
        $item = InterBranchRequisition::with('items')->findOrFail($Id);
        $data = $request->validated();

        // Optionally allow status to be updated
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

    public function getItemsByCategoryOrSubcategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategoryId = $request->get('subcategory_id');

        // Ensure only integer IDs are used
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