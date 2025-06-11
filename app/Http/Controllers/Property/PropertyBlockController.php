<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyBlockRequest;
use App\Services\Property\PropertyRegistry\PropertyBlockService;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyRegistry;

class PropertyBlockController extends Controller
{
    //
    public function index()
    {
         $blocks = PropertyBlock::with('property')->get();
        return view('property.propertyregistry.structuralmapping.addblock.index',compact('blocks'));
    }
    public function create(){
        $properties = PropertyRegistry::all();
        return view('property.propertyregistry.structuralmapping.addblock.create', compact('properties'));
    }
    public function show($id){
        $block = PropertyBlock::find($id);
        return view('property.propertyregistry.structuralmapping.addblock.show',compact('block'));
    }
    public function store(PropertyBlockRequest $request)
    {
        
        $validated = $request->validated();

        $propertyregistry = PropertyRegistry::findOrFail($validated['PropertyID']);

        $propertyblock = PropertyBlockService::create(
            $propertyregistry,
            $validated['BlockName'] ?? '--',
            $validated['Description'] ?? '--',
            auth()->user()
        );

           return redirect()->route('addblock.index')->with('success','property block created successfully');
    }
}
          