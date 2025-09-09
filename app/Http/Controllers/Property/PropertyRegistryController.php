<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $properties = PropertyRegistry::with('type')->get();
        return view('property.propertyregistry.registry.index', compact('properties'));
    }

    public function create(){
        $lineentries = CategoryMaster::with('propertytypes')->get();
        $localities = Locality::all();
        return view('property.propertyregistry.registry.create', compact('lineentries', 'localities'));
    }

    public function getTypesByCategory($categoryId)
    {
        $types = PropertyType::where('PropertyCategoryId', $categoryId)->get();
        return response()->json($types);
    }

    public function show($id){
        $property = PropertyRegistry::find($id);
        // $createdByUser = User::find($property->CreatedBy);
        // $modifiedByUser = User::find($property->ModifiedBy);
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

        foreach ($request->file('file', []) as $uploadedFile) {
        PropertyRegistryService::create(
            $validated['PropertyName'],
            $validated['PropertyCode'],
            $propertyType,
            $category,
            $validated['Owner'],
            $acquisitionDate,
            $validated['Country'],
            $townCity,
            $validated['AreaLocality'],
            $validated['PropertyDescription'] ?? '',
            $request->user(),
            $uploadedFile
            );
        }

        return redirect()->route('PropertyRegistry.index')
            ->with('success', 'Property registry created successfully');
    }

    public function edit($id)
    {
        //Check if user has permission to edit tender categories
        // $this->authorize(PermissionEnum::PropertyTypeUpdate, PropertyType::class);
        $property = PropertyRegistry::findOrFail($id);
        $types = PropertyType::all();
        $categories = CategoryMaster::all();
        $lineentries = CategoryMaster::with('propertytypes')->get();
        $localities = Locality::all();

        return view('property.propertyregistry.registry.edit', compact('property', 'localities', 'lineentries', 'types', 'categories'));
    }

public function update(PropertyRegistryRequest $request, $id)
{
    $validated = $request->validated();

    // Fetch model instances based on validated IDs
    $acquisitionDate = Carbon::parse($validated['AcquisitionDate']);
    $propertyType    = PropertyType::findOrFail($validated['PropertyType']);
    $category        = CategoryMaster::findOrFail($validated['Category']);
    $townCity        = Locality::findOrFail($validated['TownCity']);

    DB::beginTransaction();

    try {
        $property = PropertyRegistry::findOrFail($id);

        // Update the main property record first (no file yet)
        PropertyRegistryService::update(
            $property,
            $validated['PropertyName'],
            $validated['PropertyCode'],
            $propertyType,
            $category,
            $validated['Owner'],
            $acquisitionDate,
            $validated['Country'],
            $townCity,
            $validated['AreaLocality'],
            $validated['PropertyDescription'] ?? '',
            $request->user()
        );

        // Handle file uploads (loop like in store)
        foreach ($request->file('file', []) as $uploadedFile) {
            PropertyRegistryService::update(
                $property,
                $validated['PropertyName'],
                $validated['PropertyCode'],
                $propertyType,
                $category,
                $validated['Owner'],
                $acquisitionDate,
                $validated['Country'],
                $townCity,
                $validated['AreaLocality'],
                $validated['PropertyDescription'] ?? '',
                $request->user(),
                $uploadedFile
            );
        }

        DB::commit();

        return redirect()
            ->route('PropertyRegistry.index')
            ->with('success', 'Property updated successfully');
    } catch (\Throwable $th) {
        DB::rollBack();
        Log::error('Failed to update property: ' . $th->getMessage());

        return back()
            ->withErrors(['error' => 'Failed to update property'])
            ->withInput();
    }
}



    public function destroy($id)
    {
        //Check if user has permission to delete property categories
        //$this->authorize(PermissionEnum::PropertyTypeDelete , PropertyType::class);
        try {
            $property = PropertyRegistry::findOrFail($id);
            $property->delete();

            return redirect()->route('PropertyRegistry.index')
                ->with('success', 'Property Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting property: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Property. Please try again.'])
                ->withInput();
        }
    }


}
