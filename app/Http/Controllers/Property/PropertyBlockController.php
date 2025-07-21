<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyBlockRequest;
use App\Services\Property\PropertyRegistry\PropertyBlockService;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyRegistry;


class PropertyBlockController extends Controller
{
    //
    public function index()
    {
        $blocks = PropertyBlock::with('property')->get();
        return view('property.propertyregistry.structuralmapping.addblock.index',compact('blocks'));
    }
    public function create(){
        $properties = PropertyRegistry::all();
        return view('property.propertyregistry.structuralmapping.addblock.create', compact('properties'));
    }

    public function store(PropertyBlockRequest $request)
    {

        $validated = $request->validated();

        $propertyregistry = PropertyRegistry::findOrFail($validated['PropertyID']);

        $propertyblock = PropertyBlockService::create(
            $propertyregistry,
            $validated['BlockName'] ?? '--',
            $validated['Description'] ?? '--',
            auth()->user()
        );

           return redirect()->route('addblock.index')->with('success','property block created successfully');
    }

    @extends('layouts.app')
@section('title', 'Edit Assignment Request')

@section('content')
<div class="container mt-4">
    <h4 class="fw-bold mb-3">🛠️ Edit Technician / Vendor Assignment</h4>

    <form action="{{ route('assignrequest.update', $assignrequest->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">🔧 Assignment Details</div>
            <div class="card-body">

                <!-- Maintenance Request Display -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Maintenance Request Number</label>
                        <input type="text" class="form-control" value="{{ $assignrequest->maintenancerequest->RequestNumber }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assignment Date</label>
                        <input type="date" class="form-control" name="AssignmentDate" value="{{ old('AssignmentDate', $assignrequest->AssignmentDate) }}" required>
                    </div>
                </div>

                <!-- Auto-filled Info -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Property</label>
                        <input type="text" class="form-control" value="{{ $assignrequest->property->PropertyName ?? '' }}" readonly>
                        <input type="hidden" name="Property" value="{{ old('Property', $assignrequest->Property) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Block</label>
                        <input type="text" class="form-control" value="{{ $assignrequest->block->BlockName ?? '' }}" readonly>
                        <input type="hidden" name="Block" value="{{ old('Block', $assignrequest->Block) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Floor</label>
                        <input type="text" class="form-control" value="{{ $assignrequest->floor->FloorLabel ?? '' }}" readonly>
                        <input type="hidden" name="Floor" value="{{ old('Floor', $assignrequest->Floor) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" value="{{ $assignrequest->unit->UnitCode ?? '' }}" readonly>
                        <input type="hidden" name="Unit" value="{{ old('Unit', $assignrequest->Unit) }}">
                    </div>
                </div>

                <!-- Assignment Type -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Assign To</label>
                        <select class="form-select" name="AssignmentType" id="assignmentTypeSelect" required>
                            <option value="#">--Select a technician--</option>
                            @foreach ($assignmentTypes as $assignmentType)
                                <option value="{{ $assignmentType->ID }}" {{ $assignmentType->ID == old('AssignmentType', $assignrequest->AssignmentType) ? 'selected' : '' }}>{{ $assignmentType->Description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Internal Technician</label>
                        <select class="form-select" name="InternalTechnician" id="internalTechnicianSelect">
                            <option value="#">--Select a technician--</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->Id }}" {{ $employee->Id == old('InternalTechnician', $assignrequest->InternalTechnician) ? 'selected' : '' }}>{{ $employee->JobTitle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Prequalified Vendor</label>
                        <select class="form-select" name="PrequalifiedVendor" id="vendorSelect">
                            <option value="#">--Select a vendor--</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->Id }}" {{ $supplier->Id == old('PrequalifiedVendor', $assignrequest->PrequalifiedVendor) ? 'selected' : '' }}>{{ $supplier->SupplierName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Scheduling -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Expected Start Date</label>
                        <input type="date" class="form-control" name="ExpectedStartDate" value="{{ old('ExpectedStartDate', $assignrequest->ExpectedStartDate) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expected Completion</label>
                        <input type="date" class="form-control" name="ExpectedCompletion" value="{{ old('ExpectedCompletion', $assignrequest->ExpectedCompletion) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priority Level</label>
                        <select class="form-select" name="PriorityLevel" required>
                            <option value="#">--Select Priority Level--</option>
                            @foreach ($priorityLevels as $priorityLevel)
                                <option value="{{ $priorityLevel->ID }}" {{ $priorityLevel->ID == old('PriorityLevel', $assignrequest->PriorityLevel) ? 'selected' : '' }}>{{ $priorityLevel->Description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mb-3">
                    <label class="form-label">Instructions / Notes</label>
                    <textarea class="form-control" rows="2" name="InstructionNotes">{{ old('InstructionNotes', $assignrequest->InstructionNotes) }}</textarea>
                </div>

                <!-- Submit -->
                <div class="text-end">
                    <button type="submit" class="btn btn-success">💾 Update Assignment</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const assignmentType = document.getElementById('assignmentTypeSelect');
    const internalTechnician = document.getElementById('internalTechnicianSelect');
    const vendor = document.getElementById('vendorSelect');

    function toggleAssignmentFields() {
        const selectedText = assignmentType.options[assignmentType.selectedIndex].text.trim();

        if (selectedText === 'Internal Technician') {
            internalTechnician.disabled = false;
            vendor.disabled = true;
            vendor.selectedIndex = 0;
        } else if (selectedText === 'Prequalified Vendor') {
            internalTechnician.disabled = true;
            vendor.disabled = false;
            internalTechnician.selectedIndex = 0;
        } else {
            internalTechnician.disabled = true;
            vendor.disabled = true;
        }
    }

    document.addEventListener('DOMContentLoaded', toggleAssignmentFields);
    assignmentType.addEventListener('change', toggleAssignmentFields);
</script>
@endsection