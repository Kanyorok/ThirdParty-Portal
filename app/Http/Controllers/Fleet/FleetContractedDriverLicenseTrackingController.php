<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fleet\ContractedDriver;
use App\Models\Fleet\FleetContractedDriverLicense;
use Illuminate\Support\Facades\Auth;

class FleetContractedDriverLicenseTrackingController extends Controller
{
    public function index($contractedDriverId)
    {
        $driver = ContractedDriver::findOrFail($contractedDriverId);
        $licenses = FleetContractedDriverLicense::where('ContractedDriverID', $contractedDriverId)->get();

        return view('fleet.contracted_driver_licenses.index', compact('driver', 'licenses'));
    }

    public function create($contractedDriverId)
    {
        $driver = ContractedDriver::findOrFail($contractedDriverId);
        return view('fleet.contracted_driver_licenses.create', compact('driver'));
    }

    public function store(Request $request, $contractedDriverId)
    {
        $request->validate([
            'LicenseNumber' => 'required|string|max:50',
            'LicenseCategory' => 'required|string|max:20',
            'IssueDate' => 'required|date',
            'ExpiryDate' => 'required|date|after_or_equal:IssueDate',
            'Notes' => 'nullable|string|max:255',
        ]);

        FleetContractedDriverLicense::create([
            'ContractedDriverID' => $contractedDriverId,
            'LicenseNumber' => $request->LicenseNumber,
            'LicenseCategory' => $request->LicenseCategory,
            'IssueDate' => $request->IssueDate,
            'ExpiryDate' => $request->ExpiryDate,
            'Notes' => $request->Notes,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('fleet.contracted_driver_licenses.index', $contractedDriverId)
                         ->with('success', 'License recorded successfully.');
    }
}