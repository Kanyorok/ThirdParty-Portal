@extends('layouts.app')

@section('title', 'Edit Lease')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Edit Lease Agreement</h3>
        <a href="{{ route('addlease.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
    </div>

    <form method="POST" action="{{ route('addlease.update', $newlease->Id) }}">
        @csrf
        @method('PUT')

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                {{-- Lease Number (View Only) --}}
                <div class="mb-3">
                    <label class="form-label">Lease Number</label>
                    <input type="text" class="form-control" value="{{ $newlease->LeaseNumber }}" disabled>
                </div>

                {{-- Tenant --}}
                <div class="mb-3">
                    <label class="form-label">Tenant</label>
                    <input type="text" class="form-control" value="{{ $newlease->tenant->TenantName ?? '' }}" disabled>
                    <input type="hidden" name="Tenant" value="{{ $newlease->Tenant }}">
                </div>

                {{-- Property --}}
                <div class="mb-3">
                    <label class="form-label">Property</label>
                    <select name="PropertyID" id="property-select" class="form-select @error('PropertyID') is-invalid @enderror">
                        <option value="">-- Select Property --</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->Id }}" {{ $property->Id == old('PropertyID', $newlease->PropertyID) ? 'selected' : '' }}>
                                {{ $property->PropertyName }}
                            </option>
                        @endforeach
                    </select>
                    @error('PropertyID') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Block --}}
                <div class="mb-3">
                    <label class="form-label">Block</label>
                    <select name="BlockID" id="block-select" class="form-select @error('BlockID') is-invalid @enderror">
                        <option value="{{ $newlease->BlockID }}" selected>{{ $newlease->block->BlockName ?? 'Current Block' }}</option>
                    </select>
                    @error('BlockID') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Floor --}}
                <div class="mb-3">
                    <label class="form-label">Floor</label>
                    <select name="FloorID" id="floor-select" class="form-select @error('FloorID') is-invalid @enderror">
                        <option value="{{ $newlease->FloorID }}" selected>{{ $newlease->floor->FloorLabel ?? 'Current Floor' }}</option>
                    </select>
                    @error('FloorID') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Unit --}}
                <div class="mb-3">
                    <label class="form-label">Unit</label>
                    <select name="Unit" id="unit-select" class="form-select @error('Unit') is-invalid @enderror">
                        <option value="{{ $newlease->Unit }}" selected>{{ $newlease->unit->UnitCode ?? 'Current Unit' }}</option>
                    </select>
                    @error('Unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Start and End Dates --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="text" id="startDateDisplay" class="form-control flatpickr @error('StartDate') is-invalid @enderror" value="{{ old('StartDate', \Carbon\Carbon::parse($newlease->StartDate)->format('d/m/Y')) }}">
                        <input type="hidden" name="StartDate" id="startDate" value="{{ old('StartDate', \Carbon\Carbon::parse($newlease->StartDate)->format('Y-m-d')) }}">
                        @error('StartDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Date</label>
                        <input type="text" id="endDateDisplay" class="form-control flatpickr @error('EndDate') is-invalid @enderror" value="{{ old('EndDate', \Carbon\Carbon::parse($newlease->EndDate)->format('d/m/Y')) }}">
                        <input type="hidden" name="EndDate" id="endDate" value="{{ old('EndDate', \Carbon\Carbon::parse($newlease->EndDate)->format('Y-m-d')) }}">
                        @error('EndDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Payment Frequency --}}
                <div class="mb-3">
                    <label class="form-label">Payment Frequency</label>
                    <select name="PaymentFrequency" class="form-select @error('PaymentFrequency') is-invalid @enderror">
                        @foreach($codes as $code)
                            <option value="{{ $code->ID }}" {{ $code->ID == old('PaymentFrequency', $newlease->PaymentFrequency) ? 'selected' : '' }}>
                                {{ $code->Description }}
                            </option>
                        @endforeach
                    </select>
                    @error('PaymentFrequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Rent & Deposit --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Monthly Rent (KES)</label>
                        <input type="number" name="MonthlyRent" step="0.01" class="form-control" value="{{ old('MonthlyRent', $newlease->MonthlyRent) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Deposit (KES)</label>
                        <input type="number" name="Deposit" step="0.01" class="form-control" value="{{ old('Deposit', $newlease->Deposit) }}">
                    </div>
                                        <div class="col-md-6 mb-3">
                        <label class="form-label">Service Charge (KES)</label>
                        <input type="number" name="ServiceCharge" step="0.01" class="form-control" value="{{ old('ServiceCharge', $newlease->ServiceCharge) }}">
                    </div>
                                        <div class="col-md-6 mb-3">
                        <label class="form-label">Parking Fee (KES)</label>
                        <input type="number" name="ParkingFee" step="0.01" class="form-control" value="{{ old('ParkingFee', $newlease->ParkingFee) }}">
                    </div>
                                        <div class="col-md-6 mb-3">
                        <label class="form-label">Other Charges (KES)</label>
                        <input type="number" name="OtherCharges" step="0.01" class="form-control" value="{{ old('OtherCharges', $newlease->OtherCharges) }}">
                    </div>
                </div>

                {{-- Due Day --}}
                <div class="mb-3">
                    <label class="form-label">Due Day</label>
                    <input type="number" name="DueDay" class="form-control" value="{{ old('DueDay', $newlease->DueDay) }}">
                </div>

                {{-- Special Terms --}}
                <div class="mb-3">
                    <label class="form-label">Special Terms</label>
                    <textarea name="SpecialTerms" class="form-control" rows="3">{{ old('SpecialTerms', $newlease->SpecialTerms) }}</textarea>
                </div>
            </div>

            <div class="card-footer bg-light d-flex justify-content-between">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Update Lease
                </button>
                <a href="{{ route('addlease.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <!-- Flatpickr CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#startDateDisplay", {
            dateFormat: "d/m/Y",
            allowInput: true,
            onChange: function(selectedDates) {
                if (selectedDates.length) {
                    document.getElementById('startDate').value = selectedDates[0].toISOString().split('T')[0];
                }
            }
        });

        flatpickr("#endDateDisplay", {
            dateFormat: "d/m/Y",
            allowInput: true,
            onChange: function(selectedDates) {
                if (selectedDates.length) {
                    document.getElementById('endDate').value = selectedDates[0].toISOString().split('T')[0];
                }
            }
        });
    </script>
@endpush

{{-- Dependent Dropdown Scripts --}}
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
