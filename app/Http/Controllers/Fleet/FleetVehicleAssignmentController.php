<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetVehicleAssignment;
use App\Models\Branch;
use App\Models\User;

class FleetVehicleAssignmentController extends Controller
{
    public function index()
    {
        $assignments = FleetVehicleAssignment::with(['vehicle', 'user', 'branch', 'assignedBy'])
            ->orderByDesc('AssignmentDate')
            ->get();

        return view('fleet.assignments.index', compact('assignments'));
    }

    public function create($id)
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $branches = Branch::all(); // No IsActive filter
        $users = User::all(); // You may filter based on roles or branch

        return view('fleet.assignments.create', compact('vehicles', 'branches', 'users'));
    }

    public function show()
    {

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'AssignedBranchID' => 'nullable|exists:t_Branches,ID',
            'AssignedToUserID' => 'nullable|exists:users,id',
            'AssignmentDate' => 'required|date',
            'Purpose' => 'nullable|string|max:255',
            'Notes' => 'nullable|string|max:500',
        ]);

        FleetVehicleAssignment::create([
            ...$validated,
            'AssignedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        // Optional: update live assignment in FleetVehicles table
        FleetVehicle::where('VehicleID', $validated['VehicleID'])->update([
            'AssignedBranchID' => $validated['AssignedBranchID'],
            'AssignedToUserID' => $validated['AssignedToUserID'],
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle reassigned successfully.');
    }
}
