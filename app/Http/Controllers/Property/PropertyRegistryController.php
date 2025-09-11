<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
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
        $this->authorize(PermissionEnum::PropertyRegistryView, PropertyRegistry::class);
        $properties = PropertyRegistry::with('type')->get();
        return view('property.propertyregistry.registry.index', compact('properties'));
    }

    public function create(){
        $this->authorize(PermissionEnum::PropertyRegistryCreate, PropertyRegistry::class);
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
        $this->authorize(PermissionEnum::PropertyRegistryView, PropertyRegistry::class);
        $property = PropertyRegistry::find($id);
        return view('property.propertyregistry.registry.show',compact('property'));
    }

    public function store(PropertyRegistryRequest $request)
    {

        $this->authorize(PermissionEnum::PropertyRegistryCreate, PropertyRegistry::class);
        $validated = $request->validated();
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
        $this->authorize(PermissionEnum::PropertyRegistryUpdate, PropertyRegistry::class);
        $property = PropertyRegistry::findOrFail($id);
        $types = PropertyType::all();
        $categories = CategoryMaster::all();
        $lineentries = CategoryMaster::with('propertytypes')->get();
        $localities = Locality::all();

        return view('property.propertyregistry.registry.edit', compact('property', 'localities', 'lineentries', 'types', 'categories'));
    }

    public function update(PropertyRegistryRequest $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyRegistryUpdate, PropertyRegistry::class);
        $validated = $request->validated();
        $acquisitionDate = Carbon::parse($validated['AcquisitionDate']);
        $propertyType    = PropertyType::findOrFail($validated['PropertyType']);
        $category        = CategoryMaster::findOrFail($validated['Category']);
        $townCity        = Locality::findOrFail($validated['TownCity']);

        DB::beginTransaction();

        try {
            $property = PropertyRegistry::findOrFail($id);

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
                $validated['IsActive'] ?? $property->IsActive
            );

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
                    $validated['IsActive'],
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
         $this->authorize(PermissionEnum::PropertyRegistryDelete, PropertyRegistry::class);
        try {
            $property = PropertyRegistry::findOrFail($id);


            if ($property->getBlockByProperty()->exists()) {
                return redirect()->back()
                ->withErrors(['error' => 'This Property is in use and cannot be deleted.']);
            }

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
