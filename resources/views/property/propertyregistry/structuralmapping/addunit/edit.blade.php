@extends('layouts.app')
@section('title', 'Units Per Floor')
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
    <h1>Edit Unit</h1>
    <form action="{{ route('addunit.update', $unit->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Select Property</label>
                    <select name="PropertyID" id="property-select" class="form-select"
                            value="{{ old('PropertyID', $unit->PropertyID) }}" required>
                        <option value="">-- Select Property --</option>
                        @foreach ($lineentries as $property)
                            <option
                                value="{{ $property->Id }}" {{ old('PropertyID', $unit->PropertyID) == $property->Id ? 'selected' : '' }}>{{ $property->PropertyName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Select Block</label>
                    <select name="BlockID" id="block-select" class="form-select" required>
                        <option value="">-- Select Block --</option>
                        @foreach ($blocks as $block)
                            <option
                                value="{{ $block->Id }}" {{ old('BlockID', $unit->BlockID) == $block->Id ? 'selected' : '' }}>{{ $block->BlockName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Select Floor</label>
                    <select name="FloorID" id="floor-select" class="form-select" required>
                        <option value="">-- Select Floor --</option>
                        @foreach ($floors as $floor)
                            <option
                                value="{{ $floor->Id }}" {{ old('FloorID', $unit->FloorID) == $floor->Id ? 'selected' : '' }}>{{ $floor->FloorLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit Code / Label</label>
                    <input type="text" class="form-control" placeholder="e.g. Unit 101" name="UnitCode"
                           value="{{ old('UnitCode', $unit->UnitCode) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit Size (sq. ft)</label>
                    <input type="number" class="form-control" placeholder="e.g. 1200" name="UnitSize"
                           value="{{ old('UnitSize', $unit->UnitSize) }}">
                </div>
            </div>

            <div class="form-check form-check-inline">
                <label class="form-label">Is Rentable?</label>
                <select class="form-select" name="IsRentable" required>
                    <option value="1" {{ old('IsRentable', $unit->IsRentable) == '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('IsRentable', $unit->IsRentable) == '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Current Status</label>
                <select class="form-select" name="CurrentStatus">
                    <option value="1" {{ old('CurrentStatus', $unit->CurrentStatus) == '1' ? 'selected' : '' }}>Vacant
                    </option>
                    <option value="0" {{ old('CurrentStatus', $unit->CurrentStatus) == '0' ? 'selected' : '' }}>
                        Occupied
                    </option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Remarks</label>
                <input type="text" class="form-control" placeholder="Optional" name="Remarks"
                       value="{{ old('Remarks', $unit->Remarks) }}">
            </div>
        </div>
        <button type="submit" class="btn btn-success">Update Unit</button>
        <a href="{{ route('addunit.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const PropertySelect = document.getElementById('property-select');
            const BlockSelect = document.getElementById('block-select');
            const FloorSelect = document.getElementById('floor-select');

            PropertySelect.addEventListener('change', function () {
                const PropertyId = this.value;
                BlockSelect.innerHTML = '<option value="">-- Select a Block --</option>';
                FloorSelect.innerHTML = '<option value="">-- Select a Floor --</option>';

                if (PropertyId) {
                    const url = `{{ route('getblockbyproperty', ':Id') }}`.replace(':Id', PropertyId);

                    fetch(url)
                        .then(response => response.json())
                        .then(blocks => {
                            blocks.forEach(block => {
                                const option = document.createElement('option');
                                option.value = block.Id;
                                option.textContent = block.BlockName;
                                BlockSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error loading property blocks:', error));
                }
            });

            BlockSelect.addEventListener('change', function () {
                const BlockId = this.value;
                FloorSelect.innerHTML = '<option value="">-- Select a Floor --</option>';

                if (BlockId) {
                    const url = `{{ route('getfloorbyblock', ':Id') }}`.replace(':Id', BlockId);

                    fetch(url)
                        .then(response => response.json())
                        .then(floors => {
                            floors.forEach(floor => {
                                const option = document.createElement('option');
                                option.value = floor.Id;
                                option.textContent = floor.FloorLabel;
                                FloorSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error loading block floors:', error));
                }
            });
        });
    </script>

@endsection
