<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function edit($id)
    {
        //Check if user has permission to edit tender categories
       // $this->authorize(PermissionEnum::PropertyTypeUpdate, PropertyType::class);
        $type = PropertyType::findOrFail($id);
        $categories = CategoryMaster::all();

        return view('property.propertyregistry.propertytype.edit',compact('type','categories'));
    }
     public function update(Request $request, $id){ 
       // $this->authorize(PermissionEnum::PropertyTypeUpdate , PropertyType::class);
        $validated=$request->validate([
        'PropertyTypeName'  => 'required|string|max:50',
        'PropertyCategoryId' => 'required|exists:t_CategoryMaster,Id',
        'Description'  => 'required|string|max:100',
        
    ]);
 
    DB::beginTransaction();
 
    try{
        $type = PropertyType::findOrFail($id);

        $type->update([
            'PropertyTypeName'  => $validated['PropertyTypeName'],
            'PropertyCategoryId' => $validated['PropertyCategoryId'],
            'Description'  => $validated['Description'],           
            'CreatedBy' =>Auth::Id(),
            'ModifiedBy' => Auth::Id(),
        ]);
 
        DB::commit();
        activity()
                ->performedOn($type)
                ->causedBy(Auth::user())
                ->withProperties(['action'=>'update'])
                ->log('Updated Type');

                return redirect()->route('propertytype.index')->with('success' , 'Type updated successfully');
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
            $type = PropertyType::findOrFail($id);
            $type->delete();

            return redirect()->route('propertytype.index')
                ->with('success', 'Property Type Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting property type: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Property Type. Please try again.'])
                ->withInput();
        }
    }   

}
