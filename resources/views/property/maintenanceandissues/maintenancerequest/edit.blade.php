@extends('layouts.app')
@section('title', 'Edit Maintenance Request')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Edit Maintenance Request</h3>
        <a href="{{ route('maintenancerequest.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('maintenancerequest.update', $maintenancerequest->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                {{-- Request Number --}}
                <div class="mb-3">
                    <label class="form-label">Request Number</label>
                    <input type="text" class="form-control" value="{{ $maintenancerequest->RequestNumber }}" disabled>
                </div>

                {{-- Property --}}
                <div class="mb-3">
                    <label class="form-label">Property</label>
                    <select name="Property" id="property-select" class="form-select @error('Property') is-invalid @enderror">
                        <option value="">-- Select Property --</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->Id }}" {{ $property->Id == old('Property', $maintenancerequest->Property) ? 'selected' : '' }}>
                                {{ $property->PropertyName }}
                            </option>
                        @endforeach
                    </select>
                    @error('Property') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Block --}}
                <div class="mb-3">
                    <label class="form-label">Block</label>
                    <select name="Block" id="block-select" class="form-select @error('Block') is-invalid @enderror">
                        <option value="{{ $maintenancerequest->Block }}" selected>{{ $maintenancerequest->block->BlockName ?? 'Current Block' }}</option>
                    </select>
                    @error('Block') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Floor --}}
                <div class="mb-3">
                    <label class="form-label">Floor</label>
                    <select name="Floor" id="floor-select" class="form-select @error('Floor') is-invalid @enderror">
                        <option value="{{ $maintenancerequest->Floor }}" selected>{{ $maintenancerequest->floor->FloorLabel ?? 'Current Floor' }}</option>
                    </select>
                    @error('Floor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Unit --}}
                <div class="mb-3">
                    <label class="form-label">Unit</label>
                    <select name="Unit" id="unit-select" class="form-select @error('Unit') is-invalid @enderror">
                        <option value="{{ $maintenancerequest->Unit }}" selected>{{ $maintenancerequest->unit->UnitCode ?? 'Current Unit' }}</option>
                    </select>
                    @error('Unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Reported By / Issue / Priority --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Reported By</label>
                        <input type="text" class="form-control" name="ReportedBy" placeholder="Optional"
                            value="{{ old('ReportedBy', $maintenancerequest->ReportedBy) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Issue Type</label>
                        <input type="text" class="form-control" name="IssueType" placeholder="Optional"
                            value="{{ old('IssueType', $maintenancerequest->IssueType) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Priority</label>
                        <input type="text" class="form-control" name="Priority" placeholder="Optional"
                            value="{{ old('Priority', $maintenancerequest->Priority) }}">
                    </div>
                </div>

                {{-- Issue Description --}}
                <div class="mb-3">
                    <label class="form-label">Issue Description</label>
                    <textarea name="IssueDescription" class="form-control" rows="3" placeholder="Optional">{{ old('IssueDescription', $maintenancerequest->IssueDescription) }}</textarea>
                </div>

            </div>

            <div class="card-footer bg-light d-flex justify-content-between">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Update Maintenance Request
                </button>
                <a href="{{ route('maintenancerequest.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<script>
    const routes = {
        getBlocks: "{{ route('getblockbyproperty', ['PropertyId' => '__ID__']) }}",
        getFloors: "{{ route('getfloorbyblock', ['BlockId' => '__ID__']) }}",
        getUnits: "{{ route('getunitbyfloor', ['FloorId' => '__ID__']) }}"
    };

    document.addEventListener('DOMContentLoaded', function () {
        const propertySelect = document.getElementById('property-select');
        const blockSelect = document.getElementById('block-select');
        const floorSelect = document.getElementById('floor-select');
        const unitSelect = document.getElementById('unit-select');

        propertySelect.addEventListener('change', function () {
            const propertyId = this.value;
            blockSelect.innerHTML = '<option value="">-- Select Block --</option>';
            floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
            unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

            if (propertyId) {
                fetch(routes.getBlocks.replace('__ID__', propertyId))
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(block => {
                            const option = document.createElement('option');
                            option.value = block.Id;
                            option.textContent = block.BlockName;
                            blockSelect.appendChild(option);
                        });
                    });
            }
        });

        blockSelect.addEventListener('change', function () {
            const blockId = this.value;
            floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
            unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

            if (blockId) {
                fetch(routes.getFloors.replace('__ID__', blockId))
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(floor => {
                            const option = document.createElement('option');
                            option.value = floor.Id;
                            option.textContent = floor.FloorLabel;
                            floorSelect.appendChild(option);
                        });
                    });
            }
        });

        floorSelect.addEventListener('change', function () {
            const floorId = this.value;
            unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

            if (floorId) {
                fetch(routes.getUnits.replace('__ID__', floorId))
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(unit => {
                            const option = document.createElement('option');
                            option.value = unit.Id;
                            option.textContent = unit.UnitCode;
                            unitSelect.appendChild(option);
                        });
                    });
            }
        });
    });
</script>
@endsection
