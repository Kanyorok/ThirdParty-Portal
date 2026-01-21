@extends('layouts.app')
@section('title', 'Complete Maintenance')

@section('content')

{{-- GLOBAL ERROR ALERT --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

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
            <select id="request-select" name="RequestNumber" class="form-select @error('RequestNumber') is-invalid @enderror">
              <option value="">-- Select Request --</option>
              @foreach ($assignments as $assignment)
                <option value="{{ $assignment->Id }}"
                  data-property="{{ $assignment->request->property->PropertyName ?? '-' }}"
                  data-block="{{ $assignment->request->block->BlockName ?? '-' }}"
                  data-floor="{{ $assignment->request->floor->FloorLabel ?? '-' }}"
                  data-unit="{{ $assignment->request->unit->UnitCode ?? '-' }}">
                    {{ $assignment->request->RequestNumber }}
                </option>
              @endforeach
            </select>
            @error('RequestNumber') <small class="text-danger">{{ $message }}</small> @enderror
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
            <input type="date" class="form-control @error('CompletionDate') is-invalid @enderror"
                   name="CompletionDate"
                   value="{{ old('CompletionDate', date('Y-m-d')) }}">
            @error('CompletionDate') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          <div class="col-md-3">
            <label class="form-label">Parts Used</label>
            <input type="text" class="form-control @error('PartsUsed') is-invalid @enderror"
                   name="PartsUsed" placeholder="e.g. 3/4” Pipe, Valve"
                   value="{{ old('PartsUsed') }}">
            @error('PartsUsed') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          <div class="col-md-3">
            <label class="form-label">Cost</label>
            <input type="number" class="form-control @error('Cost') is-invalid @enderror"
                   name="Cost" placeholder="e.g. 1500"
                   value="{{ old('Cost') }}">
            @error('Cost') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          <div class="col-md-3">
            <label class="form-label">Final Status<span class="text-danger">*</span></label>
            <select class="form-select @error('FinalStatus') is-invalid @enderror"
                    name="FinalStatus">
              <option value="">--Select a status--</option>
              @foreach ($finalstatus as $status)
                <option value="{{ $status->ID }}">
                  {{ $status->Description }}
                </option>
              @endforeach
            </select>
            @error('FinalStatus') <small class="text-danger">{{ $message }}</small> @enderror
          </div>
        </div>

        <!-- Document Upload -->
        <div class="mb-3">
          <label class="form-label">Upload Relevant Documents</label>
          <input type="file" name="Document[]" class="form-control @error('Document') is-invalid @enderror"
                 accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple>
          @error('Document') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Work Summary -->
        <div class="mb-3">
          <label class="form-label">Work Done Summary<span class="text-danger">*</span></label>
          <textarea class="form-control @error('WorkDoneSummary') is-invalid @enderror"
                    rows="3" name="WorkDoneSummary"
                    placeholder="e.g. Replaced leaking pipe and sealed joints.">{{ old('WorkDoneSummary') }}</textarea>
          @error('WorkDoneSummary') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

      
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
          <a href="{{ route('workcompletion.index') }}" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-success"
                  onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
              Submit
          </button>
        </div>

      </div>
    </div>

  </form>
</div>

<!-- Auto-fill logic -->
<script>
  document.getElementById('request-select').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];

    document.getElementById('property-display').value = selected.dataset.property || '';
    document.getElementById('property-id').value = selected.dataset.property || '';

    document.getElementById('block-display').value = selected.dataset.block || '';
    document.getElementById('block-id').value = selected.dataset.block || '';

    document.getElementById('floor-display').value = selected.dataset.floor || '';
    document.getElementById('floor-id').value = selected.dataset.floor || '';

    document.getElementById('unit-display').value = selected.dataset.unit || '';
    document.getElementById('unit-id').value = selected.dataset.unit || '';
  });
</script>

@endsection
