@extends('layouts.app')
@section('title', 'Complete Maintenance')

@section('content')
<div class="container mt-4">
  <form action="{{ route('workcompletion.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card shadow">
      <div class="card-header bg-light fw-bold">Work Execution & Resolution</div>
      <div class="card-body">

        <!-- Request Selection -->
        <div class="row g-3 mb-3">
          <div class="col-md-6">
              <label class="form-label">Select Maintenance Request<span class="text-danger">*</span></label>
            <select id="request-select" name="RequestNumber" class="form-select" required>
              <option value="">-- Select Request --</option>
              @foreach ($assignments as $assignment)
                <option
                  value="{{ $assignment->Id }}"
                  data-property="{{ $assignment->request->property->PropertyName ?? '-' }}"
                  data-block="{{ $assignment->request->block->BlockName ?? '-' }}"
                  data-floor="{{ $assignment->request->floor->FloorLabel ?? '-' }}"
                  data-unit="{{ $assignment->request->unit->UnitCode ?? '-' }}">
                    {{ $assignment->request->RequestNumber}}
                </option>
              @endforeach
            </select>
          </div>
        <!-- Auto-filled Property Info -->
            <div class="col-md-6">
            <label class="form-label">Property</label>
            <input type="text" id="property-display" class="form-control" readonly>
            <input type="hidden" name="Property" id="property-id" value="{{ old('Property') }}">
          </div>
        </div>

          <div class="row g-3 mb-3">
              <div class="col-md-4">
            <label class="form-label">Block</label>
            <input type="text" id="block-display" class="form-control" readonly>
            <input type="hidden" name="Block" id="block-id" value="{{ old('Block') }}">
          </div>
              <div class="col-md-4">
            <label class="form-label">Floor</label>
            <input type="text" id="floor-display" class="form-control" readonly>
            <input type="hidden" name="Floor" id="floor-id" value="{{ old('Floor') }}">
          </div>
              <div class="col-md-4">
            <label class="form-label">Unit</label>
            <input type="text" id="unit-display" class="form-control" readonly>
            <input type="hidden" name="Unit" id="unit-id" value="{{ old('Unit') }}">
          </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Completion Date<span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="CompletionDate" value="{{ old('CompletionDate', date('Y-m-d')) }}" required>
          </div>
            <div class="col-md-3">
                <label class="form-label">Parts Used</label>
            <input type="text" class="form-control" name="PartsUsed" placeholder="e.g. 3/4” Pipe, Valve" value="{{ old('PartsUsed') }}">
          </div>
            <div class="col-md-3">
                <label class="form-label">Cost</label>
            <input type="number" class="form-control" name="Cost" placeholder="e.g. 1500" value="{{ old('Cost') }}">
          </div>
            <div class="col-md-3">
                <label class="form-label">Final Status<span class="text-danger">*</span></label>
            <select class="form-select" name="FinalStatus" required>
              <option value="">--Select a status--</option>
              @foreach ($finalstatus as $status)
                <option value="{{ $status->ID }}">
                  {{ $status->Description }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
          <!-- Document Upload -->
        <div class="mb-3">
            <label class="form-label">Upload Relevant Documents</label>
            <input type="file" name="Document[]" class="form-control" multiple>
        </div>

          <!-- Work Summary -->
          <div class="mb-3">
              <label class="form-label">Work Done Summary<span class="text-danger">*</span></label>
              <textarea class="form-control" rows="3" name="WorkDoneSummary"
                        placeholder="e.g. Replaced leaking pipe and sealed joints."
                        required>{{ old('WorkDoneSummary') }}</textarea>
          </div>
      </div>
    </div>
      <!-- Submit -->
      <a href="{{ route('workcompletion.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-success"
              onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Mark as Completed
      </button>
    </div>
  </form>
</div>

<!-- Auto-fill logic -->
<script>
  document.getElementById('request-select').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];

    document.getElementById('property-display').value = selected.getAttribute('data-property') || '';
    document.getElementById('property-id').value = selected.getAttribute('data-property') || '';

    document.getElementById('block-display').value = selected.getAttribute('data-block') || '';
    document.getElementById('block-id').value = selected.getAttribute('data-block') || '';

    document.getElementById('floor-display').value = selected.getAttribute('data-floor') || '';
    document.getElementById('floor-id').value = selected.getAttribute('data-floor') || '';

    document.getElementById('unit-display').value = selected.getAttribute('data-unit') || '';
    document.getElementById('unit-id').value = selected.getAttribute('data-unit') || '';
  });
</script>
@endsection
