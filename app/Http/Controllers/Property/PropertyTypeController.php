<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyTypeRequest;
use App\Models\Core\CategoryMaster;
use App\Models\PropertyManagement\PropertyType;
use App\Services\Property\PropertyRegistry\PropertyTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyTypeController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::PropertyTypeView, PropertyType::class);
        $types = PropertyType::with('propertycategory')->orderBy('Id', 'desc')->get();

        return view('property.propertyregistry.propertytype.index', compact('types'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyTypeCreate, PropertyType::class);
        $categories = CategoryMaster::all();

        return view('property.propertyregistry.propertytype.create', compact('categories'));
    }

    public function store(PropertyTypeRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyTypeCreate, PropertyType::class);
        $validated = $request->validated();

        $category = CategoryMaster::findOrFail($validated['PropertyCategoryId']);

        $propertyType = PropertyTypeService::create(
            $validated['PropertyTypeName'],
            $category,
            $validated['Description'] ?? '',
            auth()->user()
        );


        return redirect()->route('propertytype.index')->with('success', 'Property type created successfully');
    }

    public function edit($id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::PropertyTypeUpdate, PropertyType::class);
        $type = PropertyType::findOrFail($id);
        $categories = CategoryMaster::all();

        return view('property.propertyregistry.propertytype.edit', compact('type', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyTypeUpdate, PropertyType::class);
        $validated = $request->validate([
            'PropertyTypeName' => 'required|string|max:50',
            'PropertyCategoryId' => 'required|exists:t_CategoryMaster,Id',
            'Description' => 'nullable|string|max:100',

        ]);

        DB::beginTransaction();

        try {
            $type = PropertyType::findOrFail($id);

            $type->update([
                'PropertyTypeName' => $validated['PropertyTypeName'],
                'PropertyCategoryId' => $validated['PropertyCategoryId'],
                'Description' => $validated['Description'] ?? '',
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($type)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Type');

            return redirect()->route('propertytype.index')->with('success', 'Type updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update type:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update type'])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::PropertyTypeDelete, PropertyType::class);

        try {
            $type = PropertyType::findOrFail($id);

            // Check if this type has any related properties
            if ($type->property()->exists()) {
                return redirect()->back()
                    ->withErrors(['error' => 'This Property Type is in use and cannot be deleted.']);
            }

            $type->delete();

            return redirect()->route('propertytype.index')
                ->with('success', 'Property Type Deleted Successfully!');
        } catch (\Throwable $th) {
            Log::error('Error deleting property type: ' . $th->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Property Type. Please try again.'])
                ->withInput();
        }
    }
}
