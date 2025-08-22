<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Core\CodeDetail;
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
        $this->authorize(PermissionEnum::PropertyMaintenanceRequestUpdate, PropertyMaintenanceRequest::class);
        $properties = PropertyRegistry::all();
        $issuetypes = CodeDetail::where('CodeID', 'IssueType')->get();
        $priorities = CodeDetail::where('CodeID', 'PriorityLevel')->get();
        return view('property.maintenanceandissues.maintenancerequest.create', compact('properties','issuetypes','priorities'));
    }
        public function show($Id){
        $this->authorize(PermissionEnum::PropertyMaintenanceRequestView, PropertyMaintenanceRequest::class);
        $maintenancerequest = PropertyMaintenanceRequest::with('issueType','priority')->get()->find($Id);
        return view('property.maintenanceandissues.maintenancerequest.show',compact('maintenancerequest'));
    }
    public function getBlockByProperty($propertyId)
    {
        $blocks = PropertyBlock::where('PropertyID', $propertyId)->get();
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
        $this->authorize(PermissionEnum::PropertyMaintenanceRequestCreate, PropertyMaintenanceRequest::class);
        //dd($request->all());
        $validated = $request->validated();

        $Property = PropertyRegistry::findOrFail($validated['Property']);
        $Block    = PropertyBlock::findOrFail($validated['Block']);
        $Floor    = PropertyFloor::findOrFail($validated['Floor']);
        $Unit     = PropertyUnit::findOrFail($validated['Unit']);
        $IssueType = CodeDetail::findOrFail($validated['IssueType']);
        $Priority  = CodeDetail::findOrFail($validated['Priority']);
        $document = $request->file('Document');

        $maintenancerequest = PropertyMaintenanceService::create(
                    $Property,
                    $Block,
                    $Floor,
                    $Unit,
                    $validated['ReportedBy']?? '--',
                    $IssueType,
                    $Priority,
                    $validated['IssueDescription']?? '--',
            Auth::user(),
                    $document
        );

        return redirect()->route('maintenancerequest.index')->with('success', 'Maintenance request created successfully');

    }
    public function edit($Id)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceRequestUpdate, PropertyMaintenanceRequest::class);
        $maintenancerequest = PropertyMaintenanceRequest::findOrFail($Id);
        $properties = PropertyRegistry::all();
        $issuetypes = CodeDetail::where('CodeID', 'IssueType')->get();
        $priorities = CodeDetail::where('CodeID', 'PriorityLevel')->get();
        return view('property.maintenanceandissues.maintenancerequest.edit', compact('maintenancerequest', 'properties','issuetypes','priorities'));
    }

    public function update(MaintenanceRequest $request, $Id)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceRequestUpdate, PropertyMaintenanceRequest::class);
        // Validate the request data
        $validated = $request->validated();
        DB::beginTransaction();

        try {

            $maintenancerequest = PropertyMaintenanceRequest::findOrFail($Id);
            $Property = PropertyRegistry::findOrFail($validated['Property']);
            $Block    = PropertyBlock::findOrFail($validated['Block']);
            $Floor    = PropertyFloor::findOrFail($validated['Floor']);
            $Unit     = PropertyUnit::findOrFail($validated['Unit']);
            $IssueType = CodeDetail::findOrFail($validated['IssueType']);
            $Priority  = CodeDetail::findOrFail($validated['Priority']);
            $document = $request->file('Document');
            $maintenance = PropertyMaintenanceService::update(
                $maintenancerequest,
                $Property,
                $Block,
                $Floor,
                $Unit,
                $validated['ReportedBy'],
                $IssueType,
                $Priority,
                $validated['IssueDescription'],
                Auth::user(),
                $document
            );

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

    public function destroy($Id)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceRequestDelete, PropertyMaintenanceRequest::class);
        try {
            $maintenancerequest = PropertyMaintenanceRequest::findOrFail($Id);
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
