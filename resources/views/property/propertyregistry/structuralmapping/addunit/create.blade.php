@extends('layouts.app')
@section('title', 'Property Unit Creation')
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
  <h4 class="fw-bold mb-3">Add Unit to Floor</h4>

    <form action="{{ route('addunit.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">Unit Setup</div>
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
        <div class="col-md-4">
          <label class="form-label">Select Floor<span class="text-danger">*</span></label>
            <select name="FloorID" id="floor-select" class="form-select" required>
            <option value="">-- Select Floor --</option>
            </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Unit Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control" placeholder="e.g. Unit 101" name="UnitCode">
        </div>
        <div class="col-md-4">
          <label class="form-label">Unit Size (sq. ft)<span class="text-danger">*</span></label>
            <input type="number" class="form-control" placeholder="e.g. 1200" name="UnitSize">
        </div>
      </div>

        <div class="form-check form-check-inline">
          <label class="form-label">Is Rentable?<span class="text-danger">*</span></label>
          <select class="form-select" name="IsRentable" required>
            <option value="1" {{ old('IsRentable') == '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('IsRentable') == '0' ? 'selected' : '' }}>No</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Current Status<span class="text-danger">*</span></label>
            <select class="form-select" name="CurrentStatus">
            <option value="1" {{ old('CurrentStatus') == '1' ? 'selected' : '' }}>Vacant</option>
            <option value="0" {{ old('CurrentStatus') == '0' ? 'selected' : '' }}>Occupied</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Remarks</label>
            <input type="text" class="form-control" placeholder="Optional" name="Remarks">
        </div>
      </div>
      <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Save Unit</button>
    </div>
  </div>
</div>
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
