<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetDriverLicenseTracking;
use App\Services\FleetManagement\FleetDriverLicenseTrackingService;
use App\Http\Requests\FleetManagement\FleetDriverLicenseTrackingRequest;

class FleetDriverLicenseTrackingController extends Controller
{
    protected FleetDriverLicenseTrackingService $licenses;

    public function __construct(FleetDriverLicenseTrackingService $licenses)
    {
        $this->licenses = $licenses;
    }

    // List all licenses for a driver
    public function index(Request $request)
    {
        $driverId = $request->query('driver_id');
        $driver = FleetDriver::findOrFail($driverId);
        $licenses = FleetDriverLicenseTracking::where('DriverID', $driverId)->get();

        return view('fleet.drivers.show', compact('driver', 'licenses'));
    }

    // Store a new license
    public function store(FleetDriverLicenseTrackingRequest $request)
    {
        $validated = $request->validated();
        $validated['CreatedBy'] = Auth::id();
        $validated['CreatedOn'] = now();

        $license = $this->licenses->create($validated);
        $driverId = $validated['DriverID'];

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'License created successfully.',
                'license' => $license
            ]);
        }

        return redirect()->route('fleet.licenses.index', ['driver_id' => $driverId])
            ->with('success', 'License created successfully.');
    }

    // Show license for editing (AJAX or JSON)
    public function edit($Id)
    {
        $license = FleetDriverLicenseTracking::findOrFail($Id);
        return response()->json($license);
    }

    // Update license
    public function update(FleetDriverLicenseTrackingRequest $request, $Id)
    {
        $license = FleetDriverLicenseTracking::findOrFail($Id);
        $validated = $request->validated();

        $this->licenses->update($license, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'License updated successfully.',
                'license' => $license->fresh()
            ]);
        }

        return redirect()->route('fleet.licenses.index', ['driver_id' => $license->DriverID])
            ->with('success', 'License updated successfully.');
    }

    // Delete license
    public function destroy(Request $request, $Id)
    {
        $license = FleetDriverLicenseTracking::findOrFail($Id);
        $driverId = $license->DriverID;

        $this->licenses->delete($license);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'License deleted successfully.'
            ]);
        }

        return redirect()->route('fleet.licenses.index', ['driver_id' => $driverId])
            ->with('success', 'License deleted successfully.');
    }

    // Optional: show single license
    public function show($Id)
    {
        $license = FleetDriverLicenseTracking::findOrFail($Id);
        return view('fleet.drivers.license-show', compact('license'));
    }
}
