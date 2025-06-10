<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyRegistryRequest;
use App\Services\Property\PropertyRegistry\PropertyRegistryService;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyType;
use App\Models\PropertyManagement\PropertyCategory;

class PropertyRegistryController extends Controller
{
    //
    public function index()
    {
        $properties = PropertyRegistry::all();
        return view('property.propertyregistry.registry.index', compact('properties'));
    }

    public function create()
    {
        $types = PropertyType::all();
        $categories = PropertyCategory::all();
        return view('property.propertyregistry.registry.create', compact('types', 'categories'));
    }

    public function show($id)
    {
        $property = PropertyRegistry::find($id);
        return view('property.propertyregistry.registry.show', compact('property'));
    }

    public function store(PropertyRegistryRequest $request)
    {
        $property = PropertyRegistryService::create(
            PropertyName: $request->PropertyName,
            PropertyCode: $request->PropertyCode,
            PropertyType: $request->PropertyType,
            Category: $request->Category,
            Owner: $request->Owner,
            AcquisitionDate: $request->AcquisitionDate,
            Country: $request->Country,
            TownCity: $request->TownCity,
            AreaLocality: $request->AreaLocality,
            PropertyDescription: $request->PropertyDescription,
        );

        return redirect()->route('PropertyRegistry.index')
            ->with('success', 'Property registry created successfully');
    }
}
