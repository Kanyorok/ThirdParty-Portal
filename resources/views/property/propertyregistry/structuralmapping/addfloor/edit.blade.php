@extends('layouts.app')
@section('title', 'Floors Per Block')
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
    <form action="{{ route('addfloor.update', $floor->Id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Floor Setup</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Property</label>
                        <select name="PropertyID" id="property-select" class="form-select" required>
                            <option value="">-- Select Property --</option>
                            @foreach ($lineentries as $property)
                                <option
                                    value="{{ $property->Id }}" {{ old('PropertyID', $floor->PropertyID) == $property->Id ? 'selected' : '' }}>{{ $property->PropertyName }}</option>
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
                                    value="{{ $block->Id }}" {{ old('BlockID', $floor->BlockID) == $block->Id ? 'selected' : '' }}>{{ $block->BlockName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="FloorLabel" class="form-label">Floor Name</label>
                        <input type="text" name="FloorLabel" class="form-control"
                               value="{{ old('FloorLabel', $floor->FloorLabel) }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="FloorNotes" class="form-label">Notes</label>
                        <textarea name="FloorNotes" class="form-control"
                                  rows="4">{{ old('FloorNotes', $floor->FloorNotes) }}</textarea>
                    </div>
    </form>
    </div>
    <button type="submit" class="btn btn-success">Update Floor</button>
    <a href="{{ route('addfloor.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const PropertySelect = document.getElementById('property-select');
            const BlockSelect = document.getElementById('block-select');

            PropertySelect.addEventListener('change', function () {
                const PropertyId = this.value;

                // Reset Block dropdown
                BlockSelect.innerHTML = '<option value="">-- Select a Block --</option>';

                if (PropertyId) {
                    // Construct the URL from the named route
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
        });
    </script>

@endsection
