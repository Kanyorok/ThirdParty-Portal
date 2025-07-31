<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Fleet\FleetVehicleRequest;
use App\Models\User;

class FleetVehicleRequestController extends Controller
{
    // List all vehicle requests
    public function index()
    {
        $requests = FleetVehicleRequest::with(['requester', 'approver'])
            ->orderByDesc('RequestDate')
            ->get();

        return view('fleet.vehicle_requests.index', compact('requests'));
    }

    // Show form to request a new vehicle
    public function create()
    {
        return view('fleet.vehicle_requests.create');
    }

    // Store new vehicle request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'TripDate' => 'required|date|after_or_equal:today',
            'Purpose' => 'required|string|max:255',
            'FromLocation' => 'required|string|max:255',
            'ToLocation' => 'required|string|max:255',
            'PassengerCount' => 'nullable|integer|min:1',
            'PreferredVehicleType' => 'nullable|string|max:50',
        ]);

        FleetVehicleRequest::create([
            ...$validated,
            'RequestedBy' => Auth::id(),
            'RequestDate' => now()->toDateString(),
            'Department' => Auth::user()->Department ?? null,
            'CreatedOn' => now(),
        ]);

        return redirect()->route('fleet.vehicle_requests.index')->with('success', 'Vehicle request submitted.');
    }

    // Approve or reject a request
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'Action' => 'required|in:approve,reject',
            'RejectionReason' => 'nullable|string|max:255',
        ]);

        $vehicleRequest = FleetVehicleRequest::findOrFail($id);
        if ($vehicleRequest->Status !== 'Pending') {
            return back()->with('error', 'Request has already been processed.');
        }

        if ($validated['Action'] === 'approve') {
            $vehicleRequest->update([
                'Status' => 'Approved',
                'ApprovedBy' => Auth::id(),
                'ApprovedOn' => now(),
                'ModifiedOn' => now(),
            ]);
        } else {
            $vehicleRequest->update([
                'Status' => 'Rejected',
                'RejectionReason' => $validated['RejectionReason'],
                'ApprovedBy' => Auth::id(),
                'ApprovedOn' => now(),
                'ModifiedOn' => now(),
            ]);
        }

        return redirect()->route('fleet.vehicle_requests.index')->with('success', 'Request processed successfully.');
    }
}
