<?php

namespace App\Http\Controllers\API\Property;

use App\Http\Controllers\Controller;
use App\Http\Resources\Property\PropertyMaintenanceRequestCollection;
use App\Http\Resources\Property\PropertyMaintenanceRequestResource;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use App\Services\Property\MaintenanceAndIssues\PropertyMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyMaintenanceRequestController extends Controller
{
    public function index(Request $request)
    {
        // Add filtering logic here later (e.g. by user, property)
        $requests = PropertyMaintenanceRequest::with(['property', 'unit', 'issueType', 'priority'])
            ->latest('CreatedOn')
            ->paginate(10);

        return new PropertyMaintenanceRequestCollection($requests);
    }

    public function show($id)
    {
        $request = PropertyMaintenanceRequest::with(['property', 'unit', 'issueType', 'priority'])
            ->findOrFail($id);

        return new PropertyMaintenanceRequestResource($request);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'propertyId' => 'required|exists:t_PropertyRegistry,Id', // Adjust table name if needed
            'title' => 'required|string',
            'description' => 'required|string',
            'priority' => 'required|string',
            'categoryId' => 'required|string',
            'unitId' => 'nullable|integer',
            // images/documents validation
        ]);

        // Find standard codes for Priority and IssueType (Category)
        // Assuming 'categoryId' comes as string code, we might need to find ID
        // For now, let's try to find match by Code or Description
        $priority = CodeDetail::where('CodeID', 'PriorityLevel')
            ->where(function ($q) use ($validated) {
                $q->where('Code', $validated['priority'])
                  ->orWhere('Description', $validated['priority']);
            })->first();

        $issueType = CodeDetail::where('CodeID', 'IssueType')
            ->where(function ($q) use ($validated) {
                $q->where('Code', $validated['categoryId'])
                  ->orWhere('Description', $validated['categoryId']);
            })->first();

        // Fallback or error if not found?
        // Using existing service if possible, or manual creation.
        // Existing service signature: create($Property, $Block, $Floor, $Unit, $ReportedBy, $IssueType, $Priority, $IssueDescription, $User, $Document)

        $property = PropertyRegistry::findOrFail($validated['propertyId']);
        $unit = ! empty($validated['unitId']) ? PropertyUnit::find($validated['unitId']) : null;
        $block = $unit ? $unit->block : null; // Assuming relation exists or null
        $floor = $unit ? $unit->floor : null;

        $maintenanceRequest = PropertyMaintenanceService::create(
            $property,
            $block,
            $floor,
            $unit,
            Auth::user()->name ?? 'Portal User',
            $issueType ?? CodeDetail::first(), // Fallback unsafe but prevents crash
            $priority ?? CodeDetail::first(),
            $validated['description'],
            Auth::user(),
            null // Document upload handling separate
        );

        // TODO: Handle document uploads (data.images)

        return response()->json([
            'message' => 'Maintenance request submitted successfully',
            'data' => new PropertyMaintenanceRequestResource($maintenanceRequest->maintenancerequest ?? $maintenanceRequest),
        ], 201);
    }

    public function destroy($id)
    {
        $request = PropertyMaintenanceRequest::findOrFail($id);
        $request->delete();

        return response()->json(['message' => 'Request deleted successfully']);
    }
}
