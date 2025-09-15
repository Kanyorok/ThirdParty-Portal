<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Core\Country;
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
        $countries = Country::all();
        return view('property.propertyregistry.registry.create', compact('lineentries', 'countries'));
    }

    public function getTypesByCategory($categoryId)
    {
        $types = PropertyType::where('PropertyCategoryId', $categoryId)->get();
        return response()->json($types);
    }

    public function getLocalityByCountry($country)
    {
        $localities = Locality::where('CountryId', $country)->get();
        return response()->json($localities);
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
        $propertyType   = PropertyType::findOrFail($validated['PropertyType']);
        $category       = CategoryMaster::findOrFail($validated['Category']);
        $location       = Locality::findOrFail($validated['LocationId']);
        $country        = Country::findOrFail($validated['CountryId']);

        // Take first file (if any) for the initial create
        $firstFile = $request->file('file')[0] ?? null;

        $service = PropertyRegistryService::create(
            $validated['PropertyName'],
            $validated['PropertyCode'],
            $propertyType,
            $category,
            $validated['Owner'],
            $acquisitionDate,
            $country,
            $location,
            $validated['Address'],
            $validated['PropertyDescription'] ?? null,
            $request->user(),
            $firstFile
        );

        // Attach remaining files (if more than one uploaded)
        if ($request->hasFile('file')) {
            foreach (array_slice($request->file('file'), 1) as $uploadedFile) {
                $service->propertyRegistry->newDocument(
                    ModulesEnum::Property,
                    $uploadedFile,
                    [PermissionEnum::PropertyRegistryView->value],
                    $request->user()
                );
            }
        }

        return redirect()
            ->route('PropertyRegistry.index')
            ->with('success', 'Property registry created successfully');
    }


    public function edit($id)
    {
        $this->authorize(PermissionEnum::PropertyRegistryUpdate, PropertyRegistry::class);

        $property = PropertyRegistry::findOrFail($id);

        // Preload only the types for the selected category so the edit dropdown matches the saved category
        $types = PropertyType::where('PropertyCategoryId', $property->Category)->get();

        // Keep both variables if other views use them
        $categories = CategoryMaster::all();
        $lineentries = CategoryMaster::with('propertytypes')->get();

        $countries = Country::all();

        // NOTE: use plural $localities (collection) to match your blade
        $localities = Locality::where('CountryId', $property->CountryId)->get();

        return view(
            'property.propertyregistry.registry.edit',
            compact('property', 'countries', 'localities', 'lineentries', 'types', 'categories')
        );
    }


    public function update(PropertyRegistryRequest $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyRegistryUpdate, PropertyRegistry::class);
        $validated = $request->validated();
        $acquisitionDate = Carbon::parse($validated['AcquisitionDate']);
        $propertyType    = PropertyType::findOrFail($validated['PropertyType']);
        $category        = CategoryMaster::findOrFail($validated['Category']);
        $location        = Locality::findOrFail($validated['LocationId']);
        $country        = Country::findOrFail($validated['CountryId']);

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
                $country,
                $location,
                $validated['Address'],
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
                    $country,
                    $location,
                    $validated['Address'],
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
