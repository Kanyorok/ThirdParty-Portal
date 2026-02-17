<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetVehicleRequestsRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetVehicleRequest;
use App\Models\HR\Employee;
use App\Models\HRM\Department;
use App\Services\FleetManagement\FleetVehicleRequestService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetVehicleRequestController extends Controller
{
    protected FleetVehicleRequestService $service;

    public function __construct(FleetVehicleRequestService $service)
    {
        $this->service = $service;
    }

    // List all vehicle requests
    public function index()
    {
        $this->authorize('viewAny', FleetVehicleRequest::class);
        $requests = FleetVehicleRequest::with(['requester', 'approver', 'trip', 'status', 'statusDetail'])
            ->orderByDesc('RequestDate')
            ->get();

        return view('fleet.vehicle_requests.index', compact('requests'));
    }

    // Show create form
    public function create()
    {
        $this->authorize('create', FleetVehicleRequest::class);
        $branchId = Auth::user()->employee?->BranchId;
        $vehicleType = CodeDetail::where('CodeID', 'VehicleType')
            ->orderBy('Value')
            ->get();
        $departments = Department::all();
        $trips = FleetTripLog::whereNull('EndTime')
            ->orderByDesc('CreatedOn')
            ->get();

        $requester = Employee::where('BranchId', $branchId)
            ->select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
            ->pluck('name', 'Id');


        return view('fleet.vehicle_requests.create', compact('vehicleType', 'departments', 'trips', 'requester'));
    }

    // Show approve form
    public function approveForm($id)
    {
        $this->authorize('view', FleetVehicleRequest::class);
        $vehicleRequest = FleetVehicleRequest::with(['requester', 'approver', 'status', 'vehicle', 'statusDetail', 'department', 'trip'])
            ->findOrFail($id);


        return view('fleet.vehicle_requests.approval', compact('vehicleRequest'));
    }


    // Store new vehicle request
    // In App\Http\Controllers\Fleet\FleetVehicleRequestController.php

    public function store(FleetVehicleRequestsRequest $request)
    {
        $this->authorize('create', FleetVehicleRequest::class);

        try {
            $this->service->create($request->validated());

            return redirect()
                ->route('fleet.vehicle_requests.index')
                ->with('success', 'Vehicle request submitted successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Update an existing request
    public function update(FleetVehicleRequestsRequest $request, FleetVehicleRequest $vehicleRequest)
    {
        $this->authorize('update', FleetVehicleRequest::class);

        try {
            $this->service->update($vehicleRequest, $request->validated());

            return redirect()
                ->route('fleet.vehicle_requests.index')
                ->with('success', 'Vehicle request updated successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approve(Request $request, $id)
    {
        $this->authorize('approve', FleetVehicleRequest::class);
        $request->validate([
            'ApprovedOn' => 'required|date',
            'ApprovedBy' => 'required|integer',
            'Status' => 'required|in:Approved,Rejected',
            'RejectionReason' => 'nullable|string|max:255',
        ]);

        try {
            if ($request->Status === 'Approved') {
                $this->service->approve($id, $request->ApprovedOn, $request->ApprovedBy);
                $msg = 'Vehicle request approved.';
            } else {
                $this->service->reject($id, $request->RejectionReason, $request->ApprovedBy);
                $msg = 'Vehicle request rejected.';
            }

            return redirect()
                ->route('fleet.vehicle_requests.index')
                ->with('success', $msg);
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Delete a request
    public function destroy(FleetVehicleRequest $vehicleRequest)
    {
        $this->authorize('destroy', FleetVehicleRequest::class);

        try {
            $this->service->delete($vehicleRequest);

            return redirect()
                ->route('fleet.vehicle_requests.index')
                ->with('success', 'Vehicle request deleted.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
