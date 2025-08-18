<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fleet\FleetInsuranceTracker;
use App\Models\Fleet\FleetVehicle;
use App\Http\Requests\FleetManagement\FleetInsuranceTrackerRequest;
use App\Services\FleetManagement\FleetInsuranceTrackerService;
use Illuminate\Support\Facades\Auth;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Storage;
use App\Models\Insurance\InsuranceProvider;
use App\Models\Core\CodeDetail;

class FleetInsuranceTrackerController extends Controller
{
    protected FleetInsuranceTrackerService $records;

    public function __construct(FleetInsuranceTrackerService $records)
    {
        $this->records = $records;
    }

    public function index()
    {
        $records = FleetInsuranceTracker::with(['insuranceStatus', 'insurance', 'vehicle'])
            ->where('CreatedBy', Auth::id())
            ->get();

        return view('fleet.compliance.insurance_tracker.index', compact('records'));
    }

    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $insuranceProvider = InsuranceProvider::all();
        $insuranceStatuses = CodeDetail::where('CodeID', 'InsuranceStatus')->orderBy('Value')->get();

        return view('fleet.compliance.insurance_tracker.create', compact('insuranceStatuses', 'insuranceProvider', 'vehicles'));
    }

    public function store(FleetInsuranceTrackerRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('DocumentPath')) {
            $validated['DocumentPath'] = $request->file('DocumentPath')->store('insurance_documents', 'public');
        }

        $record = $this->records->create($validated);

        return redirect()->route('fleet.insurance_tracker.index')->with('success', 'Insurance record created successfully.');
    }

    public function show($id)
    {
        $record = FleetInsuranceTracker::with(['insuranceStatus', 'insurance', 'vehicle'])->findOrFail($id);
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $insuranceProvider = InsuranceProvider::all();
        $insuranceStatuses = CodeDetail::where('CodeID', 'InsuranceStatus')->orderBy('Value')->get();

        return view('fleet.compliance.insurance_tracker.show', compact('record', 'vehicles', 'insuranceProvider', 'insuranceStatuses'));
    }

    public function edit($id)
    {
        $record = FleetInsuranceTracker::findOrFail($id);
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $insuranceProvider = InsuranceProvider::all();
        $insuranceStatuses = CodeDetail::where('CodeID', 'InsuranceStatus')->orderBy('Value')->get();

        return view('fleet.compliance.insurance_tracker.edit', compact('record', 'vehicles', 'insuranceStatuses', 'insuranceProvider'));
    }

    public function update(FleetInsuranceTrackerRequest $request, $id)
    {
        $record = FleetInsuranceTracker::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('DocumentPath')) {
            $validated['DocumentPath'] = $request->file('DocumentPath')->store('insurance_documents', 'public');
        }

        $this->records->update($record, $validated);

        return redirect()->route('fleet.insurance_tracker.index')->with('success', 'Insurance record updated.');
    }

    public function destroy($id)
    {
        $record = FleetInsuranceTracker::findOrFail($id);
        $this->records->delete($record);

        return redirect()->route('fleet.insurance_tracker.index')->with('success', 'Insurance record deactivated.');
    }
}
