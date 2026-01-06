@extends('layouts.app')
@section('title', 'Assign Maintenance Task')
@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the errors below:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-4">
    <h4 class="fw-bold mb-3">Assign Technician / Vendor</h4>

    <form action="{{ route('assignrequest.store') }}" method="POST">
        @csrf

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Assignment Details</div>
            <div class="card-body">
                {{-- Maintenance Request --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Maintenance Request<span class="text-danger">*</span></label>
                        <select id="request-select" name="RequestNumber" class="form-select">
                            <option value="">-- Select Request --</option>
                            @foreach ($maintenancerequests as $req)
                                <option value="{{ $req->Id }}"
                                        data-property="{{ $req->property->PropertyName ?? '' }}"
                                        data-block="{{ $req->block->BlockName ?? '' }}"
                                        data-floor="{{ $req->floor->FloorLabel ?? '' }}"
                                        data-unit="{{ $req->unit->UnitCode ?? '' }}">
                                    {{ $req->RequestNumber }}
                                </option>
                            @endforeach
                        </select>
                        @error('RequestNumber') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Assignment Date<span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="AssignmentDate"
                               value="{{ old('AssignmentDate', date('Y-m-d')) }}">
                        @error('AssignmentDate') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Auto-filled section --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Property</label>
                        <input type="text" class="form-control" id="property-display" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Block</label>
                        <input type="text" class="form-control" id="block-display" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Floor</label>
                        <input type="text" class="form-control" id="floor-display" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" id="unit-display" readonly>
                    </div>
                </div>

                {{-- Assignment Type --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Assign To<span class="text-danger">*</span></label>
                        <select class="form-select" name="AssignmentType" id="assignmentTypeSelect">
                            <option value="">--Select--</option>
                            @foreach ($assignmentTypes as $type)
                                <option value="{{ $type->ID }}">{{ $type->Description }}</option>
                            @endforeach
                        </select>
                        @error('AssignmentType') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Internal Technician</label>
                        <select class="form-select" name="InternalTechnician" id="internalTechnicianSelect" disabled>
                            <option value="">--Select--</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->Id }}">{{ $emp->EmployeeID }} - {{ $emp->FirstName }}</option>
                            @endforeach
                        </select>
                        @error('InternalTechnician') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Prequalified Vendor</label>
                        <select class="form-select" name="PrequalifiedVendor" id="vendorSelect" disabled>
                            <option value="">--Select--</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->Id }}">{{ $sup->party->ThirdPartyName ?? 'Name'}}</option>
                            @endforeach
                        </select>
                        @error('PrequalifiedVendor') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Schedule --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Expected Start Date<span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="ExpectedStartDate">
                        @error('ExpectedStartDate') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Expected Completion<span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="ExpectedCompletion">
                        @error('ExpectedCompletion') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Priority Level<span class="text-danger">*</span></label>
                        <select class="form-select" name="PriorityLevel">
                            <option value="">--Select--</option>
                            @foreach ($priorityLevels as $priority)
                                <option value="{{ $priority->ID }}">{{ $priority->Description }}</option>
                            @endforeach
                        </select>
                        @error('PriorityLevel') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-3">
                    <label class="form-label">Instructions / Notes<span class="text-danger">*</span></label>
                    <textarea class="form-control" name="InstructionNotes" rows="3">{{ old('InstructionNotes') }}</textarea>
                    @error('InstructionNotes') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        Assign Task
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    // Autofill building info
    document.getElementById('request-select').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        document.getElementById('property-display').value = selected.dataset.property || '';
        document.getElementById('block-display').value    = selected.dataset.block || '';
        document.getElementById('floor-display').value    = selected.dataset.floor || '';
        document.getElementById('unit-display').value     = selected.dataset.unit || '';
    });

    const typeSelect = document.getElementById('assignmentTypeSelect');
    const techSelect = document.getElementById('internalTechnicianSelect');
    const vendorSelect = document.getElementById('vendorSelect');

    function toggleFields() {
        let selected = typeSelect.options[typeSelect.selectedIndex].text;

        if (selected === "Internal Technician") {
            techSelect.disabled = false;
            vendorSelect.disabled = true;
            vendorSelect.value = "";
        } else if (selected === "Prequalified Vendor") {
            vendorSelect.disabled = false;
            techSelect.disabled = true;
            techSelect.value = "";
        } else {
            techSelect.disabled = true;
            vendorSelect.disabled = true;
        }
    }

    typeSelect.addEventListener('change', toggleFields);
    document.addEventListener('DOMContentLoaded', toggleFields);
</script>

@endsection
