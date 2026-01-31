@extends('layouts.app')
@section('title', 'Add Floor to Block')

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
    <form action="{{ route('addfloor.store') }}" method="POST">
        @csrf

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Floor Setup</div>

            <div class="card-body">

                {{-- Row 1: Property --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Select Property <span class="text-danger">*</span>
                        </label>
                        <select name="PropertyID" id="property-select" class="form-select" required>
                            <option value="">-- Select Property --</option>
                            @foreach ($lineentries as $property)
                                <option value="{{ $property->Id }}"
                                    {{ old('PropertyID') == $property->Id ? 'selected' : '' }}>
                                    {{ $property->PropertyName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 2: Block / Floor Label --}}
                <div class="row g-3 mb-3">

                    <div class="col-md-6">
                        <label class="form-label">
                            Select Block <span class="text-danger">*</span>
                        </label>
                        <select name="BlockID" id="block-select" class="form-select" required>
                            <option value="">-- Select Block --</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Floor Label <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               name="FloorLabel"
                               placeholder="e.g. Ground Floor, 1st Floor"
                               value="{{ old('FloorLabel') }}"
                               required>
                    </div>

                </div>

                {{-- Floor Notes --}}
                <div class="mb-3">
                    <label class="form-label">Floor Notes</label>
                    <textarea class="form-control"
                              rows="2"
                              name="FloorNotes"
                              placeholder="Optional floor notes">{{ old('FloorNotes') }}</textarea>
                </div>

                {{-- Buttons --}}
                <div class="d-flex justify-content-between gap-2">
                    <a href="{{ route('addfloor.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit"
                            class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        Save Floor
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
</script>

{{-- JS --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const PropertySelect = document.getElementById('property-select');
    const BlockSelect = document.getElementById('block-select');

    function loadBlocks(propertyId, selectedBlockId = null) {
        if (!propertyId) return;

        const url = `{{ route('getblocksforfloor', ':Id') }}`.replace(':Id', propertyId);

        fetch(url)
            .then(res => res.json())
            .then(blocks => {
                BlockSelect.innerHTML = '<option value="">-- Select Block --</option>';

                blocks.forEach(block => {
                    const option = document.createElement('option');
                    option.value = block.Id;
                    option.textContent = block.BlockName;
                    if (block.Id == selectedBlockId) option.selected = true;
                    BlockSelect.appendChild(option);
                });
            })
            .catch(err => console.error('Error loading blocks:', err));
    }

    // Restore old selection after validation error
    if (oldPropertyID) {
        PropertySelect.value = oldPropertyID;
        loadBlocks(oldPropertyID, oldBlockID);
    }

    PropertySelect.addEventListener('change', function () {
        loadBlocks(this.value);
    });

});
</script>

@endsection
