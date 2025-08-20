<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\SupplierCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Procurement\Suppliers\StoreSupplierCategoryRequest;
use App\Http\Requests\Procurement\Suppliers\UpdateSupplierCategoryRequest;
use Illuminate\Http\JsonResponse;

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
            );

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
                ->editColumn('IsActive', fn($row) => $row->IsActive ? 'Yes' : 'No')
                ->rawColumns(['actions'])
                ->make(true);
        }

        $supplierCategories = SupplierCategory::all();

        return view('procurement.suppliers.supplier_categories.index', compact('supplierCategories'));
    }

    public function all(): JsonResponse
    {
        $categories = SupplierCategory::all();
        return response()->json($categories);
    }

    public function create()
    {
        return view('procurement.suppliers.supplier_categories.create');
    }

    public function store(StoreSupplierCategoryRequest $request)
    {
        $validatedData = array_merge($request->validated(), [
            'CreatedBy' => auth()->id(),
        ]);

        $category = SupplierCategory::create($validatedData);

        if ($request->wantsJson()) {
            return response()->json($category, 201);
        }

        return redirect()->route('proc.supplier-cat.index')->with('success', 'Supplier Category created successfully.');
    }

    public function edit(SupplierCategory $supplier_cat)
    {
        return view('procurement.suppliers.supplier_categories.edit', [
            'category' => $supplier_cat,
        ]);
    }

    public function update(UpdateSupplierCategoryRequest $request, SupplierCategory $supplierCategory)
    {
        $validatedData = array_merge($request->validated(), [
            'ModifiedBy' => auth()->id(),
        ]);

        $supplierCategory->update($validatedData);

        if ($request->wantsJson()) {
            return response()->json($supplierCategory, 200);
        }

        return redirect()->route('proc.supplier-cat.index')->with('success', 'Supplier Category updated successfully.');
    }

    public function destroy(SupplierCategory $supplierCategory)
    {
        $supplierCategory->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('proc.supplier-cat.index')->with('success', 'Supplier Category deleted successfully.');
    }
}
