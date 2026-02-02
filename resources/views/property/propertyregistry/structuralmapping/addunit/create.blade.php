@extends('layouts.app')
@section('title', 'Add Unit to Floor')

@section('content')

{{-- GLOBAL VALIDATION ERRORS --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-4">
    <form action="{{ route('addunit.store') }}" method="POST">
        @csrf

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Unit Setup</div>

            <div class="card-body">

                {{-- Row 1: Property / Block / Floor --}}
                <div class="row g-2 mb-3">

                    {{-- Property --}}
                    <div class="col-md-4">
                        <label class="form-label">
                            Property <span class="text-danger">*</span>
                        </label>
                        <select name="PropertyID" id="property-select" class="form-select" required>
                            <option value="">-- Select Property --</option>
                            @foreach ($lineentries as $property)
                                <option value="{{ $property->Id }}"
                                    {{ old('PropertyID') == $property->Id ? 'selected' : '' }}>
                                    {{ $property->PropertyName ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Block --}}
                    <div class="col-md-4">
                        <label class="form-label">
                            Block <span class="text-danger">*</span>
                        </label>
                        <select name="BlockID" id="block-select" class="form-select" required>
                            <option value="">-- Select Block --</option>
                        </select>
                    </div>

                    {{-- Floor --}}
                    <div class="col-md-4">
                        <label class="form-label">
                            Floor <span class="text-danger">*</span>
                        </label>
                        <select name="FloorID" id="floor-select" class="form-select" required>
                            <option value="">-- Select Floor --</option>
                        </select>
                    </div>

                </div>

                {{-- Row 2: Unit Code / Unit Size --}}
                <div class="row g-2 mb-3">

                    <div class="col-md-6">
                        <label class="form-label">
                            Unit Code <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               name="UnitCode"
                               placeholder="e.g. Unit 101"
                               value="{{ old('UnitCode') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Unit Size (sq. ft) <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               class="form-control"
                               name="UnitSize"
                               min="1"
                               placeholder="e.g. 1200"
                               value="{{ old('UnitSize') }}">
                    </div>

                </div>

                {{-- Row 3: Rentable / Status --}}
                <div class="row g-2 mb-3">

                    <div class="col-md-6">
                        <label class="form-label">
                            Is Rentable? <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="IsRentable" required>
                            <option value="1" {{ old('IsRentable', '1') == '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ old('IsRentable') == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Current Status <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="CurrentStatus" required>
                            <option value="1" {{ old('CurrentStatus', '1') == '1' ? 'selected' : '' }}>Vacant</option>
                            <option value="0" {{ old('CurrentStatus') == '0' ? 'selected' : '' }}>Occupied</option>
                        </select>
                    </div>

                </div>

                {{-- Row 4: Remarks --}}
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control"
                              rows="2"
                              name="Remarks">{{ old('Remarks') }}</textarea>
                </div>

                {{-- Buttons --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('addunit.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit"
                            class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        Save Unit
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- OLD VALUES FOR JS --}}
<script>
    const oldPropertyID = "{{ old('PropertyID') }}";
    const oldBlockID = "{{ old('BlockID') }}";
    const oldFloorID = "{{ old('FloorID') }}";
</script>

{{-- JS --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const PropertySelect = document.getElementById('property-select');
    const BlockSelect = document.getElementById('block-select');
    const FloorSelect = document.getElementById('floor-select');

    function loadBlocks(propertyId, selectedBlockId = null) {
        if (!propertyId) return;

        const url = `{{ route('getblockbyproperty.unit', ':Id') }}`.replace(':Id', propertyId);

        fetch(url)
            .then(res => res.json())
            .then(blocks => {
                BlockSelect.innerHTML = '<option value="">-- Select Block --</option>';
                FloorSelect.innerHTML = '<option value="">-- Select Floor --</option>';

                blocks.forEach(block => {
                    const option = document.createElement('option');
                    option.value = block.Id;
                    option.textContent = block.BlockName;
                    if (block.Id == selectedBlockId) option.selected = true;
                    BlockSelect.appendChild(option);
                });

                if (selectedBlockId && oldFloorID) {
                    loadFloors(selectedBlockId, oldFloorID);
                }
            })
            .catch(err => console.error('Error loading blocks:', err));
    }

    function loadFloors(blockId, selectedFloorId = null) {
        if (!blockId) return;

        const url = `{{ route('getfloorbyblock.unit', ':Id') }}`.replace(':Id', blockId);

        fetch(url)
            .then(res => res.json())
            .then(floors => {
                FloorSelect.innerHTML = '<option value="">-- Select Floor --</option>';

                floors.forEach(floor => {
                    const option = document.createElement('option');
                    option.value = floor.Id;
                    option.textContent = floor.FloorLabel;
                    if (floor.Id == selectedFloorId) option.selected = true;
                    FloorSelect.appendChild(option);
                });
            })
            .catch(err => console.error('Error loading floors:', err));
    }

    // Restore old selections
    if (oldPropertyID) {
        PropertySelect.value = oldPropertyID;
        loadBlocks(oldPropertyID, oldBlockID);
    }

    PropertySelect.addEventListener('change', function () {
        loadBlocks(this.value);
    });

    BlockSelect.addEventListener('change', function () {
        loadFloors(this.value);
    });

});
</script>

@endsection
