<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreItemCategoryRequest;
use App\Http\Requests\Inventory\UpdateItemCategoryRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemType;
use App\Services\Inventory\ItemCategoryService;

class ItemCategoryController extends Controller
{
    protected $service;

    public function __construct(ItemCategoryService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $allowed = [5, 10, 20, 50];
        $requested = request()->query('perPage');

        if ($requested !== null) {
            $perPage = intval($requested);
            if (! in_array($perPage, $allowed)) {
                $perPage = 20;
            }
            session(['itemcategory.perPage' => $perPage]);
        } else {
            $perPage = session('itemcategory.perPage', 20);
            if (! in_array($perPage, $allowed)) {
                $perPage = 20;
            }
        }

        $categories = ItemCategories::whereNull('ParentId')
            ->with('parent', 'status', 'itemType.type')
            ->orderByDesc('Id')
            ->paginate($perPage);

        return view('inventory.itemmaster.itemcategory.index', compact('categories'));
    }

    public function create()
    {
        $this->authorize('create', ItemCategories::class);

        $activeStatusId = CodeDetail::where('CodeID', 'CategoryStatus')
            ->where('Description', 'Active')
            ->value('Id');

        $categories = ItemCategories::whereNull('ParentId')
            ->where('Status', $activeStatusId)
            ->get();

        $itemTypes = ItemType::with('type')->where('Active', 1)->get();

        return view('inventory.itemmaster.itemcategory.create', compact('categories', 'itemTypes'));
    }

    public function store(StoreItemCategoryRequest $request)
    {
        $this->authorize('create', ItemCategories::class);

        $activeStatusId = CodeDetail::where('CodeID', 'CategoryStatus')
            ->where('Description', 'Active')
            ->value('Id');

        $data = $request->validated();
        $data['Status'] = $activeStatusId;

        $this->service->create($data);

        return redirect()->route('itemcategory.index')
            ->with('success', 'Category created successfully.');
    }

    public function show($id)
    {
        $category = ItemCategories::with('parent', 'children', 'itemType.type')->findOrFail($id);
        $this->authorize('view', $category);

        return view('inventory.itemmaster.itemcategory.show', compact('category'));
    }

    public function edit($id)
    {
        $category = ItemCategories::with('parent')->findOrFail($id);
        $this->authorize('update', $category);
        $categories = ItemCategories::whereNull('ParentId')->where('Id', '!=', $id)->get();
        $status = CodeDetail::where('CodeID', 'CategoryStatus')
            ->orderBy('Value')
            ->get();
        $itemTypes = ItemType::with('type')->where('Active', 1)->get();

        return view('inventory.itemmaster.itemcategory.edit', compact('category', 'categories', 'status', 'itemTypes'));
    }

    public function update(UpdateItemCategoryRequest $request, $id)
    {
        $category = ItemCategories::findOrFail($id);
        $this->authorize('update', $category);

        $this->service->update($category, $request->validated());

        return redirect()->route('itemcategory.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = ItemCategories::findOrFail($id);
        $this->authorize('destroy', $category);
        $this->service->destroy($category);

        return redirect()->route('itemcategory.index')->with('success', 'Category deleted successfully.');
    }
}
