<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
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
        $properties = PropertyRegistry::all()->where('IsActive', True);
        $issuetypes = CodeDetail::where('CodeID', 'IssueType')->get();
        $priorities = CodeDetail::where('CodeID', 'PriorityLevel')->get();
        return view('property.maintenanceandissues.maintenancerequest.create', compact('properties','issuetypes','priorities'));
    }

    public function show($Id)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceRequestView, PropertyMaintenanceRequest::class);
        $maintenancerequest = PropertyMaintenanceRequest::with('issueType','priority')->get()->find($Id);
        return view('property.maintenanceandissues.maintenancerequest.show',compact('maintenancerequest'));
    }

    public function getBlockByProperty($PropertyId)
    {
        $blocks = PropertyBlock::where('PropertyID', $PropertyId)->get();
        return response()->json($blocks);
    }

    public function getFloorByBlock($BlockId)
    {
        $floors = PropertyFloor::where('BlockID', $BlockId)->get();
        return response()->json($floors);
    }

    public function getUnitByFloor($FloorId)
    {
        $units = PropertyUnit::where('FloorId', $FloorId)->get();
        return response()->json($units);
    }

    public function store(MaintenanceRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceRequestCreate, PropertyMaintenanceRequest::class);

        $validated = $request->validated();

        $Property = PropertyRegistry::findOrFail($validated['Property']);
        $Block    = !empty($validated['Block']) ? PropertyBlock::find($validated['Block']) : null;
        $Floor    = !empty($validated['Floor']) ? PropertyFloor::find($validated['Floor']) : null;
        $Unit     = !empty($validated['Unit']) ? PropertyUnit::find($validated['Unit']) : null;
        $IssueType = CodeDetail::findOrFail($validated['IssueType']);
        $Priority  = CodeDetail::findOrFail($validated['Priority']);

        $uploadedFile = $request->file('Document')[0] ?? null;

        $maintenanceRequest = PropertyMaintenanceService::create(
                    $Property,
                    $Block,
                    $Floor,
                    $Unit,
                    $validated['ReportedBy']?? '--',
                    $IssueType,
                    $Priority,
                    $validated['IssueDescription']?? '--',
                    Auth::user(),
                    $uploadedFile
        );

        if ($request->hasFile('Document')) {
            foreach (array_slice($request->file('Document'), 1) as $uploadedFile) {
                $maintenanceRequest->maintenancerequest->newDocument(
                    ModulesEnum::Property,
                    $uploadedFile,
                    [PermissionEnum::PropertyMaintenanceAssignView->value],
                    $request->user()
                );
            }
        }

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

        $validated = $request->validated();
        DB::beginTransaction();

        try {

            $maintenancerequest = PropertyMaintenanceRequest::findOrFail($Id);
            $Property = PropertyRegistry::findOrFail($validated['Property']);
            $Block    = !empty($validated['Block']) ? PropertyBlock::find($validated['Block']) : null;
            $Floor    = !empty($validated['Floor']) ? PropertyFloor::find($validated['Floor']) : null;
            $Unit     = !empty($validated['Unit']) ? PropertyUnit::find($validated['Unit']) : null;
            $IssueType = CodeDetail::findOrFail($validated['IssueType']);
            $Priority  = CodeDetail::findOrFail($validated['Priority']);

            PropertyMaintenanceService::update(
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
            );

            $uploadedFile = $request->file('Document')[0] ?? null;
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
                $uploadedFile
            );

            if ($request->hasFile('Document')) {
                foreach (array_slice($request->file('Document'), 1) as $uploadedFile) {
                    $maintenance->maintenancerequest->newDocument(
                        ModulesEnum::Property,
                        $uploadedFile,
                        [PermissionEnum::PropertyMaintenanceAssignView->value],
                        $request->user()
                    );
                }
        }

            DB::commit();
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

            if ($maintenancerequest->requestId()->exists()) {
                return redirect()->back()
                    ->withErrors(['error' => 'This Maintenance request is in use and cannot be deleted.']);
            }

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
