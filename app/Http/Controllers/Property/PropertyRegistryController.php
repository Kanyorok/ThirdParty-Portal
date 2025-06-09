<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Core\Locality;
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

    public function create(){
        $lineentries = PropertyCategory::with('type','property',)->get();
        return view('property.propertyregistry.registry.create', compact('lineentries'));
    }

    public function show($id){
        $property = PropertyRegistry::find($id);
        return view('property.propertyregistry.registry.show',compact('property'));
    }
    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'PropertyName'=>'required|string|max:100',
            'PropertyCode'=>'required|string|max:50',
            'PropertyType'=>'required|exists:t_PropertyType,Id',
            'Category'=>'required|exists:t_PropertyCategory,Id',
            'Owner'=>'required|string|max:100',
            'AcquisitionDate'=>'required|date',
            'Country'=>'required|string|max:100',
            'TownCity'=>'required|exists:t_Localities,Id',
            'AreaLocality'=>'required|string|max:100',
            'PropertyDescription'=>'required|string|max:1000',
        ]);
        PropertyRegistry::create($request->only
        ('PropertyName', 'PropertyCode', 'PropertyType', 
        'Category', 'Owner', 'AcquisitionDate','Country', 
        'TownCity', 'AreaLocality', 'PropertyDescription')+[
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
   return redirect()->route('PropertyRegistry.index')->with('success','property registry created successfully');
    }
}
