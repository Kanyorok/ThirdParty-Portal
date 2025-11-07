@extends('layouts.app')
@section('title', 'Edit Claim Assessment')

@section('content')
<div class="container mt-5" style="max-width: 850px;">
    <form action="{{ route('bancassurance.claims.assessment_update', $assessment->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow-lg border-0 rounded-4">
            {{-- Header --}}
            <div class="card-header bg-primary text-white rounded-top-4">
                <p class="mb-0">
                    <b>Claim Info</b>
                </p>
            </div>

            {{-- Body --}}
            <div class="card-body p-4">
                {{-- Claim Info --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Claim ID</label>
                        <input type="text" name="ClaimId" class="form-control bg-light"
                               value="{{ old('ClaimId', $assessment->claim->policy->PolicyNumber) }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assessed By</label>
                        <input type="text" name="AssessedBy" class="form-control"
                               value="{{ old('AssessedBy', $assessment->assessedby->Name) }}" required>
                    </div>
                </div>

                {{-- Assessment Date & Amount --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assessment Date</label>
                        <input type="date" name="AssessmentDate" class="form-control"
                               value="{{ old('AssessmentDate', \Carbon\Carbon::parse($assessment->AssessmentDate)->format('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assessment Amount</label>
                        <!-- NOTE: use text input so commas can be shown while typing -->
                        <input type="text" id="AssessmentAmount" name="AssessmentAmount" class="form-control text-end"
                               value="{{ number_format(old('AssessmentAmount', $assessment->AssessmentAmount), 2) }}" required>
                    </div>
                </div>

                {{-- Decision --}}
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Decision</label>
                        <select name="Decision" class="form-select" required>
                            <option value="">-- Select Decision --</option>
                            @foreach($decisions as $decision)
                                <option value="{{ $decision->ID }}"
                                    {{ old('Decision', $assessment->Decision) == $decision->ID ? 'selected' : '' }}>
                                    {{ $decision->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Comments --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Assessment Comments</label>
                    <textarea name="AssessmentComments" class="form-control" rows="3"
                              placeholder="Enter assessment remarks or notes...">{{ old('AssessmentComments', $assessment->AssessmentComments) }}</textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="card-footer bg-light d-flex justify-content-between align-items-center rounded-bottom-4 py-3 px-4">
                <a href="{{ route('bancassurance.claims.assessment_list') }}" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-left-circle me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save2 me-1"></i> Update Assessment
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Number formatting script: keeps decimal input friendly, preserves caret, enforces 2 decimals on blur --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const amountInput = document.getElementById('AssessmentAmount');
    if (!amountInput) return;
    const form = amountInput.closest('form');

    function formatDuringInput(e) {
        const el = e.target;
        const raw = el.value;
        const selectionStart = el.selectionStart || raw.length;

        // How many "raw" characters (digits + dot) were before the caret:
        const rawLeft = raw.substring(0, selectionStart).replace(/,/g, '');
        const charsBefore = rawLeft.length;

        // Keep only digits and the first dot
        let cleaned = raw.replace(/[^0-9.]/g, '');
        const firstDot = cleaned.indexOf('.');
        if (firstDot !== -1) {
            cleaned = cleaned.slice(0, firstDot + 1) + cleaned.slice(firstDot + 1).replace(/\./g, '');
        }

        // Split integer and decimal parts
        let [intPart, decPart] = cleaned.split('.');
        if (typeof intPart === 'undefined' || intPart === '') intPart = '0';
        decPart = decPart ? decPart.slice(0, 2) : '';

        // Remove leading zeros except keep single zero
        intPart = intPart.replace(/^0+(?=\d)/, '');

        // Add commas to integer part
        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        // Rebuild new value. If user typed a dot but no decimals yet, preserve trailing dot.
        const newValue = (decPart !== '') ? (intPart + '.' + decPart) : (cleaned.includes('.') ? (intPart + '.') : intPart);
        el.value = newValue;

        // Compute new caret position by matching charsBefore (digits + dot) in the new value
        let pos = 0, count = 0;
        while (pos < newValue.length && count < charsBefore) {
            if (/\d|\./.test(newValue[pos])) count++;
            pos++;
        }
        // place caret
        el.setSelectionRange(pos, pos);
    }

    function formatOnBlur() {
        let v = amountInput.value.replace(/,/g, '');
        if (v === '' || v === '.') {
            amountInput.value = '';
            return;
        }
        if (!isNaN(v)) {
            const n = parseFloat(v);
            amountInput.value = n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    amountInput.addEventListener('input', formatDuringInput);
    amountInput.addEventListener('blur', formatOnBlur);

    // Remove commas before submit so server gets a plain numeric string
    if (form) {
        form.addEventListener('submit', function () {
            amountInput.value = amountInput.value.replace(/,/g, '').trim();
        });
    }
});
</script>
@endsection
