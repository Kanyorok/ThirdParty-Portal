<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyRegistry;

class PropertyBlockController extends Controller
{
    //
    public function index()
    {
        $blocks = PropertyBlock::all();
        //dd($properties);
        return view('property.propertyregistry.structuralmapping.addblock.index', compact('blocks'));
    }
    public function create(){
        $properties = PropertyRegistry::all();
        return view('property.propertyregistry.structuralmapping.addblock.create', compact('properties'));
    }

    public function show($id)
    {
        $block = PropertyBlock::find($id);
        return view('property.propertyregistry.structuralmapping.addblock.show', compact('block'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'PropertyID' => 'required|string|max:50',
            'BlockName' => 'required|string|max:50',
            'Description' => 'required|string|max:100',
        ]);

        $block = PropertyBlock::create([
            'PropertyID' => $request->PropertyID,
            'BlockName' => $request->BlockName,
            'Description' => $request->Description,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        return redirect()->route('addblock.index')->with('success', 'property block created successfully');
    }
}
