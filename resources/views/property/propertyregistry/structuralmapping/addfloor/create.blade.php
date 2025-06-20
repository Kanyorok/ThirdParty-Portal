@extends('layouts.app')
@section('title', 'Add Floor to Block')

@section('content')
<div class="container mt-4">
    <h4 class="fw-bold mb-3">🏬 Add Floor to Block</h4>

    {{-- Show validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('addfloor.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card shadow">
            <div class="card-header bg-light fw-bold">➕ Floor Setup</div>
            <div class="card-body">

                {{-- Property Selection --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Property</label>
                        <select name="PropertyID" id="property-select" class="form-select" required>
                            <option value="">-- Select Property --</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->Id }}">{{ $property->PropertyName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Block Selection and Floor Label --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Block</label>
                        <select name="BlockID" id="block-select" class="form-select" required>
                            <option value="">-- Select a Block --</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Floor Label</label>
                        <input type="text" class="form-control" placeholder="e.g. Ground Floor, 1st Floor"
                               name="FloorLabel">
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-3">
                    <label class="form-label">Floor Notes</label>
                    <textarea class="form-control" rows="2" placeholder="Optional floor notes"
                              name="FloorNotes"></textarea>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-success">
                    💾 Save Floor
                </button>
                <a href="{{ route('addfloor.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

{{-- Dynamic Block Fetch Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const propertySelect = document.getElementById('property-select');
        const blockSelect = document.getElementById('block-select');

        propertySelect.addEventListener('change', function () {
            const propertyId = this.value;

            // Reset block dropdown
            blockSelect.innerHTML = '<option value="">-- Select a Block --</option>';

            if (propertyId) {
                const url = `{{ route('getblocks', ':Id') }}`.replace(':Id', propertyId);

                fetch(url)
                    .then(response => response.json())
                    .then(blocks => {
                        blocks.forEach(block => {
                            const option = document.createElement('option');
                            option.value = block.id;
                            option.textContent = block.BlockName;
                            blockSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching blocks:', error);
                    });
            }
        });
    });
</script>
@endsection
