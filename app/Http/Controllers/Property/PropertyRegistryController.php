<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyRegistryRequest;
use App\Models\Core\CategoryMaster;
use App\Models\Core\Locality;
use App\Models\PropertyManagement\PropertyType;
use App\Services\Property\PropertyRegistry\PropertyRegistryService;
use App\Models\PropertyManagement\PropertyRegistry;
use Illuminate\Support\Carbon;

class PropertyRegistryController extends Controller
{
    //
    public function index()
    {
         $properties = PropertyRegistry::all();
        return view('property.propertyregistry.registry.index', compact('properties'));
    }

    public function create(){
        $lineentries = CategoryMaster::with('propertytypes')->get();
        $localities = Locality::all();
        return view('property.propertyregistry.registry.create', compact('lineentries','localities'));
    }

    public function getTypesByCategory($categoryId)
    {
        $types = PropertyType::where('PropertyCategoryId', $categoryId)->get();
        return response()->json($types);
    }

    public function show($id){
        $property = PropertyRegistry::find($id);
        return view('property.propertyregistry.registry.show',compact('property'));
    }
public function store(PropertyRegistryRequest $request)
{
    
    $validated = $request->validated();
    //dd($validated);
    // Fetch model instances based on validated IDs
    $acquisitionDate = Carbon::parse($validated['AcquisitionDate']);
    $propertyType = PropertyType::findOrFail($validated['PropertyType']);
    $category = CategoryMaster::findOrFail($validated['Category']);
    $townCity = Locality::findOrFail($validated['TownCity']);


    // Call the service with structured arguments
    $property = PropertyRegistryService::create(
        PropertyName: $validated['PropertyName'],
        PropertyCode: $validated['PropertyCode'],
        PropertyType: $propertyType,
        Category: $category,
        Owner: $validated['Owner'],
        AcquisitionDate: $acquisitionDate,
        Country: $validated['Country'],
        TownCity: $townCity,
        AreaLocality: $validated['AreaLocality'],
        PropertyDescription: $validated['PropertyDescription'] ?? '',
    );

    return redirect()->route('PropertyRegistry.index')
        ->with('success', 'Property registry created successfully');
}

}
