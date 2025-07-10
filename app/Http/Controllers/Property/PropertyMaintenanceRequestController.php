<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use App\Http\Requests\Property\MaintenanceAndIssues\MaintenanceRequest;
use App\Services\Property\MaintenanceAndIssues\PropertyMaintenanceService;
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
        public function show($id){
        $maintenancerequest = PropertyMaintenanceRequest::find($id);
        return view('property.maintenanceandissues.maintenancerequest.show',compact('maintenancerequest'));
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

    public function store(MaintenanceRequest $request)
    {
        //dd($request->all());
        $validated = $request->validated();
     
        $Property = PropertyRegistry::findOrFail($validated['Property']);
        $Block    = PropertyBlock::findOrFail($validated['Block']);
        $Floor    = PropertyFloor::findOrFail($validated['Floor']);
        $Unit     = PropertyUnit::findOrFail($validated['Unit']);
        $user     = Auth::user();
        
        $maintenancerequest = PropertyMaintenanceService::create(
                    $Property,
                    $Block,
                    $Floor,
                    $Unit,
                    $validated['ReportedBy']?? '--',
                    $validated['IssueType']?? '--',
                    $validated['Priority']?? '--',
                    $validated['IssueDescription']?? '--',
                    auth()->user()
        );
        //dd('validation passed');
        return redirect()->route('maintenancerequest.index')->with('success', 'Maintenance request created successfully');

    }
     public function edit($id)
{
    // Optional: add authorization logic here if needed
    $maintenancerequest = PropertyMaintenanceRequest::findOrFail($id);
    $properties = PropertyRegistry::all();

    return view('property.maintenanceandissues.maintenancerequest.edit', compact('maintenancerequest', 'properties'));
}

public function update(Request $request, $id)
{
   $validated = $request->validate([
            'Property' => 'required|exists:t_PropertyRegistry,id',
            'Block' => 'required|exists:t_PropertyBlock,id',
            'Floor' => 'required|exists:t_PropertyFloor,id',
            'Unit' => 'required|exists:t_PropertyUnit,id',
            'ReportedBy' => 'required|string|max:50',
            'IssueType' => 'required|string|max:50',
            'Priority' => 'required|string|max:50',
            'IssueDescription' => 'required|string|max:255',
        ]);
    DB::beginTransaction();

    try {
        $maintenancerequest = PropertyMaintenanceRequest::findOrFail($id);

        $maintenancerequest->update([
            'Property'         => $validated['Property'],
            'Block'            => $validated['Block'],
            'Floor'            => $validated['Floor'],
            'Unit'             => $validated['Unit'],
            'ReportedBy'       => $validated['ReportedBy'],
            'IssueType'        => $validated['IssueType'],
            'Priority'         => $validated['Priority'],
            'IssueDescription' => $validated['IssueDescription'],
            'ModifiedBy'       => Auth::id(),
        ]);

        DB::commit();

        activity()
            ->performedOn($maintenancerequest)
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'update'])
            ->log('Updated Maintenance Request');

        return redirect()->route('maintenancerequest.index')
                         ->with('success', 'Maintenance request updated successfully');
    } catch (\Throwable $th) {
        DB::rollBack();
        Log::error('Failed to update maintenance request: ' . $th->getMessage());

        return back()->withErrors(['error' => 'Failed to update maintenance request'])->withInput();
    }
}

public function destroy($id)
{
    try {
        $maintenancerequest = PropertyMaintenanceRequest::findOrFail($id);
        $maintenancerequest->delete();

        return redirect()->route('maintenancerequest.index')
                         ->with('success', 'Maintenance request deleted successfully!');
    } catch (\Throwable $th) {
        Log::error('Error deleting maintenance request: ' . $th->getMessage());

        return redirect()->back()
                         ->withErrors(['error' => 'Failed to delete maintenance request. Please try again.'])
                         ->withInput();
    }
  }
}