<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fleet\FleetInsuranceTracker;
use App\Models\Fleet\FleetVehicle;
use Illuminate\Support\Facades\Auth;

class FleetInsuranceTrackerController extends Controller
{
    public function index()
    {
        $records = FleetInsuranceTracker::with('vehicle')->whereNull('DeletedOn')->get();
        return view('fleet.compliance.insurance_tracker.index', compact('records'));
    }

    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        return view('fleet.compliance.insurance_tracker.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'InsuranceProvider' => 'required|string|max:100',
            'PolicyNumber' => 'required|string|max:100',
            'CoverageStartDate' => 'required|date',
            'CoverageEndDate' => 'required|date|after:CoverageStartDate',
            'PremiumAmount' => 'required|numeric',
            'RenewalReminderDate' => 'nullable|date',
            'Notes' => 'nullable|string',
            'DocumentPath' => 'nullable|string|max:255',
        ]);

        $validated['CreatedBy'] = Auth::id();
        $validated['CreatedOn'] = now();

        FleetInsuranceTracker::create($validated);

        return redirect()->route('fleet.insurance.index')->with('success', 'Insurance record created successfully.');
    }

    public function edit($id)
    {
        $record = FleetInsuranceTracker::findOrFail($id);
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        return view('fleet.compliance.insurance_tracker.edit', compact('record', 'vehicles'));
    }

    public function update(Request $request, $id)
    {
        $record = FleetInsuranceTracker::findOrFail($id);

        $validated = $request->validate([
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'InsuranceProvider' => 'required|string|max:100',
            'PolicyNumber' => 'required|string|max:100',
            'CoverageStartDate' => 'required|date',
            'CoverageEndDate' => 'required|date|after:CoverageStartDate',
            'PremiumAmount' => 'required|numeric',
            'RenewalReminderDate' => 'nullable|date',
            'Notes' => 'nullable|string',
            'DocumentPath' => 'nullable|string|max:255',
        ]);

        $validated['ModifiedBy'] = Auth::id();
        $validated['ModifiedOn'] = now();

        $record->update($validated);

        return redirect()->route('fleet.insurance.index')->with('success', 'Insurance record updated.');
    }

    public function destroy($id)
    {
        $record = FleetInsuranceTracker::findOrFail($id);
        $record->update([
            'DeletedBy' => Auth::id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('fleet.insurance.index')->with('success', 'Insurance record deactivated.');
    }
}
