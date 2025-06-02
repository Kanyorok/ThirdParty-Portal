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
        $categories = ItemCategories::whereNull('ParentId')->get();
        $branches = Branch::all();
        $uoms = UnitOfMeasure::all();
       

        return view('inventory.interbranchrequisition.create', compact('branches', 'uoms', 'categories'));
    }

    public function store(InterBranchRequisitionRequest $request)
    {
        $this->service->create($request->validated());
        return redirect()->route('interbranchrequisition.index')->with('success', 'Requisition submitted successfully.');
    }

    public function show($Id)
    {
        $item = InterBranchRequisition::findOrFail($Id);
        return view('inventory.interbranchrequisition.show', compact('item'));
    }

    public function edit($Id)
{
    $item = InterBranchRequisition::with(['item.category'])->findOrFail($Id);
    
    $categories = ItemCategories::whereNull('ParentId')->get();
    $branches = Branch::all();
    $uoms = UnitOfMeasure::all(); // Changed variable name for consistency
    
    // Safely get category information
    $categoryId = null;
    $subcategoryId = null;
    
    if ($item->item && $item->item->category) {
        $categoryId = $item->item->category->parent 
            ? $item->item->category->parent->Id 
            : $item->item->category->Id;
        $subcategoryId = $item->item->category->parent 
            ? $item->item->category->Id 
            : null;
    }
    
    $items = ItemMasterList::where('Category', $subcategoryId ?? $categoryId)->get();
    
    // Removed 'stores' from compact as it's not defined
    return view('inventory.interbranchrequisition.edit', compact('item', 'branches', 'uoms', 'categories', 'items'));
}

public function update(InterBranchRequisitionRequest $request, $Id)
{
    $item = InterBranchRequisition::findOrFail($Id); // Fixed: was UnitOfMeasure
    $data = $request->validated();
    
    try {
        $this->service->update($item, $data); // Fixed: was InterBranchRequisitionService
        return redirect()->route('interbranchrequisition.index')->with('success', 'Requisition updated successfully!');
    } catch (\Exception $e) {
        return back()->withErrors('Failed to update Requisition: ' . $e->getMessage())->withInput();
    }
}


    public function destroy($Id)
    {
        $items = InterBranchRequisition::where('Id', $Id)->get();
        foreach ($items as $item) {
            $this->service->delete($item);
        }
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

    $items = \DB::table('t_Items')
        ->where('Category', $subcategoryId ?? $categoryId)
        ->select('Id', 'ItemName', 'ItemCode') // Make sure ItemCode is included
        ->get();

    return response()->json($items); // ✅ RETURN THE DATA
}




    

}