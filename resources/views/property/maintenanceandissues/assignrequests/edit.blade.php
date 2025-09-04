@extends('layouts.app')
@php use Carbon\Carbon; @endphp
@section('title', 'Edit Request Assignment')

@section('content')
<div class="container mt-4">

    <form action="{{ route('assignrequest.update', $assignment->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Assignment Details</div>
            <div class="card-body">

                <!-- Static Request Info -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Property</label>
                        <input type="text" class="form-control" 
                            value="{{ $assignment->request->property->PropertyName ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Request Number</label>
                        <input type="text" class="form-control" 
                            value="{{ $assignment->request->RequestNumber ?? '-' }}" readonly>
                        <input type="hidden" name="RequestNumber" value="{{ $assignment->RequestNumber }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Block</label>
                        <input type="text" class="form-control" 
                            value="{{ $assignment->request->block->BlockName ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Floor</label>
                        <input type="text" class="form-control" 
                            value="{{ $assignment->request->floor->FloorLabel ?? '-' }}" readonly>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" 
                            value="{{ $assignment->request->unit->UnitCode ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Assignment Date <span class="text-danger">*</span></label>
                        <input type="date" name="AssignmentDate" class="form-control"
                            value="{{ old('AssignmentDate', Carbon::parse($assignment->AssignmentDate)->format('Y-m-d')) }}">
                        @error('AssignmentDate') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <!-- Assignment Type -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Assign To</label>
                        <select name="AssignmentType" id="assignmentTypeSelect" class="form-select" required>
                            <option value="">-- Select Assignment Type --</option>
                            @foreach($assignmentTypes as $type)
                                <option value="{{ $type->ID }}" 
                                    {{ $assignment->AssignmentType == $type->ID ? 'selected' : '' }}>
                                    {{ $type->Description }}
                                </option>
                            @endforeach
                        </select>
                        @error('AssignmentType') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Internal Technician</label>
                        <select class="form-select" name="InternalTechnician" id="internalTechnicianSelect">
                            <option value="">-- Select Technician --</option>
                            @foreach ($technicians as $employee)
                                <option value="{{ $employee->Id }}"
                                    {{ (string) old('InternalTechnician', $assignment->InternalTechnician) === (string) $employee->Id ? 'selected' : '' }}>
                                    {{ $employee->JobTitle }}
                                </option>
                            @endforeach
                        </select>
                        @error('InternalTechnician') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Prequalified Vendor</label>
                        <select class="form-select" name="PrequalifiedVendor" id="vendorSelect">
                            <option value="">-- Select Vendor --</option>
                            @foreach ($vendors as $supplier)
                                <option value="{{ $supplier->Id }}"
                                    {{ (string) old('PrequalifiedVendor', $assignment->PrequalifiedVendor) === (string) $supplier->Id ? 'selected' : '' }}>
                                    {{ $supplier->SupplierName }}
                                </option>
                            @endforeach
                        </select>
                        @error('PrequalifiedVendor') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <!-- Scheduling -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Expected Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="ExpectedStartDate" class="form-control"
                            value="{{ old('ExpectedStartDate', Carbon::parse($assignment->ExpectedStartDate)->format('Y-m-d')) }}">
                        @error('ExpectedStartDate') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expected Completion <span class="text-danger">*</span></label>
                        <input type="date" name="ExpectedCompletion" class="form-control"
                            value="{{ old('ExpectedCompletion', Carbon::parse($assignment->ExpectedCompletion)->format('Y-m-d')) }}">
                        @error('ExpectedCompletion') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                        <select name="PriorityLevel" class="form-select" required>
                            <option value="">-- Select Priority Level --</option>
                            @foreach ($priorityLevels as $level)
                                <option value="{{ $level->ID }}"
                                    {{ (string) old('PriorityLevel', $assignment->PriorityLevel) === (string) $level->ID ? 'selected' : '' }}>
                                    {{ $level->Description }}
                                </option>
                            @endforeach
                        </select>
                        @error('PriorityLevel') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mb-3">
                    <label class="form-label">Instructions / Notes</label>
                    <textarea name="InstructionNotes" class="form-control" rows="2">{{ old('InstructionNotes', $assignment->InstructionNotes) }}</textarea>
                    @error('InstructionNotes') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-success">Update Assignment</button>
                <a href="{{ route('assignrequest.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const assignmentType = document.getElementById('assignmentTypeSelect');
    const internalTechnician = document.getElementById('internalTechnicianSelect');
    const vendor = document.getElementById('vendorSelect');

    function toggleAssignmentFields() {
        const selectedText = assignmentType.options[assignmentType.selectedIndex]?.text?.trim();

        if (selectedText === 'Internal Technician') {
            internalTechnician.disabled = false;
            vendor.disabled = true;
            vendor.value = '';
        } else if (selectedText === 'Prequalified Vendor') {
            internalTechnician.disabled = true;
            internalTechnician.value = '';
            vendor.disabled = false;
        } else {
            internalTechnician.disabled = true;
            internalTechnician.value = '';
            vendor.disabled = true;
            vendor.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', toggleAssignmentFields);
    assignmentType.addEventListener('change', toggleAssignmentFields);
</script>
@endpush
