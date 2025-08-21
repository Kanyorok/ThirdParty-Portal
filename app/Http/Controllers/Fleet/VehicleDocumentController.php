<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetVehicleDocument;

class VehicleDocumentController extends Controller
{
  
public function index()
{
    $documents = FleetVehicleDocument::with('vehicle')
        ->orderByDesc('CreatedOn')
        ->get();

    return view('fleet.documents.index', compact('documents'));
}
    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();

        return view('fleet.documents.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'DocumentType' => 'required|string|max:100',
            'DocumentNumber' => 'nullable|string|max:100',
            'IssueDate' => 'nullable|date',
            'ExpiryDate' => 'nullable|date',
            'DocumentFile' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'Notes' => 'nullable|string|max:500',
        ]);

        $filePath = null;

        if ($request->hasFile('DocumentFile')) {
            $file = $request->file('DocumentFile');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('uploads/vehicle-documents', $fileName, 'public');
        }

        FleetVehicleDocument::create([
            ...$validated,
            'FilePath' => $filePath,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle document uploaded successfully.');
    }
}
