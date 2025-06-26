<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
public function edit($id)
    {
        //Check if user has permission to edit tender categories
       // $this->authorize(PermissionEnum::PropertyTypeUpdate, PropertyType::class);
        $property = PropertyRegistry::findOrFail($id);
        $types = PropertyType::all();
        $categories = CategoryMaster::all();
        $lineentries = CategoryMaster::with('propertytypes')->get();
        $localities = Locality::all();

        return view('property.propertyregistry.registry.edit',compact('property','localities','lineentries','types','categories'));
    }
     public function update(Request $request, $id){ 
       // $this->authorize(PermissionEnum::PropertyTypeUpdate , PropertyType::class);
        $validated=$request->validate([
        'PropertyName'  => 'required|string|max:50',
        'PropertyType' => 'required|exists:t_PropertyType,Id',
        'Category' => 'required|exists:t_CategoryMaster,Id',
        'TownCity' => 'required|exists:t_Localities,Id',
        'PropertyDescription'  => 'required|string|max:100',
        
    ]);
 
    DB::beginTransaction();
 
    try{
        $property = PropertyRegistry::findOrFail($id);

        $property->update([
            'PropertyName'  => $validated['PropertyName'],
            'Category' => $validated['Category'],
            'PropertyType' => $validated['PropertyType'],
            'TownCity' => $validated['TownCity'],
            'PropertyDescription'  => $validated['PropertyDescription'],  
            'ModifiedBy' => Auth::Id(),
        ]);
 
        DB::commit();
        activity()
                ->performedOn($property)
                ->causedBy(Auth::user())
                ->withProperties(['action'=>'update'])
                ->log('Updated Propeerty Registry');

                return redirect()->route('PropertyRegistry.index')->with('success' , 'property updated successfully');
            }catch(\Throwable $th) {
                DB::rollBack();
                Log::error('Failed to Update type:' . $th->getMessage());

                return back()->withErrors(['error'=>'Failed to update type'])->withInput();
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
