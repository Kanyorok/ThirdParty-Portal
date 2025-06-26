@extends('layouts.app')

@section('title', 'New Lease Agreement')

@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📄 New Lease Agreement</h4>

  <form method="POST" action="{{ route('addlease.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="card shadow">
      <div class="card-header bg-light fw-bold">📝 Lease Details</div>
      <div class="card-body">

        <!-- Tenant Selection -->
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Select Tenant</label>
            <select name="Tenant" class="form-select" required>
              <option value="">-- Select Tenant --</option>
              @foreach ($newtenants as $newtenant)
                <option value="{{ $newtenant->Tenant }}">{{ $newtenant->tenant->TenantName }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- Property Hierarchy -->
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Select Property</label>
            <select name="PropertyID" id="property-select" class="form-select" required>
              <option value="">-- Select Property --</option>
              @foreach ($properties as $property)
                <option value="{{ $property->Id }}">{{ $property->PropertyName }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Select Block</label>
            <select name="BlockID" id="block-select" class="form-select" required>
              <option value="">-- Select Block --</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Select Floor</label>
            <select name="FloorID" id="floor-select" class="form-select" required>
              <option value="">-- Select Floor --</option>
            </select>
          </div>

          <div class="col-md-6 mt-3">
            <label class="form-label">Select Unit</label>
            <select name="Unit" id="unit-select" class="form-select" required>
              <option value="">-- Select Unit --</option>
            </select>
          </div>
        </div>

        <!-- Lease Duration -->
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" name="StartDate" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" name="EndDate" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Payment Frequency</label>
            <select class="form-select" name="PaymentFrequency" required>
              <option value="">-- Select Frequency --</option>
              @foreach ($codes as $code)
                <option value="{{ $code->ID }}">{{ $code->Description }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- Financials -->
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Monthly Rent (KES)</label>
            <input type="number" class="form-control" placeholder="e.g. 25000" name="MonthlyRent" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Deposit (KES)</label>
            <input type="number" class="form-control" placeholder="e.g. 25000" name="Deposit" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Due Day</label>
            <input type="number" class="form-control" placeholder="e.g. 5" name="DueDay" required>
          </div>
        </div>

        <!-- Terms and Document -->
        <div class="mb-3">
          <label class="form-label">Special Terms & Conditions</label>
          <textarea class="form-control" rows="3" placeholder="Optional terms or notes..." name="SpecialTerms"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Upload Lease Document</label>
          <input type="file" class="form-control" name="LeaseDocument" accept=".pdf,.docx">
        </div>

        <button type="submit" class="btn btn-success">Save Lease</button>

      </div>
    </div>
  </form>
</div>

<script>
  // Define routes with placeholders
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

    // Property → Block
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
          })
          .catch(err => {
            console.error('Error loading blocks:', err);
            alert('Failed to load blocks.');
          });
      }
    });

    // Block → Floor
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
          })
          .catch(err => {
            console.error('Error loading floors:', err);
            alert('Failed to load floors.');
          });
      }
    });

    // Floor → Unit
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
          })
          .catch(err => {
            console.error('Error loading units:', err);
            alert('Failed to load units.');
          });
      }
    });
  });
</script>



@endsection
