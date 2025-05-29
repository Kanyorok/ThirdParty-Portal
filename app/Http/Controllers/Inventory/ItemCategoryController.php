<?php

namespace App\Http\Controllers\Inventory;


use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemCategories;
use App\Http\Requests\Inventory\StoreItemCategoryRequest;
use App\Http\Requests\Inventory\UpdateItemCategoryRequest;
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
        $categories = ItemCategories::whereNull('ParentId')->with('parent')->get();
        return view('inventory.itemmaster.itemcategory.index', compact('categories'));
    }

    public function create()
    {
        $this->authorize('create', ItemCategories::class);
        $categories = ItemCategories::whereNull('ParentId')->get();
        return view('inventory.itemmaster.itemcategory.create', compact('categories'));
    }

    public function store(StoreItemCategoryRequest $request)
    {
        $this->authorize('create', ItemCategories::class);
        $this->service->create($request->validated());
        return redirect()->route('itemcategory.index')->with('success', 'Category created successfully.');
    }

    public function show($id)
    {
        $category = ItemCategories::with('parent', 'children')->findOrFail($id);
        $this->authorize('view', $category);
        return view('inventory.itemmaster.itemcategory.show', compact('category'));
    }

    public function edit($id)
    {
        $category = ItemCategories::with('parent')->findOrFail($id);
        $this->authorize('update', $category);
        $categories = ItemCategories::whereNull('ParentId')->where('Id', '!=', $id)->get();
        return view('inventory.itemmaster.itemcategory.edit', compact('category', 'categories'));
    }

    public function update(UpdateItemCategoryRequest $request, $id)
    {
        $category = ItemCategories::findOrFail($id);
        $this->authorize('update', $category);
        $this->service->update($category, $request->validated());
        return redirect()->route('itemcategory.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = ItemCategories::findOrFail($id);
        $this->authorize('destroy', $category);
        $this->service->destroy($category);
        return redirect()->route('itemcategory.index')->with('success', 'Category deleted successfully.');
    }
}
