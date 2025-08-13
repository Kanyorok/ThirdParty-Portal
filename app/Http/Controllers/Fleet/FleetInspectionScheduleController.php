<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fleet\FleetInspectionSchedule;
use App\Models\Fleet\FleetVehicle;
use Illuminate\Support\Facades\Auth;

class FleetInspectionScheduleController extends Controller
{
    public function index()
    {
        $schedules = FleetInspectionSchedule::with('vehicle')->orderByDesc('InspectionDate')->get();
        return view('fleet.compliance.inspection_schedule.index', compact('schedules'));
    }

    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        return view('fleet.compliance.inspection_schedule.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'InspectionType' => 'required|string|max:100',
            'InspectionDate' => 'required|date',
            'DueDate' => 'nullable|date|after_or_equal:InspectionDate',
            'Status' => 'required|string|max:20',
            'Inspector' => 'nullable|string|max:100',
            'Remarks' => 'nullable|string|max:1000',
        ]);

        FleetInspectionSchedule::create([
            'VehicleID' => $request->VehicleID,
            'InspectionType' => $request->InspectionType,
            'InspectionDate' => $request->InspectionDate,
            'DueDate' => $request->DueDate,
            'Status' => $request->Status,
            'Inspector' => $request->Inspector,
            'Remarks' => $request->Remarks,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('fleet.inspection_schedule.index')->with('success', 'Inspection schedule created successfully.');
    }
}
