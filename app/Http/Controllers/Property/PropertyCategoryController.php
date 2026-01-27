<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyCategoryRequest;
use App\Models\Core\CategoryMaster;
use App\Services\Property\PropertyRegistry\PropertyCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyCategoryController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::PropertyCategoryView, CategoryMaster::class);
        $categories = CategoryMaster::where('Code', '500000')->get();

        //dd($categories);
        return view('property.propertyregistry.propertycategory.index', compact('categories'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyCategoryCreate, CategoryMaster::class);

        return view('property.propertyregistry.propertycategory.create');
    }

    public function store(PropertyCategoryRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyCategoryCreate, CategoryMaster::class);
        $validated = $request->validated();
        //Type and Code have been hardcoded
        $propertyCategory = PropertyCategoryService::create(
            $validated['Name'],
            $validated['Description'],
            'PropertyCategory',
            '500000',
            Auth::user()
        );

        return redirect()->route('propertycategory.index')->with('success', 'property category created successfully');
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::PropertyCategoryUpdate, CategoryMaster::class);
        $category = CategoryMaster::findOrFail($id);

        return view('property.propertyregistry.propertycategory.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyCategoryUpdate, CategoryMaster::class);
        $validated = $request->validate([
            'Description' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $category = CategoryMaster::findOrFail($id);

            $category->update([
                'Description' => $validated['Description'] ?? '',
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($category)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Category');

            return redirect()->route('propertycategory.index')->with('success', 'Category updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update category:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update category'])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::PropertyCategoryDelete, CategoryMaster::class);

        try {
            $category = CategoryMaster::findOrFail($id);

            if ($category->propertytypes()->exists()) {
                return redirect()->back()
                    ->withErrors(['error' => 'This Property category is in use and cannot be deleted.']);
            }

            $category->delete();

            return redirect()->route('propertycategory.index')
                ->with('success', 'Property Category Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting property category: ' . $th->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Property Category. Please try again.'])
                ->withInput();
        }
    }
}
