@extends('layouts.app')
@section('title', 'Edit Assignment Request')

@section('content')
<div class="container mt-4">
    <h4 class="fw-bold mb-3">🛠️ Edit Technician / Vendor Assignment</h4>

    <form action="{{ route('assignrequest.update', $assignment->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">🔧 Assignment Details</div>
            <div class="card-body">

                <!-- Maintenance Request Dropdown -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Select Maintenance Request</label>
                    <select id="request-select" name="RequestNumber" class="form-select" required>
                        <option value="">-- Select Request --</option>
                        @foreach ($maintenancerequests as $maintenancerequest)
                            <option
                                value="{{ $maintenancerequest->Id }}"
                                data-property="{{ $maintenancerequest->property->PropertyName ?? '_' }}"
                                data-block="{{ $maintenancerequest->block->BlockName }}"
                                data-floor="{{ $maintenancerequest->floor->FloorLabel }}"
                                data-unit="{{ $maintenancerequest->unit->UnitCode }}"
                                data-property-id="{{ $maintenancerequest->property->Id ?? '' }}"
                                data-block-id="{{ $maintenancerequest->block->Id ?? '' }}"
                                data-floor-id="{{ $maintenancerequest->floor->Id ?? '' }}"
                                data-unit-id="{{ $maintenancerequest->unit->Id ?? '' }}"
                                {{ old('RequestNumber', $assignment->RequestNumber ?? '') == $maintenancerequest->Id ? 'selected' : '' }}>
                                {{ $maintenancerequest->RequestNumber }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Auto-filled Info -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Property</label>
                    <input type="text" id="property-display" class="form-control" readonly
                        value="{{ $selectedRequest->property->PropertyName ?? '' }}">
                    <input type="hidden" name="Property" id="property-id"
                        value="{{ $selectedRequest->property->Id ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Block</label>
                    <input type="text" id="block-display" class="form-control" readonly
                        value="{{ $selectedRequest->block->BlockName ?? '' }}">
                    <input type="hidden" name="Block" id="block-id"
                        value="{{ $selectedRequest->block->Id ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Floor</label>
                    <input type="text" id="floor-display" class="form-control" readonly
                        value="{{ $selectedRequest->floor->FloorLabel ?? '' }}">
                    <input type="hidden" name="Floor" id="floor-id"
                        value="{{ $selectedRequest->floor->Id ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Unit</label>
                    <input type="text" id="unit-display" class="form-control" readonly
                        value="{{ $selectedRequest->unit->UnitCode ?? '' }}">
                    <input type="hidden" name="Unit" id="unit-id"
                        value="{{ $selectedRequest->unit->Id ?? '' }}">
                </div>
            </div>
                    <div class="col-md-6">
                        <label class="form-label">Assignment Date</label>
                        <input type="date" class="form-control" name="AssignmentDate" value="{{ old('AssignmentDate', $assignment->AssignmentDate) }}" required>
                    </div>
                </div>
                <!-- Assignment Type -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Assign To</label>
                        <select class="form-select" name="AssignmentType" id="assignmentTypeSelect" required>
                            <option value="#">--Select a technician--</option>
                            @foreach ($assignmentTypes as $assignmentType)
                                <option value="{{ $assignmentType->ID }}" {{ $assignmentType->ID == old('AssignmentType', $assignment->AssignmentType) ? 'selected' : '' }}>{{ $assignmentType->Description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Internal Technician</label>
                        <select class="form-select" name="InternalTechnician" id="internalTechnicianSelect">
                            <option value="#">--Select a technician--</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->Id }}" {{ $employee->Id == old('InternalTechnician', $assignment->InternalTechnician) ? 'selected' : '' }}>{{ $employee->JobTitle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Prequalified Vendor</label>
                        <select class="form-select" name="PrequalifiedVendor" id="vendorSelect">
                            <option value="#">--Select a vendor--</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->Id }}" {{ $supplier->Id == old('PrequalifiedVendor', $assignment->PrequalifiedVendor) ? 'selected' : '' }}>{{ $supplier->SupplierName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Scheduling -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Expected Start Date</label>
                        <input type="date" class="form-control" name="ExpectedStartDate" value="{{ old('ExpectedStartDate', $assignment->ExpectedStartDate) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expected Completion</label>
                        <input type="date" class="form-control" name="ExpectedCompletion" value="{{ old('ExpectedCompletion', $assignment->ExpectedCompletion) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priority Level</label>
                        <select class="form-select" name="PriorityLevel" required>
                            <option value="#">--Select Priority Level--</option>
                            @foreach ($priorityLevels as $priorityLevel)
                                <option value="{{ $priorityLevel->ID }}" {{ $priorityLevel->ID == old('PriorityLevel', $assignment->PriorityLevel) ? 'selected' : '' }}>{{ $priorityLevel->Description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mb-3">
                    <label class="form-label">Instructions / Notes</label>
                    <textarea class="form-control" rows="2" name="InstructionNotes">{{ old('InstructionNotes', $assignment->InstructionNotes) }}</textarea>
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
<script>
    document.getElementById('request-select').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];

        document.getElementById('property-display').value = selected.getAttribute('data-property') || '';
        document.getElementById('property-id').value = selected.getAttribute('data-property') || '';

        document.getElementById('block-display').value = selected.getAttribute('data-block') || '';
        document.getElementById('block-id').value = selected.getAttribute('data-block') || '';

        document.getElementById('floor-display').value = selected.getAttribute('data-floor') || '';
        document.getElementById('floor-id').value = selected.getAttribute('data-floor') || '';

        document.getElementById('unit-display').value = selected.getAttribute('data-unit') || '';
        document.getElementById('unit-id').value = selected.getAttribute('data-unit') || '';
    });git add
</script>
@endsection