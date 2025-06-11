<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyUnit;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyBlock;


class PropertyUnitController extends Controller
{
    //
    public function index()
    {
        $units = PropertyUnit::all();
        return view('property.propertyregistry.structuralmapping.addunit.index', compact('units'));
    }

    public function create(){
        $properties = PropertyRegistry::all();
        $blocks = PropertyBlock::all();
        $floors = PropertyFloor::all();
        return view('property.propertyregistry.structuralmapping.addunit.create', compact('floors', 'properties', 'blocks'));
    }
    public function show($id){
        $unit = PropertyUnit::find($id);
        return view('property.propertyregistry.structuralmapping.addunit.show',compact('unit'));
    }
     public function store(Request $request)
    {
       //dd($request->all());
        $request->validate([
            'PropertyID'=>'required|string|max:50',
            'BlockID'=>'required|string|max:50',
            'FloorID'=>'required|string|max:50',
            'UnitCode'=>'required|string|max:50',  
            'UnitSize'=>'required|integer',
            'IsRentable'=>'required|string|max:50',
            'CurrentStatus'=>'required|string|max:50',
            'Remarks'=>'required|string|max:50',
        ]);
            
         $unit = PropertyUnit::create([
            'PropertyID'=> $request->PropertyID,
            'BlockID'=> $request->BlockID,
            'FloorID'=> $request->FloorID,
            'UnitCode'=> $request->UnitCode,
            'UnitSize'=> $request->UnitSize,
            'IsRentable'=> $request->IsRentable,
            'CurrentStatus'=> $request->CurrentStatus,
            'Remarks'=> $request->Remarks,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
           return redirect()->route('addunit.index')->with('success','property unit created successfully');
    }
}

