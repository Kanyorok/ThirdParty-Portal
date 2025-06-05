<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $types = PropertyType::all();
        $categories = PropertyCategory::all();
        return view('property.propertyregistry.registry.create', compact('types', 'categories'));
    }

    public function show($id){
        $property = PropertyRegistry::find($id);
        return view('property.propertyregistry.registry.show',compact('property'));
    }
    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'PropertyName'=>'required',
            'PropertyCode'=>'required',
            'PropertyType'=>'required',
            'Category'=>'required',
            'Owner'=>'required',
            'AcquisitionDate'=>'required|date',
            'TownCity'=>'required',
            'AreaLocality'=>'required',
            'GPSCoordinates'=>'required',
            'PropertyDescription'=>'required',
           
        ]);
        $property = PropertyRegistry::create([
            'PropertyName'=> $request->PropertyName,
            'PropertyCode'=> $request->PropertyCode,
            'PropertyType'=> $request->PropertyType,
            'Category'=> $request->Category,
            'Owner'=> $request->Owner,
            'AcquisitionDate'=> $request->AcquisitionDate,
            'Country'=> $request->Country,
            'TownCity'=> $request->TownCity,
            'AreaLocality'=> $request->AreaLocality,
            'GPSCoordinates'=> $request->GPSCoordinates,
            'PropertyDescription'=> $request->PropertyDescription,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        //dd($property);
         return redirect()->route('PropertyRegistry.index')->with('success','property registry created successfully');
    }
}
