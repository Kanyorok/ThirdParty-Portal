<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\SupplierCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Procurement\Suppliers\StoreSupplierCategoryRequest;
use App\Http\Requests\Procurement\Suppliers\UpdateSupplierCategoryRequest;

class SupplierCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $categories = SupplierCategory::all();
            return response()->json($categories);
        }

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

    public function getPreferredCategories(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->id();
        if (!$user || !$user->thirdParty) {
            return response()->json(['message' => 'User not authenticated or not associated with a third party.'], 404);
        }

        $preferredCategories = $user->thirdParty->supplierCategories;

        return response()->json($preferredCategories);
    }

    public function updatePreferredCategories(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:t_SupplierCategories,SupplierCategoryID',
        ]);

        $user = auth()->user();
        if (!$user || !$user->thirdParty) {
            return response()->json(['message' => 'User not authenticated or not associated with a third party.'], 404);
        }

        $user->thirdParty->supplierCategories()->sync($request->input('category_ids'));

        return response()->json([
            'message' => 'Preferred categories updated successfully.',
            'data' => $user->thirdParty->supplierCategories
        ], 200);
    }
}
