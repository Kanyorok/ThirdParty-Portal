@extends('layouts.app')
@section('title', 'Maintenance Request')

@section('content')
<div class="container mt-4">

    {{-- GLOBAL VALIDATION ERROR LIST --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the errors below:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('maintenancerequest.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Report Maintenance Issue</div>
            <div class="card-body">

                <!-- Property Drill-down -->
                <div class="row g-3 mb-3">

                    {{-- PROPERTY --}}
                    <div class="col-md-4">
                        <label class="form-label">Select Property <span class="text-danger">*</span></label>
                        <select name="Property"
                                id="property-select"
                                class="form-select @error('Property') is-invalid @enderror"
                                required>
                            <option value="">-- Select Property --</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->Id }}"
                                        {{ old('Property') == $property->Id ? 'selected' : '' }}>
                                    {{ $property->PropertyName }}
                                </option>
                            @endforeach
                        </select>
                        @error('Property')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- BLOCK --}}
                    <div class="col-md-4">
                        <label class="form-label">Select Block</label>
                        <select name="Block"
                                id="block-select"
                                class="form-select @error('Block') is-invalid @enderror">
                            <option value="">-- Select Block --</option>
                        </select>
                        @error('Block')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- FLOOR --}}
                    <div class="col-md-4">
                        <label class="form-label">Select Floor</label>
                        <select name="Floor"
                                id="floor-select"
                                class="form-select @error('Floor') is-invalid @enderror">
                            <option value="">-- Select Floor --</option>
                        </select>
                        @error('Floor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- UNIT --}}
                    <div class="col-md-6 mt-3">
                        <label class="form-label">Select Unit</label>
                        <select name="Unit"
                                id="unit-select"
                                class="form-select @error('Unit') is-invalid @enderror">
                            <option value="">-- Select Unit --</option>
                        </select>
                        @error('Unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <!-- Request Details -->
                <div class="row g-3 mb-3">

                    {{-- REPORTED BY --}}
                    <div class="col-md-4">
                        <label class="form-label">Reported By <span class="text-danger">*</span></label>
                        <input type="text"
                               name="ReportedBy"
                               class="form-control @error('ReportedBy') is-invalid @enderror"
                               placeholder="e.g. Moses K. / Caretaker"
                               value="{{ old('ReportedBy') }}"
                               required>
                        @error('ReportedBy')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ISSUE TYPE --}}
                    <div class="col-md-4">
                        <label class="form-label">Issue Type <span class="text-danger">*</span></label>
                        <select name="IssueType"
                                class="form-select @error('IssueType') is-invalid @enderror"
                                required>
                            <option value="">-- Select Issue Type --</option>
                            @foreach ($issuetypes as $issuetype)
                                <option value="{{ $issuetype->ID }}"
                                        {{ old('IssueType') == $issuetype->ID ? 'selected' : '' }}>
                                    {{ $issuetype->Description }}
                                </option>
                            @endforeach
                        </select>
                        @error('IssueType')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- PRIORITY --}}
                    <div class="col-md-4">
                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                        <select name="Priority"
                                class="form-select @error('Priority') is-invalid @enderror"
                                required>
                            <option value="">-- Select Priority Level --</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->ID }}"
                                        {{ old('Priority') == $priority->ID ? 'selected' : '' }}>
                                    {{ $priority->Description }}
                                </option>
                            @endforeach
                        </select>
                        @error('Priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- ISSUE DESCRIPTION --}}
                <div class="mb-3">
                    <label class="form-label">Issue Description <span class="text-danger">*</span></label>
                    <textarea name="IssueDescription"
                              class="form-control @error('IssueDescription') is-invalid @enderror"
                              rows="3"
                              placeholder="Describe the issue..."
                              required>{{ old('IssueDescription') }}</textarea>
                    @error('IssueDescription')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Document Upload -->
                <div class="mb-3">
                    <label class="form-label">Upload Relevant Documents</label>
                    <input type="file"
                           name="Document[]"
                           class="form-control @error('Document') is-invalid @enderror @error('Document.*') is-invalid @enderror"
                           accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                           multiple>
                    <small class="text-muted">Allowed: .pdf, .jpg, .jpeg, .png, .docx, .xlsx | Max size: 25MB</small>

                    @error('Document')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    @error('Document.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <a href="{{ route('maintenancerequest.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit"
                            class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        Submit Request
                    </button>
                </div>

            </div>
        </div>

    </form>
</div>

{{-- DYNAMIC SELECT DROPDOWNS --}}
<script>
const routes = {
    getBlocks: "{{ route('getblockbyproperty.maintenance', ['PropertyId' => '__ID__']) }}",
    getFloors: "{{ route('getfloorbyblock.maintenance', ['BlockId' => '__ID__']) }}",
    getUnits: "{{ route('getunitbyfloor.maintenance', ['FloorId' => '__ID__']) }}"
};

document.addEventListener('DOMContentLoaded', function () {

    const propertySelect = document.getElementById('property-select');
    const blockSelect = document.getElementById('block-select');
    const floorSelect = document.getElementById('floor-select');
    const unitSelect = document.getElementById('unit-select');

    // PROPERTY → BLOCK
    propertySelect.addEventListener('change', function () {
        const PropertyId = this.value;
        blockSelect.innerHTML = '<option value="">-- Select Block --</option>';
        floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

        if (PropertyId) {
            fetch(routes.getBlocks.replace('__ID__', PropertyId))
                .then(res => res.json())
                .then(data => {
                    data.forEach(item => {
                        blockSelect.innerHTML += `<option value="${item.Id}">${item.BlockName}</option>`;
                    });
                })
                .catch(() => alert('Failed to load blocks.'));
        }
    });

    // BLOCK → FLOOR
    blockSelect.addEventListener('change', function () {
        const BlockId = this.value;
        floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

        if (BlockId) {
            fetch(routes.getFloors.replace('__ID__', BlockId))
                .then(res => res.json())
                .then(data => {
                    data.forEach(item => {
                        floorSelect.innerHTML += `<option value="${item.Id}">${item.FloorLabel}</option>`;
                    });
                })
                .catch(() => alert('Failed to load floors.'));
        }
    });

    // FLOOR → UNIT
    floorSelect.addEventListener('change', function () {
        const FloorId = this.value;
        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

        if (FloorId) {
            fetch(routes.getUnits.replace('__ID__', FloorId))
                .then(res => res.json())
                .then(data => {
                    data.forEach(item => {
                        unitSelect.innerHTML += `<option value="${item.Id}">${item.UnitCode}</option>`;
                    });
                })
                .catch(() => alert('Failed to load units.'));
        }
    });

});
</script>

@endsection
