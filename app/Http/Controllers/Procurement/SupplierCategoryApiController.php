<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Suppliers\StoreSupplierCategoryRequest;
use App\Http\Requests\Procurement\Suppliers\UpdateSupplierCategoryRequest;
use App\Models\ThirdParty\SupplierCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierCategoryApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = SupplierCategory::all();

        return response()->json($categories);
    }

    public function store(StoreSupplierCategoryRequest $request): JsonResponse
    {
        $validatedData = array_merge($request->validated(), [
            'CreatedBy' => auth()->id(),
        ]);

        $category = SupplierCategory::create($validatedData);

        return response()->json($category, 201);
    }

    public function show(SupplierCategory $supplierCategory): JsonResponse
    {
        return response()->json($supplierCategory);
    }

    public function update(UpdateSupplierCategoryRequest $request, SupplierCategory $supplierCategory): JsonResponse
    {
        $validatedData = array_merge($request->validated(), [
            'ModifiedBy' => auth()->id(),
        ]);

        $supplierCategory->update($validatedData);

        return response()->json($supplierCategory, 200);
    }

    public function destroy(SupplierCategory $supplierCategory): JsonResponse
    {
        $supplierCategory->delete();

        return response()->json(['success' => true], 200);
    }
}
