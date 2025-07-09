@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🛠️ New Maintenance Request</h4>

    <form action="{{ route('maintenancerequest.store') }}" method="POST">
        @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📋 Report Maintenance Issue</div>
    <div class="card-body">
      <!-- Property Drill-down -->
      <div class="row g-3 mb-3">
       <div class="col-md-4">
            <label class="form-label">Select Property</label>
            <select name="Property" id="property-select" class="form-select" required>
              <option value="">-- Select Property --</option>
              @foreach ($properties as $property)
                <option value="{{ $property->Id }}">{{ $property->PropertyName }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Select Block</label>
            <select name="Block" id="block-select" class="form-select" required>
              <option value="">-- Select Block --</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Select Floor</label>
            <select name="Floor" id="floor-select" class="form-select" required>
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

      <!-- Request Details -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Reported By</label>
            <input type="text" class="form-control" placeholder="e.g. Moses K. / Caretaker" name="ReportedBy">
        </div>
        <div class="col-md-4">
          <label class="form-label">Issue Type</label>
            <select class="form-select" name="IssueType">
            <option>Plumbing</option>
            <option>Electrical</option>
            <option>Cleaning</option>
            <option>Pest Control</option>
            <option>Security</option>
            <option>Other</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Priority</label>
            <select class="form-select" name="Priority">
            <option>Low</option>
            <option>Medium</option>
            <option>High</option>
            <option>Emergency</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Issue Description</label>
          <textarea class="form-control" rows="3" placeholder="Describe the issue..."
                    name="IssueDescription"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Upload Image / Document (optional)</label>
        <input type="file" class="form-control">
      </div>
        <button class="btn btn-success">💾 Submit Request</button>
    </form>
    </div>
  </div>
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
