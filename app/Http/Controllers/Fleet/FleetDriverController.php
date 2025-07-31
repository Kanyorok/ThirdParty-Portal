<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetDriver;

class FleetDriverController extends Controller
{
    public function index()
    {
        $drivers = FleetDriver::where('IsActive', 1)->get();

        return view('fleet.drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('fleet.drivers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'FullName' => 'required|string|max:255',
            'StaffNumber' => 'nullable|string|max:50',
            'NationalID' => 'required|string|max:20',
            'Phone' => 'required|string|max:20',
            'LicenseNumber' => 'required|string|max:50',
            'LicenseExpiryDate' => 'required|date',
            'LicenseCategory' => 'nullable|string|max:50',
            'EmploymentType' => 'required|string|max:50',
        ]);

        FleetDriver::create([
            ...$validated,
            'IsActive' => 1,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('fleet.drivers.index')->with('success', 'Driver registered successfully.');
    }

    public function edit($id)
    {
        $driver = FleetDriver::findOrFail($id);
        return view('fleet.drivers.edit', compact('driver'));
    }

    public function update(Request $request, $id)
    {
        $driver = FleetDriver::findOrFail($id);

        $validated = $request->validate([
            'FullName' => 'required|string|max:255',
            'StaffNumber' => 'nullable|string|max:50',
            'NationalID' => 'required|string|max:20',
            'Phone' => 'required|string|max:20',
            'LicenseNumber' => 'required|string|max:50',
            'LicenseExpiryDate' => 'required|date',
            'LicenseCategory' => 'nullable|string|max:50',
            'EmploymentType' => 'required|string|max:50',
        ]);

        $driver->update([
            ...$validated,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('fleet.drivers.index')->with('success', 'Driver details updated successfully.');
    }

    public function deactivate($id)
{
    $driver = FleetDriver::findOrFail($id);

    $driver->update([
        'IsActive' => 0,
        'ModifiedBy' => Auth::id(),
        'ModifiedOn' => now(),
    ]);

    return redirect()->route('fleet.drivers.index')->with('success', 'Driver deactivated successfully.');
}
public function show($id)
{
    $driver = FleetDriver::findOrFail($id);

    return view('fleet.drivers.show', compact('driver'));
}
}
