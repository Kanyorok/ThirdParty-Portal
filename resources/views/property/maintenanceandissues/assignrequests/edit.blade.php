@extends('layouts.app')
@php use Carbon\Carbon; @endphp
@section('title', 'Edit Request Assignment')

@section('content')
<div class="container mt-5" style="max-width: 700px;">
    <h3 class="mb-4">Edit Assignment</h3>

    <form action="{{ route('assignrequest.update', $assignment->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                {{-- Static Info --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Property</label>
                    <div class="form-control-plaintext">{{ $assignment->request->property->PropertyName ?? '-' }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Request Number</label>
                    <div class="form-control-plaintext">{{ $assignment->request->RequestNumber ?? '-' }}</div>
                    <input type="hidden" name="RequestNumber" value="{{ $assignment->RequestNumber }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Block</label>
                    <div class="form-control-plaintext">{{ $assignment->request->block->BlockName ?? '-' }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Floor</label>
                    <div class="form-control-plaintext">{{ $assignment->request->floor->FloorLabel ?? '-' }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Unit</label>
                    <div class="form-control-plaintext">{{ $assignment->request->unit->UnitCode ?? '-' }}</div>
                </div>

                {{-- Assignment Date --}}
                <div class="mb-3">
                    <label class="form-label">Assignment Date <span class="text-danger">*</span></label>
                    <input type="date" name="AssignmentDate" class="form-control"
                        value="{{ old('AssignmentDate', \Carbon\Carbon::parse($assignment->AssignmentDate)->format('Y-m-d')) }}">
                    @error('AssignmentDate') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Assignment Type --}}
                <div class="mb-3">
                    <label class="form-label">Assign To <span class="text-danger">*</span></label>
                    <select class="form-select" name="AssignmentTypeId" id="assignmentTypeSelect" required>
                        <option value="">-- Select Assignment Type --</option>
                        @foreach ($assignmentTypes as $assignmentType)
                            <option value="{{ $assignmentType->ID }}"
                                {{ (string) old('AssignmentType', $assignment->AssignmentType) === (string) $assignmentType->ID ? 'selected' : '' }}>
                                {{ $assignmentType->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Internal Technician --}}
                <div class="mb-3">
                    <label class="form-label">Internal Technician</label>
                    <select class="form-select" name="InternalTechnicianId" id="internalTechnicianSelect">
                        <option value="">-- Select Technician --</option>
                        @foreach ($technicians as $employee)
                            <option value="{{ $employee->Id }}"
                                {{ (string) old('InternalTechnician', $assignment->InternalTechnician) === (string) $employee->Id ? 'selected' : '' }}>
                                {{ $employee->JobTitle }}
                            </option>
                        @endforeach
                    </select>
                    @error('InternalTechnicianId') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Prequalified Vendor --}}
                <div class="mb-3">
                    <label class="form-label">Prequalified Vendor</label>
                    <select class="form-select" name="PrequalifiedVendorId" id="vendorSelect">
                        <option value="">-- Select Vendor --</option>
                        @foreach ($vendors as $supplier)
                            <option value="{{ $supplier->Id }}"
                                {{ (string) old('PrequalifiedVendorId', $assignment->PrequalifiedVendor) === (string) $supplier->Id ? 'selected' : '' }}>
                                {{ $supplier->SupplierName }}
                            </option>
                        @endforeach
                    </select>
                    @error('PrequalifiedVendorId') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Expected Start Date --}}
                <div class="mb-3">
                    <label class="form-label">Expected Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="ExpectedStartDate" class="form-control"
                        value="{{ old('ExpectedStartDate', \Carbon\Carbon::parse($assignment->ExpectedStartDate)->format('Y-m-d')) }}">
                    @error('ExpectedStartDate') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Expected Completion --}}
                <div class="mb-3">
                    <label class="form-label">Expected Completion <span class="text-danger">*</span></label>
                    <input type="date" name="ExpectedCompletion" class="form-control"
                        value="{{ old('ExpectedCompletion', \Carbon\Carbon::parse($assignment->ExpectedCompletion)->format('Y-m-d')) }}">
                    @error('ExpectedCompletion') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Priority Level --}}
                <div class="mb-3">
                    <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                    <select name="PriorityLevel" class="form-control" required>
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

                {{-- Instruction Notes --}}
                <div class="mb-3">
                    <label class="form-label">Instructions / Notes</label>
                    <textarea name="InstructionNotes" class="form-control" rows="3">{{ old('InstructionNotes', $assignment->InstructionNotes) }}</textarea>
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

    document.addEventListener('DOMContentLoaded', () => {
        toggleAssignmentFields();
    });
    assignmentType.addEventListener('change', toggleAssignmentFields);
</script>
@endpush
