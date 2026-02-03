<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Suppliers\StoreSupplierCategoryRequest;
use App\Http\Requests\Procurement\Suppliers\UpdateSupplierCategoryRequest;
use App\Models\Inventory\ItemCategories;
use App\Models\ThirdParty\SupplierCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class SupplierCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = SupplierCategory::select(
                'SupplierCategoryID',
                'CategoryName',
                'Description',
                'IsActive',
                'CreatedOn'
            )->whereNull('DeletedOn');

            return DataTables::of($categories)
                ->addColumn('actions', function ($row) {
                    $editUrl = route('proc.supplier-cat.edit', $row->SupplierCategoryID);
                    $deleteUrl = route('proc.supplier-cat.destroy', $row->SupplierCategoryID);

                    return '
                        <a href="' . $editUrl . '" class="btn btn-warning btn-sm">Edit</a>
                        <form action="' . $deleteUrl . '" method="POST" style="display:inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete this category?\')">Delete</button>
                        </form>
                    ';
                })
                ->editColumn('IsActive', fn ($row) => $row->IsActive ? 'Yes' : 'No')
                ->rawColumns(['actions'])
                ->make(true);
        }

        $supplierCategories = SupplierCategory::whereNull('DeletedOn')->latest('CreatedOn')->get();

        return view('procurement.suppliers.supplier_categories.index', compact('supplierCategories'));
    }

    public function all(): JsonResponse
    {
        $categories = SupplierCategory::all();

        return response()->json($categories);
    }

    public function create()
    {
        $itemCategories = ItemCategories::whereNull('ParentId')->orderBy('Name')->get(['Id', 'Name']);

        return view('procurement.suppliers.supplier_categories.create', compact('itemCategories'));
    }

    public function store(StoreSupplierCategoryRequest $request)
    {
        $validatedData = array_merge($request->validated(), [
            'CreatedBy' => Auth::id(),
        ]);

        $category = SupplierCategory::create($validatedData);

        // Attach selected item categories (top-level) if provided
        $itemCategoryIds = collect($request->input('item_category_ids', []))
            ->filter()->unique()->values();
        if ($itemCategoryIds->isNotEmpty()) {
            $category->syncItemCategoriesWithAudit($itemCategoryIds->all(), Auth::id());
        }

        if ($request->wantsJson()) {
            return response()->json($category->load('itemCategories'), 201);
        }

        return redirect()->route('proc.supplier-cat.index')->with('success', 'Supplier Category created successfully.');
    }

    public function edit(SupplierCategory $supplier_cat)
    {
        $supplier_cat->load('itemCategories');
        $itemCategories = ItemCategories::whereNull('ParentId')->orderBy('Name')->get(['Id', 'Name']);

        return view('procurement.suppliers.supplier_categories.edit', [
            'category' => $supplier_cat,
            'itemCategories' => $itemCategories,
        ]);
    }

    public function update(UpdateSupplierCategoryRequest $request, SupplierCategory $supplier_cat)
    {
        $validatedData = array_merge($request->validated(), [
            'ModifiedBy' => Auth::id(),
        ]);

        // Remove pivot ids before update if present
        $itemCategoryIds = collect($validatedData['item_category_ids'] ?? $request->input('item_category_ids', []))
            ->filter()->unique();
        unset($validatedData['item_category_ids']);

        $supplier_cat->update($validatedData);

        if ($itemCategoryIds->isNotEmpty()) {
            $supplier_cat->syncItemCategoriesWithAudit($itemCategoryIds->all(), Auth::id());
        } elseif ($request->has('item_category_ids')) {
            // Treat as removing all (soft delete existing pivots)
            $supplier_cat->syncItemCategoriesWithAudit([], Auth::id());
        }

        if ($request->wantsJson()) {
            return response()->json($supplier_cat->load('itemCategories'), 200);
        }

        return redirect()->route('proc.supplier-cat.index')->with('success', 'Supplier Category updated successfully.');
    }

    public function destroy(SupplierCategory $supplier_cat)
    {
        $supplier_cat->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('proc.supplier-cat.index')->with('success', 'Supplier Category deleted successfully.');
    }
}
