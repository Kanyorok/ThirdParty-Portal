<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyFloorRequest;
use App\Services\Property\PropertyRegistry\PropertyFloorService;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyBlock;

class PropertyFloorController extends Controller
{
    protected $service;

    public function __construct(PropertyFloorService $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        $floors = PropertyFloor::all();
        //dd($properties);
        return view('property.propertyregistry.structuralmapping.addfloor.index', compact('floors'));
    }
    public function create(){
        $properties = PropertyRegistry::with('block')->get();
        return view('property.propertyregistry.structuralmapping.addfloor.create', compact('properties'));
    }

    public function show($id)
    {
        $floor = PropertyFloor::find($id);
        return view('property.propertyregistry.structuralmapping.addfloor.show', compact('floor'));
    }

    public function store(PropertyFloorRequest $request)
    {
        $this->service->create($request->validated());
        return redirect()->route('addfloor.index')->with('success', 'property floor created successfully');
    }

    public function getBlockByProperty($propertyId)
    {
        $blocks = PropertyBlock::where('PropertyID', $propertyId)->get();
        return response()->json($blocks);
    }
}
