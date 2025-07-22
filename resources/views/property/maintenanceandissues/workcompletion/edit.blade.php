@extends('layouts.app')
@section('title', 'Edit Maintenance Work Completion')

@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🛠️ Edit Maintenance Work Completion</h4>

  <form action="{{ route('workcompletion.update', $workCompletion->Id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="card shadow">
      <div class="card-header bg-light fw-bold">🧰 Work Execution & Resolution</div>
      <div class="card-body">

        <!-- Request Selection -->
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Select Maintenance Request</label>
            <select id="request-select" name="RequestNumber" class="form-select" required>
              <option value="">-- Select Request --</option>
              @foreach ($assignments as $assignment)
                <option
                  value="{{ $assignment->Id }}"
                  data-property="{{ $assignment->Property }}"
                  data-block="{{ $assignment->Block }}"
                  data-floor="{{ $assignment->Floor }}"
                  data-unit="{{ $assignment->Unit }}"
                  {{ old('RequestNumber', $workCompletion->RequestNumber) == $assignment->Id ? 'selected' : '' }}>
                  {{ $assignment->request->RequestNumber }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- Auto-filled Property Info -->
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Property</label>
            <input type="text" id="property-display" class="form-control" readonly
              value="{{ old('PropertyDisplay', $workCompletion->property->PropertyName ?? '') }}">
            <input type="hidden" name="Property" id="property-id" value="{{ old('Property', $workCompletion->Property) }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Block</label>
            <input type="text" id="block-display" class="form-control" readonly
              value="{{ old('BlockDisplay', $workCompletion->block->BlockName ?? '') }}">
            <input type="hidden" name="Block" id="block-id" value="{{ old('Block', $workCompletion->Block) }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Floor</label>
            <input type="text" id="floor-display" class="form-control" readonly
              value="{{ old('FloorDisplay', $workCompletion->floor->FloorLabel ?? '') }}">
            <input type="hidden" name="Floor" id="floor-id" value="{{ old('Floor', $workCompletion->Floor) }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Unit</label>
            <input type="text" id="unit-display" class="form-control" readonly
              value="{{ old('UnitDisplay', $workCompletion->unit->UnitCode ?? '') }}">
            <input type="hidden" name="Unit" id="unit-id" value="{{ old('Unit', $workCompletion->Unit) }}">
          </div>
        </div>

        <!-- Completion Date -->
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Completion Date</label>
            <input type="date" class="form-control" name="CompletionDate" value="{{ old('CompletionDate', $workCompletion->CompletionDate) }}" required>
          </div>
        </div>

        <!-- Work Summary -->
        <div class="mb-3">
          <label class="form-label">Work Done Summary</label>
          <textarea class="form-control" rows="3" name="WorkDoneSummary" required>{{ old('WorkDoneSummary', $workCompletion->WorkDoneSummary) }}</textarea>
        </div>

        <!-- Cost and Status -->
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Parts Used (Optional)</label>
            <input type="text" class="form-control" name="PartsUsed" value="{{ old('PartsUsed', $workCompletion->PartsUsed) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">Cost (KES)</label>
            <input type="number" class="form-control" name="Cost" value="{{ old('Cost', $workCompletion->Cost) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">Final Status</label>
            <select class="form-select" name="FinalStatus" required>
              <option value="">--Select a status--</option>
              @foreach ($finalstatus as $status)
                <option value="{{ $status->ID }}" {{ old('FinalStatus', $workCompletion->FinalStatus) == $status->ID ? 'selected' : '' }}>
                  {{ $status->Description }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- File Upload -->
        <div class="mb-3">
          <label class="form-label">Upload Resolution Evidence (Photos/Invoice)</label>
          <input type="file" class="form-control" name="Attachments[]" multiple>
        </div>

        <!-- Submit -->
        <div class="text-end">
          <button class="btn btn-primary" type="submit">💾 Update Completion</button>
        </div>
      </div>
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
