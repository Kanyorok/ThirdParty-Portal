@extends('layouts.app')
@section('title', 'Edit Unit')

@section('content')
<div class="container mt-4" style="max-width: 950px;">

  {{-- Validation Errors --}}
  @if ($errors->any())
    <div class="alert alert-danger shadow-sm">
      <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following issues:</h6>
      <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('addunit.update', $unit->Id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card shadow border-0 rounded-3">
      <div class="card-header bg-light fw-bold">
        <i class="bi bi-door-open"></i> Unit Details
      </div>

      <div class="card-body">

        {{-- Row 1: Property / Block / Floor --}}
        <div class="row g-2 mb-3">
          <div class="col-md-4">
            <label class="form-label fw-bold">Property <span class="text-danger">*</span></label>
            <select name="PropertyID" id="property-select" class="form-select" required>
              <option value="">-- Select Property --</option>
              @foreach ($lineentries as $property)
                <option value="{{ $property->Id }}"
                  {{ old('PropertyID', $unit->PropertyID) == $property->Id ? 'selected' : '' }}>
                  {{ $property->PropertyName ?? '-' }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">Block <span class="text-danger">*</span></label>
            <select name="BlockID" id="block-select" class="form-select" required>
              <option value="">-- Select Block --</option>
              @foreach ($blocks as $block)
                <option value="{{ $block->Id }}"
                  {{ old('BlockID', $unit->BlockID) == $block->Id ? 'selected' : '' }}>
                  {{ $block->BlockName }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">Floor <span class="text-danger">*</span></label>
            <select name="FloorID" id="floor-select" class="form-select" required>
              <option value="">-- Select Floor --</option>
              @foreach ($floors as $floor)
                <option value="{{ $floor->Id }}"
                  {{ old('FloorID', $unit->FloorID) == $floor->Id ? 'selected' : '' }}>
                  {{ $floor->FloorLabel }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Row 2: Unit Code / Unit Size --}}
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Unit Code <span class="text-danger">*</span></label>
            <input type="text" name="UnitCode" class="form-control @error('UnitCode') is-invalid @enderror"
                   placeholder="e.g. Unit 101"
                   value="{{ old('UnitCode', $unit->UnitCode) }}" required>
            @error('UnitCode')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Unit Size (sq. ft) <span class="text-danger">*</span></label>
            <input type="number" name="UnitSize" class="form-control @error('UnitSize') is-invalid @enderror"
                   placeholder="e.g. 1200"
                   value="{{ old('UnitSize', $unit->UnitSize) }}" required>
            @error('UnitSize')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- Row 3: Rentable / Status --}}
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Is Rentable? <span class="text-danger">*</span></label>
            <select class="form-select" name="IsRentable" required>
              <option value="1" {{ old('IsRentable', $unit->IsRentable) == '1' ? 'selected' : '' }}>Yes</option>
              <option value="0" {{ old('IsRentable', $unit->IsRentable) == '0' ? 'selected' : '' }}>No</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Current Status <span class="text-danger">*</span></label>
            <select class="form-select" name="CurrentStatus">
              <option value="1" {{ old('CurrentStatus', $unit->CurrentStatus) == '1' ? 'selected' : '' }}>Vacant</option>
              <option value="0" {{ old('CurrentStatus', $unit->CurrentStatus) == '0' ? 'selected' : '' }}>Occupied</option>
            </select>
          </div>
        </div>

        {{-- Row 4: Remarks --}}
        <div class="mb-3">
          <label class="form-label fw-bold">Remarks</label>
          <textarea class="form-control" rows="2" name="Remarks"
            placeholder="Optional notes">{{ old('Remarks', $unit->Remarks) }}</textarea>
        </div>

        {{-- Buttons --}}
        <div class="d-flex justify-content-end gap-2 mt-3">
          <button type="submit" class="btn btn-success"
              onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">
              <i class="bi bi-check-circle"></i> Update Unit
          </button>
          <a href="{{ route('addunit.index') }}" class="btn btn-secondary">
              <i class="bi bi-x-circle"></i> Cancel
          </a>
        </div>
      </div>
    </div>
  </form>
</div>

{{-- Dynamic Dropdowns --}}
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const PropertySelect = document.getElementById('property-select');
    const BlockSelect = document.getElementById('block-select');
    const FloorSelect = document.getElementById('floor-select');

    PropertySelect.addEventListener('change', function() {
      const PropertyId = this.value;
      BlockSelect.innerHTML = '<option value="">-- Select Block --</option>';
      FloorSelect.innerHTML = '<option value="">-- Select Floor --</option>';

      if (PropertyId) {
        const url = `{{ route('getblockbyproperty.unit', ':Id') }}`.replace(':Id', PropertyId);
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

    BlockSelect.addEventListener('change', function() {
      const BlockId = this.value;
      FloorSelect.innerHTML = '<option value="">-- Select Floor --</option>';

      if (BlockId) {
        const url = `{{ route('getfloorbyblock.unit', ':Id') }}`.replace(':Id', BlockId);
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
