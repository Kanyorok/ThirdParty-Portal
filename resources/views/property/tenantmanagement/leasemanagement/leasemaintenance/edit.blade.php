@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Edit Lease Agreement')

@section('content')
  <div class="container mt-4">

    <form method="POST" action="{{ route('addlease.update', $newlease->Id) }}" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="card shadow">
        <div class="card-header bg-light fw-bold">Lease Details</div>
        <div class="card-body">

          <!-- Lease Number -->
        <div class="row g-3 mb-3">
         <div class="col-md-4">
            <label class="form-label">Lease Number</label>
            <input type="text" class="form-control" value="{{ $newlease->LeaseNumber }}" disabled>
          </div>

          <!-- Tenant -->
         <div class="col-md-4">
            <label class="form-label">Tenant</label>
            <input type="text" class="form-control" value="{{ $newlease->tenant->TenantName ?? '' }}" disabled>
            <input type="hidden" name="Tenant" value="{{ $newlease->Tenant }}">
          </div>

          <!-- Property Hierarchy -->
            <div class="col-md-4">
              <label class="form-label">Property</label>
              <select name="PropertyID" id="property-select" class="form-select">
                <option value="">-- Select Property --</option>
                @foreach($properties as $property)
                  <option value="{{ $property->Id }}" {{ $property->Id == old('PropertyID', $newlease->PropertyID) ? 'selected' : '' }}>
                    {{ $property->PropertyName }}
                  </option>
                @endforeach
              </select>
            </div>
         <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Block</label>
              <select name="BlockID" id="block-select" class="form-select">
                <option value="{{ $newlease->BlockID }}" selected>{{ $newlease->block->BlockName ?? 'Current Block' }}</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Floor</label>
              <select name="FloorID" id="floor-select" class="form-select">
                <option value="{{ $newlease->FloorID }}" selected>{{ $newlease->floor->FloorLabel ?? 'Current Floor' }}</option>
              </select>
            </div>
                        <div class="col-md-4">
              <label class="form-label">Unit</label>
              <select name="Unit" id="unit-select" class="form-select">
                <option value="{{ $newlease->Unit }}" selected>{{ $newlease->unit->UnitCode ?? 'Current Unit' }}</option>
              </select>
            </div>
          </div>

          <!-- Dates -->
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Start Date</label>
              <input type="date" class="form-control" name="StartDate"
                     value="{{ old('StartDate', Carbon::parse($newlease->StartDate)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">End Date</label>
              <input type="date" class="form-control" name="EndDate"
                     value="{{ old('EndDate', Carbon::parse($newlease->EndDate)->format('Y-m-d')) }}">
            </div>
          </div>

          <!-- Payment Frequency -->
          <div class="mb-3">
            <label class="form-label">Payment Frequency</label>
            <select name="PaymentFrequency" class="form-select">
              @foreach($codes as $code)
                <option value="{{ $code->ID }}" {{ $code->ID == old('PaymentFrequency', $newlease->PaymentFrequency) ? 'selected' : '' }}>
                  {{ $code->Description }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Financials -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Monthly Rent (KES)</label>
              <input type="number" name="MonthlyRent" class="form-control"
                     value="{{ old('MonthlyRent', $newlease->MonthlyRent) }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Deposit (KES)</label>
              <input type="number" name="Deposit" class="form-control"
                     value="{{ old('Deposit', $newlease->Deposit) }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Service Charge (KES)</label>
              <input type="number" name="ServiceCharge" class="form-control"
                     value="{{ old('ServiceCharge', $newlease->ServiceCharge) }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Parking Fee (KES)</label>
              <input type="number" name="ParkingFee" class="form-control"
                     value="{{ old('ParkingFee', $newlease->ParkingFee) }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Other Charges (KES)</label>
              <input type="number" name="OtherCharges" class="form-control"
                     value="{{ old('OtherCharges', $newlease->OtherCharges) }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Due Day<span class="text-danger">*</span></label>
              <input type="number" name="DueDay" class="form-control"
                     min="1" max="28"
                     value="{{ old('DueDay', $newlease->DueDay) }}">
              <small class="text-muted">Must be between 1 and 28</small>
            </div>
          </div>

          <!-- Terms -->
          <div class="mb-3">
            <label class="form-label">Special Terms</label>
            <textarea name="SpecialTerms" class="form-control" rows="3">{{ old('SpecialTerms', $newlease->SpecialTerms) }}</textarea>
          </div>

          <!-- Documents -->
          <div class="mb-3">
            <label class="form-label">Upload Lease Document</label>
              <div class="p-3 border rounded bg-light text-dark">
                @forelse($newlease->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                    {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                @empty
                    <span>No documents attached.</span>
                @endforelse
            </div>
            <input type="file" name="Document[]" class="form-control" multiple>
            <small class="text-muted">e.g. upload Lease Document</small>
          </div>

          <!-- Buttons -->
          <button type="submit" class="btn btn-success"
                  onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">Update Lease</button>
          <a href="{{ route('addlease.index') }}" class="btn btn-outline-secondary">Cancel</a>

        </div>
      </div>
    </form>
  </div>

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

@section('scripts')
 @include('snippets.actions.preview-files')
@endsection
