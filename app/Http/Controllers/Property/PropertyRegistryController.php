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
use Illuminate\Http\JsonResponse;

class PropertyRegistryController extends Controller
{
    public function index()
    {

        $properties = PropertyRegistry::with('type')->orderBy('Id', 'desc')->get();

        if (request()->expectsJson()) {
            return response()->json($properties);
        }

        return view('property.propertyregistry.registry.index', compact('properties'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyRegistryCreate, PropertyRegistry::class);

        $lineentries = CategoryMaster::with('propertytypes')
            ->where('Type', 'PropertyCategory')->get();
        $countries = Country::all();

        return view('property.propertyregistry.registry.create', compact('lineentries', 'countries'));
    }

    public function store(PropertyRegistryRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyRegistryCreate, PropertyRegistry::class);

        $validated = $request->validated();

        $acquisitionDate = Carbon::parse($validated['AcquisitionDate']);
        $propertyType    = PropertyType::findOrFail($validated['PropertyType']);
        $category        = CategoryMaster::findOrFail($validated['Category']);
        $location        = Locality::findOrFail($validated['LocationId']);
        $country         = Country::findOrFail($validated['CountryId']);

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
            $validated['PropertyDescription'] ?? '',
            $request->user(),
            $firstFile
        );

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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Property registry created successfully',
                'data'    => $service->propertyRegistry,
            ], 201);
        }

        return redirect()
            ->route('PropertyRegistry.index')
            ->with('success', 'Property registry created successfully');
    }


    public function show($id)
    {
        $this->authorize(PermissionEnum::PropertyRegistryView, PropertyRegistry::class);

        $property = PropertyRegistry::with([
            'getBlockByProperty.floor.units'
        ])->findOrFail($id);

        if (request()->expectsJson()) {
            return response()->json($property);
        }

        return view('property.propertyregistry.registry.show', compact('property'));
    }


    public function edit($id)
    {
        $this->authorize(PermissionEnum::PropertyRegistryUpdate, PropertyRegistry::class);

        $property    = PropertyRegistry::findOrFail($id);
        $types       = PropertyType::where('PropertyCategoryId', $property->Category)->get();
        $categories  = CategoryMaster::all();
        $lineentries = CategoryMaster::with('propertytypes')->get();
        $countries   = Country::all();
        $localities  = Locality::where('CountryId', $property->CountryId)->get();

        return view(
            'property.propertyregistry.registry.edit',
            compact('property', 'countries', 'localities', 'lineentries', 'types', 'categories')
        );
    }


    public function update(PropertyRegistryRequest $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyRegistryUpdate, PropertyRegistry::class);

        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $property = PropertyRegistry::findOrFail($id);

            $acquisitionDate = Carbon::parse($validated['AcquisitionDate']);
            $propertyType    = PropertyType::findOrFail($validated['PropertyType']);
            $category        = CategoryMaster::findOrFail($validated['Category']);
            $location        = Locality::findOrFail($validated['LocationId']);
            $country         = Country::findOrFail($validated['CountryId']);

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

            // Upload new files
            foreach ($request->file('file', []) as $uploadedFile) {
                $property->newDocument(
                    ModulesEnum::Property,
                    $uploadedFile,
                    [PermissionEnum::PropertyRegistryView->value],
                    $request->user()
                );
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Property updated successfully',
                    'data'    => $property->refresh(),
                ]);
            }

            return redirect()
                ->route('PropertyRegistry.index')
                ->with('success', 'Property updated successfully');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to update property: ' . $th->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to update property',
                    'error'   => $th->getMessage(),
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Failed to update property'])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::PropertyRegistryDelete, PropertyRegistry::class);

        try {
            $property = PropertyRegistry::findOrFail($id);

            if ($property->getBlockByProperty()->exists()) {
                $errorMsg = 'This Property is in use and cannot be deleted.';

                return request()->expectsJson()
                    ? response()->json(['message' => $errorMsg], 409)
                    : back()->withErrors(['error' => $errorMsg]);
            }

            $property->delete();

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Property Deleted Successfully!'], 204);
            }

            return redirect()->route('PropertyRegistry.index')
                ->with('success', 'Property Deleted Successfully!');

        } catch (\Throwable $th) {
            Log::error('Error deleting property: ' . $th->getMessage());

            return request()->expectsJson()
                ? response()->json(['message' => 'Failed to delete Property'], 500)
                : back()->withErrors(['error' => 'Failed to delete Property'])->withInput();
        }
    }


    public function getTypesByCategory($categoryId)
    {
        return response()->json(
            PropertyType::where('PropertyCategoryId', $categoryId)->get()
        );
    }

    public function getLocalityByCountry($countryId)
    {
        return response()->json(
            Locality::where('CountryId', $countryId)->get()
        );
    }
}
