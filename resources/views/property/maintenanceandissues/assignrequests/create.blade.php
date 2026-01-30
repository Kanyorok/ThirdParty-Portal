@extends('layouts.app')

@section('title', 'Assign Maintenance Task')

@section('content')
<div class="container mt-4">

<form action="{{ route('assignrequest.store') }}" method="POST">
@csrf

<div class="card shadow">
    <div class="card-header bg-primary text-white fw-bold">
        Assignment Details
    </div>

    <div class="card-body">

        {{-- Maintenance Request --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">
                    Select Maintenance Request <span class="text-danger">*</span>
                </label>
                <select id="request-select" name="RequestNumber" class="form-select">
                    <option value="">-- Select Request --</option>
                    @foreach ($maintenancerequests as $req)
                        <option value="{{ $req->Id }}"
                            {{ old('RequestNumber') == $req->Id ? 'selected' : '' }}
                            data-property="{{ $req->property->PropertyName ?? '' }}"
                            data-block="{{ $req->block->BlockName ?? '' }}"
                            data-floor="{{ $req->floor->FloorLabel ?? '' }}"
                            data-unit="{{ $req->unit->UnitCode ?? '' }}">
                            {{ $req->RequestNumber }} - {{ $req->reportedByUser->ThirdPartyName ?? 'Name' }}
                        </option>
                    @endforeach
                </select>
                @error('RequestNumber')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    Assignment Date <span class="text-danger">*</span>
                </label>
                <input type="date"
                       class="form-control"
                       name="AssignmentDate"
                       value="{{ old('AssignmentDate', date('Y-m-d')) }}" readonly>
                @error('AssignmentDate')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        {{-- Auto-filled Info --}}
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Property</label>
                <input type="text" id="property-display" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Block</label>
                <input type="text" id="block-display" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Floor</label>
                <input type="text" id="floor-display" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Unit</label>
                <input type="text" id="unit-display" class="form-control" readonly>
            </div>
        </div>

        {{-- Assignment --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">
                    Assign To <span class="text-danger">*</span>
                </label>
                <select class="form-select" name="AssignmentType" id="assignmentTypeSelect">
                    <option value="">-- Select --</option>
                    @foreach ($assignmentTypes as $type)
                        <option value="{{ $type->ID }}"
                            {{ old('AssignmentType') == $type->ID ? 'selected' : '' }}>
                            {{ $type->Description }}
                        </option>
                    @endforeach
                </select>
                @error('AssignmentType')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Internal Technician</label>
                <select class="form-select" name="InternalTechnician" id="internalTechnicianSelect">
                    <option value="">-- Select --</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->Id }}"
                            {{ old('InternalTechnician') == $emp->Id ? 'selected' : '' }}>
                            {{ $emp->EmployeeID }} - {{ $emp->FirstName }}
                        </option>
                    @endforeach
                </select>
                @error('InternalTechnician')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Prequalified Vendor</label>
                <select class="form-select" name="PrequalifiedVendor" id="vendorSelect">
                    <option value="">-- Select --</option>
                    @foreach ($suppliers as $sup)
                        <option value="{{ $sup->Id }}"
                            {{ old('PrequalifiedVendor') == $sup->Id ? 'selected' : '' }}>
                            {{ $sup->party->ThirdPartyName ?? 'Name' }}
                        </option>
                    @endforeach
                </select>
                @error('PrequalifiedVendor')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        {{-- Schedule --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">
                    Expected Start Date <span class="text-danger">*</span>
                </label>
                <input type="date"
                       class="form-control"
                       name="ExpectedStartDate"
                       value="{{ old('ExpectedStartDate') }}">
                @error('ExpectedStartDate')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">
                    Expected Completion <span class="text-danger">*</span>
                </label>
                <input type="date"
                       class="form-control"
                       name="ExpectedCompletion"
                       value="{{ old('ExpectedCompletion') }}">
                @error('ExpectedCompletion')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">
                    Priority Level <span class="text-danger">*</span>
                </label>
                <select class="form-select" name="PriorityLevel">
                    <option value="">-- Select --</option>
                    @foreach ($priorityLevels as $priority)
                        <option value="{{ $priority->ID }}"
                            {{ old('PriorityLevel') == $priority->ID ? 'selected' : '' }}>
                            {{ $priority->Description }}
                        </option>
                    @endforeach
                </select>
                @error('PriorityLevel')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        {{-- Notes --}}
        <div class="mb-3">
            <label class="form-label">
                Instructions / Notes <span class="text-danger">*</span>
            </label>
            <textarea class="form-control"
                      name="InstructionNotes"
                      rows="3"
                      required>{{ old('InstructionNotes') }}</textarea>
            @error('InstructionNotes')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('assignrequest.index') }}" class="btn btn-outline-secondary">
                Cancel
            </a>
            <button type="submit" class="btn btn-success">
                Assign Task
            </button>
        </div>

    </div>
</div>
</form>
</div>

{{-- Styles --}}
<style>
    .disabled {
        pointer-events: none;
        background-color: #e9ecef;
    }
</style>

{{-- Scripts --}}
<script>
const requestSelect = document.getElementById('request-select');
const typeSelect    = document.getElementById('assignmentTypeSelect');
const techSelect    = document.getElementById('internalTechnicianSelect');
const vendorSelect  = document.getElementById('vendorSelect');

function fillPropertyInfo() {
    const opt = requestSelect.options[requestSelect.selectedIndex];
    if (!opt) return;

    document.getElementById('property-display').value = opt.dataset.property || '';
    document.getElementById('block-display').value    = opt.dataset.block || '';
    document.getElementById('floor-display').value    = opt.dataset.floor || '';
    document.getElementById('unit-display').value     = opt.dataset.unit || '';
}

function toggleAssignmentFields() {
    let text = typeSelect.options[typeSelect.selectedIndex]?.text?.trim();

    techSelect.classList.remove('disabled');
    vendorSelect.classList.remove('disabled');

    if (text === 'Internal Technician') {
        vendorSelect.classList.add('disabled');
        vendorSelect.value = '';
    } else if (text === 'Prequalified Vendor') {
        techSelect.classList.add('disabled');
        techSelect.value = '';
    } else {
        techSelect.classList.add('disabled');
        vendorSelect.classList.add('disabled');
        techSelect.value = '';
        vendorSelect.value = '';
    }
}

requestSelect.addEventListener('change', fillPropertyInfo);
typeSelect.addEventListener('change', toggleAssignmentFields);

document.addEventListener('DOMContentLoaded', () => {
    fillPropertyInfo();
    toggleAssignmentFields();
});
</script>

@endsection
