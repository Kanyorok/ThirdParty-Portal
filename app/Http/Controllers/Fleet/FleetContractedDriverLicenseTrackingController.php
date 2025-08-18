<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Fleet\ContractedDriver;
use App\Models\Fleet\FleetContractedDriverLicense;
use App\Services\FleetManagement\FleetContractedDriverLicenseService;
use App\Http\Requests\FleetManagement\FleetContractedDriverLicenseRequest;

class FleetContractedDriverLicenseTrackingController extends Controller
{
    protected FleetContractedDriverLicenseService $licenses;

    public function __construct(FleetContractedDriverLicenseService $licenses)
    {
        $this->licenses = $licenses;
    }

    public function index($driverId)
    {
        $driver = ContractedDriver::findOrFail($driverId);
        $licenses = FleetContractedDriverLicense::where('ContractedDriverID', $driverId)->get();

        return view('fleet.contracted_drivers.show', compact('driver', 'licenses'));
    }

    public function store(FleetContractedDriverLicenseRequest $request, $driverId)
    {
        $validated = $request->validated();
        $validated['ContractedDriverID'] = $driverId;
        $validated['CreatedBy'] = Auth::id();
        $validated['CreatedOn'] = now();

        $license = $this->licenses->create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'License created successfully.',
                'license' => $license
            ]);
        }

        return redirect()->route('fleet.contracted_driver_licenses.index', $driverId)
            ->with('success', 'License created successfully.');
    }

    public function edit($driverId, $licenseId)
    {
        $license = FleetContractedDriverLicense::where('ContractedDriverID', $driverId)
                    ->where('Id', $licenseId)
                    ->firstOrFail(); 

        return response()->json($license);
    }

  public function update(FleetContractedDriverLicenseRequest $request, $driverId, $licenseId)
    {
        $license = FleetContractedDriverLicense::findOrFail($licenseId);
        $validated = $request->validated();
        $this->licenses->update($license, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'Id' => $license->Id,
                'LicenseNumber' => $license->LicenseNumber,
                'LicenseCategory' => $license->LicenseCategory,
                'IssueDate' => $license->IssueDate,
                'ExpiryDate' => $license->ExpiryDate,
                'Notes' => $license->Notes
            ]);
        }

        return redirect()->route('fleet.contracted_driver_licenses.index', $driverId)
            ->with('success', 'License updated successfully.');
    }


   public function destroy(Request $request, $driverId, $licenseId)
    {
        $license = FleetContractedDriverLicense::findOrFail($licenseId);

        $this->licenses->delete($license);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'License deleted successfully.'
            ]);
        }

        return redirect()->route('fleet.contracted_driver_licenses.index', $driverId)
            ->with('success', 'License deleted successfully.');
    }

}
