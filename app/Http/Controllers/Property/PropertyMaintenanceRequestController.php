<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyUnit;

class PropertyMaintenanceRequestController extends Controller
{
    //
    public function index()
    {
        $maintenancerequests = PropertyMaintenanceRequest::all();
        return view('property.maintenanceandissues.maintenancerequest.index', compact('maintenancerequests'));
    }

    public function create(){
        $units = PropertyUnit::all();
        $blocks = PropertyBlock::all();
        $floors = PropertyFloor::all();
        $properties = PropertyRegistry::all();
        return view('property.maintenanceandissues.maintenancerequest.create', compact('properties', 'floors', 'blocks', 'units'));
    }
    public function getBlockByProperty($propertyId)
    {
        $blocks = PropertyBlock::where('PropertyID', $propertyId)->get();
        //dd($blocks); // check if it's returning correctly
        return response()->json($blocks);
    }


    public function getFloorByBlock($blockId)
    {
        $floors = PropertyFloor::where('BlockID', $blockId)->get();
        return response()->json($floors);
    }
    public function getUnitByFloor($floorId)
    {
        $units = PropertyUnit::where('FloorId', $floorId)->get();
        return response()->json($units);
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'Property' => 'required|string|max:50',
            'Block' => 'required|string|max:50',
            'Floor' => 'required|string|max:50',
            'Unit' => 'required|string|max:50',
            'ReportedBy' => 'required|string|max:50',
            'IssueType' => 'required|string|max:50',
            'Priority' => 'required|string|max:50',
            'IssueDescription' => 'required|string|max:255',
        ]);
        //dd('validation passed');
        $maintenancerequest = PropertyMaintenanceRequest::create([
            'Property' => $request->Property,
            'Block' => $request->Block,
            'Floor' => $request->Floor,
            'Unit' => $request->Unit,
            'ReportedBy' => $request->ReportedBy,
            'IssueType' => $request->IssueType,
            'Priority' => $request->Priority,
            'IssueDescription' => $request->IssueDescription,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        //dd('validation passed');
        return redirect()->route('maintenancerequest.index')->with('success', 'Maintenance request created successfully');

    }

}
