@extends('layouts.app')
@section('title', 'Maintenance Request')

@section('content')

{{-- ================= STYLES ================= --}}
<style>
    .section-title {
        color: #000;
        font-weight: 600;
        font-size: .9rem;
        padding-bottom: .35rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
</style>

<div class="container mt-4" style="max-width: 1100px;">

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

        <div class="card shadow-lg border-0 rounded-4">

            {{-- Header --}}
            <div class="card-header bg-primary border-bottom rounded-top-4">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-tools me-2"></i>
                    Report Maintenance Issue
                </h5>
            </div>

            <div class="card-body p-4">

                {{-- ================= LOCATION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Property Location</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Property <span class="text-danger">*</span>
                            </label>
                            <select name="Property"
                                    id="property-select"
                                    class="form-select form-select-sm @error('Property') is-invalid @enderror"
                                    required>
                                <option value="">-- Select Property --</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->Id }}"
                                            {{ old('Property') == $property->Id ? 'selected' : '' }}>
                                        {{ $property->PropertyName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('Property') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">Block</label>
                            <select name="Block"
                                    id="block-select"
                                    class="form-select form-select-sm @error('Block') is-invalid @enderror">
                                <option value="">-- Select Block --</option>
                            </select>
                            @error('Block') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">Floor</label>
                            <select name="Floor"
                                    id="floor-select"
                                    class="form-select form-select-sm @error('Floor') is-invalid @enderror">
                                <option value="">-- Select Floor --</option>
                            </select>
                            @error('Floor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">Unit</label>
                            <select name="Unit"
                                    id="unit-select"
                                    class="form-select form-select-sm @error('Unit') is-invalid @enderror">
                                <option value="">-- Select Unit --</option>
                            </select>
                            @error('Unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- ================= REQUEST DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Request Details</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Reported By <span class="text-danger">*</span>
                            </label>
                            <select name="ReportedBy"
                                    class="form-select form-select-sm @error('ReportedBy') is-invalid @enderror"
                                    required>
                                <option value="">-- Select User --</option>
                                @foreach ($Users as $user)
                                    <option value="{{ $user->Id }}"
                                            {{ old('ReportedBy') == $user->Id ? 'selected' : '' }}>
                                        {{ $user->ThirdPartyName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ReportedBy') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Issue Type <span class="text-danger">*</span>
                            </label>
                            <select name="IssueType"
                                    class="form-select form-select-sm @error('IssueType') is-invalid @enderror"
                                    required>
                                <option value="">-- Select Issue Type --</option>
                                @foreach ($issuetypes as $issuetype)
                                    <option value="{{ $issuetype->ID }}"
                                            {{ old('IssueType') == $issuetype->ID ? 'selected' : '' }}>
                                        {{ $issuetype->Description }}
                                    </option>
                                @endforeach
                            </select>
                            @error('IssueType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Priority <span class="text-danger">*</span>
                            </label>
                            <select name="Priority"
                                    class="form-select form-select-sm @error('Priority') is-invalid @enderror"
                                    required>
                                <option value="">-- Select Priority --</option>
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority->ID }}"
                                            {{ old('Priority') == $priority->ID ? 'selected' : '' }}>
                                        {{ $priority->Description }}
                                    </option>
                                @endforeach
                            </select>
                            @error('Priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- ================= DESCRIPTION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Issue Description<span class="text-danger">*</span></h6>

                    <textarea name="IssueDescription"
                              class="form-control form-control-sm @error('IssueDescription') is-invalid @enderror"
                              rows="3"
                              placeholder="Describe the issue..."
                              required>{{ old('IssueDescription') }}</textarea>
                    @error('IssueDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- ================= ATTACHMENTS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Attachments</h6>

                    <input type="file"
                           name="Document[]"
                           class="form-control form-control-sm @error('Document') is-invalid @enderror @error('Document.*') is-invalid @enderror"
                           accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                           multiple>

                    <small class="text-muted">
                        Allowed: .pdf, .jpg, .jpeg, .png, .docx, .xlsx | Max size: 25MB
                    </small>

                    @error('Document') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('Document.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('maintenancerequest.index') }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Cancel
                    </a>

                    <button type="submit"
                            class="btn btn-sm btn-success px-4"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        <i class="bi bi-check-circle me-1"></i>
                        Submit Request
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- ================= DYNAMIC SELECTS (unchanged) ================= --}}
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

    propertySelect.addEventListener('change', function () {
        const PropertyId = this.value;
        blockSelect.innerHTML = '<option value="">-- Select Block --</option>';
        floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

        if (PropertyId) {
            fetch(routes.getBlocks.replace('__ID__', PropertyId))
                .then(res => res.json())
                .then(data => data.forEach(item =>
                    blockSelect.innerHTML += `<option value="${item.Id}">${item.BlockName}</option>`
                ));
        }
    });

    blockSelect.addEventListener('change', function () {
        const BlockId = this.value;
        floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

        if (BlockId) {
            fetch(routes.getFloors.replace('__ID__', BlockId))
                .then(res => res.json())
                .then(data => data.forEach(item =>
                    floorSelect.innerHTML += `<option value="${item.Id}">${item.FloorLabel}</option>`
                ));
        }
    });

    floorSelect.addEventListener('change', function () {
        const FloorId = this.value;
        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

        if (FloorId) {
            fetch(routes.getUnits.replace('__ID__', FloorId))
                .then(res => res.json())
                .then(data => data.forEach(item =>
                    unitSelect.innerHTML += `<option value="${item.Id}">${item.UnitCode}</option>`
                ));
        }
    });

});
</script>
@endsection
