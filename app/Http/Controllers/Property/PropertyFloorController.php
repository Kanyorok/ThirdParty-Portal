<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;  
use App\Models\PropertyManagement\PropertyBlock;

class PropertyFloorController extends Controller
{
    //
    public function index()
    {
         $floors = PropertyFloor::all();
        //dd($properties);
        return view('property.propertyregistry.structuralmapping.addfloor.index', compact('floors'));
    }
    public function create(){
        $blocks = PropertyBlock::all();
        $properties = PropertyRegistry::all();
        return view('property.propertyregistry.structuralmapping.addfloor.create', compact('blocks', 'properties'));
    }
    public function show($id){
        $floor = PropertyFloor::find($id);
        return view('property.propertyregistry.structuralmapping.addfloor.show',compact('floor'));
    }
     public function store(Request $request)
    {
        //dd($request->all());

        $request->validate([
            'PropertyID'=>'required|string|max:50',
            'BlockID'=>'required|string|max:50',
            'FloorLabel'=>'required|string|max:50',
            'FloorNotes'=>'required|string|max:100',
        ]);

         $floor = PropertyFloor::create([
            'PropertyID'=> $request->PropertyID,
            'BlockID'=> $request->BlockID,
            'FloorLabel'=> $request->FloorLabel,
            'FloorNotes'=> $request->FloorNotes,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
    
           return redirect()->route('addfloor.index')->with('success','property floor created successfully');
           
    }
}