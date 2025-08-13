<?php


namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetDriverLicenseTracking;


class FleetDriverLicenseTrackingController extends Controller
{
    // Show license history for a specific driver
public function index($driverId)
{
    $driver = FleetDriver::findOrFail($driverId);
    $licenses = FleetDriverLicenseTracking::where('DriverID', $driverId)
        ->orderByDesc('IssuedDate')
        ->get();

    return view('fleet.licenses.index', compact('driver', 'licenses'));
}

    // Show create form
    public function create($driverId)
    {
        $driver = FleetDriver::findOrFail($driverId);
        return view('fleet.licenses.create', compact('driver'));
    }

    // Store license tracking record
    public function store(Request $request)
    {
        $validated = $request->validate([
            'DriverID' => 'required|exists:t_FleetDrivers,ID',
            'LicenseNumber' => 'required|string|max:50',
            'LicenseCategory' => 'nullable|string|max:50',
            'IssuedDate' => 'nullable|date',
            'ExpiryDate' => 'required|date|after:IssuedDate',
            'RenewalDate' => 'nullable|date|after_or_equal:ExpiryDate',
            'Notes' => 'nullable|string|max:255',
        ]);

        FleetDriverLicenseTracking::create([
            ...$validated,
            'CreatedOn' => now(),
            'CreatedBy' => Auth::id(),
        ]);

        return redirect()
            ->route('fleet.licenses.index', $validated['DriverID'])
            ->with('success', 'License record added successfully.');
    }

    
}
