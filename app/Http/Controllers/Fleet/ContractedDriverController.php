<?php


namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\ContractedDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractedDriverController extends Controller
{
    // Show all contracted drivers
    public function index()
    {
        $drivers = ContractedDriver::where('IsActive', 1)->orderBy('FullName')->get();
        return view('fleet.contracted_drivers.index', compact('drivers'));
    }

    // Show create form
    public function create()
    {
        return view('fleet.contracted_drivers.create');
    }

    // Store new contracted driver
    public function store(Request $request)
    {
        $validated = $request->validate([
            'FullName' => 'required|string|max:255',
            'NationalID' => 'nullable|string|max:50',
            'Phone' => 'nullable|string|max:50',
            'CompanyName' => 'nullable|string|max:255',
            'ContractStartDate' => 'nullable|date',
            'ContractEndDate' => 'nullable|date|after_or_equal:ContractStartDate',
            'LicenseNumber' => 'nullable|string|max:100',
            'LicenseExpiryDate' => 'nullable|date',
            'LicenseCategory' => 'nullable|string|max:50',
            'Notes' => 'nullable|string|max:1000',
        ]);

        $validated['CreatedBy'] = Auth::id();
        $validated['CreatedOn'] = now();

        ContractedDriver::create($validated);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver registered successfully.');
    }

    // Show edit form
    public function edit($id)
    {
        $driver = ContractedDriver::findOrFail($id);
        return view('fleet.contracted_drivers.edit', compact('driver'));
    }

    // Update existing record
    public function update(Request $request, $id)
    {
        $driver = ContractedDriver::findOrFail($id);

        $validated = $request->validate([
            'FullName' => 'required|string|max:255',
            'NationalID' => 'nullable|string|max:50',
            'Phone' => 'nullable|string|max:50',
            'CompanyName' => 'nullable|string|max:255',
            'ContractStartDate' => 'nullable|date',
            'ContractEndDate' => 'nullable|date|after_or_equal:ContractStartDate',
            'LicenseNumber' => 'nullable|string|max:100',
            'LicenseExpiryDate' => 'nullable|date',
            'LicenseCategory' => 'nullable|string|max:50',
            'Status' => 'required|string|max:50',
            'Notes' => 'nullable|string|max:1000',
        ]);

        $validated['ModifiedBy'] = Auth::id();
        $validated['ModifiedOn'] = now();

        $driver->update($validated);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver updated successfully.');
    }

    // Deactivate contracted driver
    public function deactivate($id)
    {
        $driver = ContractedDriver::findOrFail($id);
        $driver->update([
            'IsActive' => 0,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver deactivated.');
    }

    
    public function show($id)
{
    $driver = ContractedDriver::findOrFail($id);
    return view('fleet.contracted_drivers.show', compact('driver'));
}

}
