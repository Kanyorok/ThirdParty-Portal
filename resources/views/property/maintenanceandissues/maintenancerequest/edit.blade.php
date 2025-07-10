@extends('layouts.app')
@section('title', 'Property Maintenance Request')
@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<h1>Edit Maintenance Request</h1>

<form action="{{ route('maintenancerequest.update', $maintenancerequest->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card-body">
        <div class="row g-3 mb-3">
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
            </div>

            {{-- Block --}}
            <div class="mb-3">
                <label class="form-label">Block</label>
                <select name="Block" id="block-select" class="form-select @error('Block') is-invalid @enderror">
                    <option value="{{ $maintenancerequest->Block }}" selected>{{ $maintenancerequest->block->BlockName ?? 'Current Block' }}</option>
                </select>
            </div>

            {{-- Floor --}}
            <div class="mb-3">
                <label class="form-label">Floor</label>
                <select name="Floor" id="floor-select" class="form-select @error('Floor') is-invalid @enderror">
                    <option value="{{ $maintenancerequest->Floor }}" selected>{{ $maintenancerequest->floor->FloorLabel ?? 'Current Floor' }}</option>
                </select>
            </div>

            {{-- Unit --}}
            <div class="mb-3">
                <label class="form-label">Unit</label>
                <select name="Unit" id="unit-select" class="form-select @error('Unit') is-invalid @enderror">
                    <option value="{{ $maintenancerequest->Unit }}" selected>{{ $maintenancerequest->unit->UnitCode ?? 'Current Unit' }}</option>
                </select>
            </div>

            {{-- Reported By --}}
            <div class="mb-3 col-md-4">
                <label class="form-label">Reported By</label>
                <input type="text" class="form-control" name="ReportedBy" placeholder="Optional"
                       value="{{ old('ReportedBy', $maintenancerequest->ReportedBy) }}">
            </div>

            {{-- Issue Type --}}
            <div class="mb-3 col-md-4">
                <label class="form-label">Issue Type</label>
                <input type="text" class="form-control" name="IssueType" placeholder="Optional"
                       value="{{ old('IssueType', $maintenancerequest->IssueType) }}">
            </div>

            {{-- Priority --}}
            <div class="mb-3 col-md-4">
                <label class="form-label">Priority</label>
                <input type="text" class="form-control" name="Priority" placeholder="Optional"
                       value="{{ old('Priority', $maintenancerequest->Priority) }}">
            </div>

            {{-- Issue Description --}}
            <div class="mb-3 col-md-12">
                <label class="form-label">Issue Description</label>
                <input type="text" class="form-control" name="IssueDescription" placeholder="Optional"
                       value="{{ old('IssueDescription', $maintenancerequest->IssueDescription) }}">
            </div>
        </div>

        <button type="submit" class="btn btn-success">Update Maintenance Request</button>
        <a href="{{ route('maintenancerequest.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const PropertySelect = document.getElementById('property-select');
        const BlockSelect = document.getElementById('block-select');
        const FloorSelect = document.getElementById('floor-select');
        const UnitSelect = document.getElementById('unit-select');

        PropertySelect.addEventListener('change', function () {
            const propertyId = this.value;
            BlockSelect.innerHTML = '<option value="">-- Select a Block --</option>';
            FloorSelect.innerHTML = '<option value="">-- Select a Floor --</option>';
            UnitSelect.innerHTML = '<option value="">-- Select a Unit --</option>';

            if (propertyId) {
                fetch(`{{ route('getblockbyproperty', ':id') }}`.replace(':id', propertyId))
                    .then(response => response.json())
                    .then(blocks => {
                        blocks.forEach(block => {
                            const option = document.createElement('option');
                            option.value = block.Id;
                            option.textContent = block.BlockName;
                            BlockSelect.appendChild(option);
                        });
                    });
            }
        });

        BlockSelect.addEventListener('change', function () {
            const blockId = this.value;
            FloorSelect.innerHTML = '<option value="">-- Select a Floor --</option>';
            UnitSelect.innerHTML = '<option value="">-- Select a Unit --</option>';

            if (blockId) {
                fetch(`{{ route('getfloorbyblock', ':id') }}`.replace(':id', blockId))
                    .then(response => response.json())
                    .then(floors => {
                        floors.forEach(floor => {
                            const option = document.createElement('option');
                            option.value = floor.Id;
                            option.textContent = floor.FloorLabel;
                            FloorSelect.appendChild(option);
                        });
                    });
            }
        });

        FloorSelect.addEventListener('change', function () {
            const floorId = this.value;
            UnitSelect.innerHTML = '<option value="">-- Select a Unit --</option>';

            if (floorId) {
                fetch(`{{ route('getunitbyfloor', ':id') }}`.replace(':id', floorId))
                    .then(response => response.json())
                    .then(units => {
                        units.forEach(unit => {
                            const option = document.createElement('option');
                            option.value = unit.Id;
                            option.textContent = unit.UnitCode;
                            UnitSelect.appendChild(option);
                        });
                    });
            }
        });
    });
</script>

@endsection