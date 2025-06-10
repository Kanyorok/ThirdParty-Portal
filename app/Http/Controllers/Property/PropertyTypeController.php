<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyTypeRequest;
use App\Models\Core\CategoryMaster;
use App\Services\Property\PropertyRegistry\PropertyTypeService;
use App\Models\PropertyManagement\PropertyType;

class PropertyTypeController extends Controller
{

    public function index()
    {
        $types = PropertyType::with('propertycategory')->get();
        //dd($properties);
        return view('property.propertyregistry.propertytype.index', compact('types'));
    }

    public function create(){
        $categories = CategoryMaster::all();
        return view('property.propertyregistry.propertytype.create', compact('categories'));
    }

    public function store(PropertyTypeRequest $request)
    {
        $validated = $request->validated();

        $category = CategoryMaster::findOrFail($validated['PropertyCategoryId']);

        $propertyType = PropertyTypeService::create(
            $validated['PropertyTypeName'],
            $category,
            $validated['Description'] ?? '',
            auth()->user()
        );

        //$this->authorize('store', $propertyType);

        return redirect()->route('propertytype.index')->with('success', 'Property type created successfully');
    }

}
