@extends('layouts.app')
@section('title', 'Maintenance Request')
@section('content')
<div class="container mt-4">

    <form action="{{ route('maintenancerequest.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">Report Maintenance Issue</div>
    <div class="card-body">
      <!-- Property Drill-down -->
      <div class="row g-3 mb-3">
       <div class="col-md-4">
           <label class="form-label">Select Property<span class="text-danger">*</span></label>
            <select name="Property" id="property-select" class="form-select" required>
              <option value="">-- Select Property --</option>
              @foreach ($properties as $property)
                <option value="{{ $property->Id }}">{{ $property->PropertyName }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Select Block</label>
              <select name="Block" id="block-select" class="form-select">
              <option value="">-- Select Block --</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Select Floor</label>
              <select name="Floor" id="floor-select" class="form-select">
              <option value="">-- Select Floor --</option>
            </select>
          </div>

          <div class="col-md-6 mt-3">
            <label class="form-label">Select Unit</label>
              <select name="Unit" id="unit-select" class="form-select">
              <option value="">-- Select Unit --</option>
            </select>
          </div>
        </div>

      <!-- Request Details -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Reported By<span class="text-danger">*</span></label>
            <input type="text" class="form-control" placeholder="e.g. Moses K. / Caretaker" name="ReportedBy" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Issue Type<span class="text-danger">*</span></label>
            <select class="form-select" name="IssueType" required>
            <option value="">-- Select Issue Type --</option>
              @foreach ($issuetypes as $issuetype)
                <option value="{{ $issuetype->ID }}">{{ $issuetype->Description }}</option>
              @endforeach
          </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Priority<span class="text-danger">*</span></label>
            <select class="form-select" name="Priority" required>
            <option value="">-- Select Priority Level --</option>
              @foreach ($priorities as $priority)
                <option value="{{ $priority->ID }}">{{ $priority->Description }}</option>
              @endforeach
          </select>
        </div>
      </div>

      <div class="mb-3">
          <label class="form-label">Issue Description<span class="text-danger">*</span></label>
          <textarea class="form-control" rows="3" placeholder="Describe the issue..."
                    name="IssueDescription" required></textarea>
      </div>

        <!-- Document Upload -->
        <div class="mb-3">
            <label class="form-label">Upload Relevant Documents</label>
            <input type="file" name="Document[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple>
            <small class="text-muted d-block mb-1">Allowed file types: .pdf, .jpg, .jpeg, .png, .docx, .xlsx | Max size: 25MB</small>
        </div>
        <button type="submit" class="btn btn-success"
                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Submit Request
        </button>
    </form>
    </div>
  </div>
</div>
<script>
  // Define routes with placeholders
  const routes = {
      getBlocks: "{{ route('getblockbyproperty.maintenance', ['PropertyId' => '__ID__']) }}",
      getFloors: "{{ route('getfloorbyblock.maintenance', ['BlockId' => '__ID__']) }}",
      getUnits: "{{ route('getunitbyfloor.maintenance', ['FloorId' => '__ID__']) }}"
  };

  document.addEventListener('DOMContentLoaded', function () {
    const propertySelect = document.getElementById('property-select');
    const blockSelect = document.getElementById('block-select');
    const floorSelect = document.getElementById('floor-select');
    const unitSelect = document.getElementById('unit-select');

    // Property → Block
    propertySelect.addEventListener('change', function () {
        const PropertyId = this.value;

      blockSelect.innerHTML = '<option value="">-- Select Block --</option>';
      floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
      unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

        if (PropertyId) {
            fetch(routes.getBlocks.replace('__ID__', PropertyId))
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
        const BlockId = this.value;

      floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
      unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

        if (BlockId) {
            fetch(routes.getFloors.replace('__ID__', BlockId))
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
        const FloorId = this.value;

      unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

        if (FloorId) {
            fetch(routes.getUnits.replace('__ID__', FloorId))
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
