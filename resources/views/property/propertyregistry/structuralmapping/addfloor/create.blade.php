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
    <div class="container mt-4">
        <h4 class="fw-bold mb-3">🏬 Add Floor to Block</h4>
    <form action="{{ route('addfloor.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card shadow">
            <div class="card-header bg-light fw-bold">➕ Floor Setup</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Property<span class="text-danger">*</span></label>
                        <select name="PropertyID" id="property-select" class="form-select" required>
                            <option value="">-- Select Property --</option>
                            @foreach ($lineentries as $property)
                                <option value="{{ $property->Id }}">{{ $property->PropertyName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Block<span class="text-danger">*</span></label>
                        <select name="BlockID" id="block-select" class="form-select" required>
                            <option value="">-- Select Block --</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Floor Label<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="e.g. Ground Floor, 1st Floor"
                               name="FloorLabel" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Floor Notes</label>
                    <textarea class="form-control" rows="2" placeholder="Optional floor notes"
                              name="FloorNotes"></textarea>
                </div>
                <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">💾 Save Floor</button>
                <a href="{{ route('addfloor.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
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
