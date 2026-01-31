@extends('layouts.app')
@section('title', 'Complete Maintenance')

@section('content')

<div class="container mt-4">
    <form action="{{ route('workcompletion.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">
                Work Execution & Resolution
            </div>

            <div class="card-body">

                {{-- ================= Request Selection ================= --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Select Maintenance Request <span class="text-danger">*</span>
                        </label>

                        <select id="request-select"
                                name="RequestNumber"
                                class="form-select @error('RequestNumber') is-invalid @enderror">

                            <option value="">-- Select Request --</option>

                            @foreach ($assignments as $assignment)
                                <option value="{{ $assignment->Id }}"
                                    data-property="{{ $assignment->request->property->PropertyName ?? '' }}"
                                    data-block="{{ $assignment->request->block->BlockName ?? '' }}"
                                    data-floor="{{ $assignment->request->floor->FloorLabel ?? '' }}"
                                    data-unit="{{ $assignment->request->unit->UnitCode ?? '' }}"
                                    {{ old('RequestNumber') == $assignment->Id ? 'selected' : '' }}>
                                    {{ $assignment->request->RequestNumber }}
                                </option>
                            @endforeach
                        </select>

                        @error('RequestNumber')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Property</label>
                        <input type="text" id="property-display" class="form-control" readonly>
                    </div>
                </div>

                {{-- ================= Location Details ================= --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Block</label>
                        <input type="text" id="block-display" class="form-control" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Floor</label>
                        <input type="text" id="floor-display" class="form-control" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Unit</label>
                        <input type="text" id="unit-display" class="form-control" readonly>
                    </div>
                </div>

                {{-- ================= Work Details ================= --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">
                            Completion Date <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               name="CompletionDate"
                               class="form-control @error('CompletionDate') is-invalid @enderror"
                               value="{{ old('CompletionDate', date('Y-m-d')) }}">

                        @error('CompletionDate')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Parts Used</label>
                        <input type="text"
                               name="PartsUsed"
                               class="form-control @error('PartsUsed') is-invalid @enderror"
                               value="{{ old('PartsUsed') }}"
                               placeholder="e.g. Pipe, Valve">

                        @error('PartsUsed')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Cost</label>
                        <input type="number"
                               name="Cost"
                               class="form-control @error('Cost') is-invalid @enderror"
                               value="{{ old('Cost') }}"
                               placeholder="e.g. 1500">

                        @error('Cost')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">
                            Final Status <span class="text-danger">*</span>
                        </label>

                        <select name="FinalStatus"
                                class="form-select @error('FinalStatus') is-invalid @enderror">
                            <option value="">-- Select Status --</option>
                            @foreach ($finalstatus as $status)
                                <option value="{{ $status->ID }}"
                                    {{ old('FinalStatus') == $status->ID ? 'selected' : '' }}>
                                    {{ $status->Description }}
                                </option>
                            @endforeach
                        </select>

                        @error('FinalStatus')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- ================= Documents ================= --}}
                <div class="mb-3">
                    <label class="form-label">Upload Relevant Documents</label>
                    <input type="file"
                           name="Document[]"
                           class="form-control @error('Document') is-invalid @enderror"
                           accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                           multiple>

                    <small class="text-muted">
                        Documents must be re-uploaded if the form reloads.
                    </small>

                    @error('Document')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ================= Work Summary ================= --}}
                <div class="mb-3">
                    <label class="form-label">
                        Work Done Summary <span class="text-danger">*</span>
                    </label>

                    <textarea name="WorkDoneSummary"
                              rows="3"
                              class="form-control @error('WorkDoneSummary') is-invalid @enderror"
                              placeholder="Describe the work performed...">{{ old('WorkDoneSummary') }}</textarea>

                    @error('WorkDoneSummary')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ================= Actions ================= --}}
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('workcompletion.index') }}"
                       class="btn btn-outline-secondary">
                        Cancel
                    </a>

                    <button type="submit"
                            class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        Submit
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- ================= Auto-fill Script ================= --}}
<script>
    const requestSelect = document.getElementById('request-select');

    function populateDetails() {
        const selected = requestSelect.options[requestSelect.selectedIndex];
        if (!selected) return;

        document.getElementById('property-display').value = selected.dataset.property || '';
        document.getElementById('block-display').value = selected.dataset.block || '';
        document.getElementById('floor-display').value = selected.dataset.floor || '';
        document.getElementById('unit-display').value = selected.dataset.unit || '';
    }

    requestSelect.addEventListener('change', populateDetails);

    // Restore data after validation error
    window.addEventListener('DOMContentLoaded', () => {
        if (requestSelect.value) {
            populateDetails();
        }
    });
</script>

@endsection
