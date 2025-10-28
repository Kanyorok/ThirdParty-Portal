@extends('layouts.app')
@section('title','New Credit Adjustment')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-plus-circle text-primary me-2"></i> Create Credit Adjustment
                </h6>
                <a href="{{ route('creditadjustment.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Adjustments
                </a>
            </div>

            <div class="card-body p-3">
                <form action="{{ route('creditadjustment.store') }}" method="POST" id="adjustmentForm">
                    @csrf
                    <p class="text-muted">Create a new credit adjustment request for an approved credit profile.</p>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="CreditID" class="form-label">Credit Profile <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" disabled id="CreditID_display">
                                <option value="">-- Select Credit Profile --</option>
                                @foreach($creditProfiles as $profile)
                                    <option value="{{ $profile->Id }}"
                                            data-customer="{{ $profile->customer->ThirdPartyName }}"
                                            data-current-limit="{{ $profile->CreditLimit }}"
                                        {{ $selectedCredit && $selectedCredit->Id == $profile->Id ? 'selected' : '' }}>
                                        {{ $profile->customer->ThirdPartyName }} -
                                        Current: {{ number_format($profile->CreditLimit, 2) }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Hidden field that actually submits -->
                            <input type="hidden" name="CreditID" id="CreditID" value="{{ $selectedCredit->Id ?? '' }}">

                            <div class="form-text">Only approved credit profiles are shown</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="AdjustmentType" class="form-label">Adjustment Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" name="AdjustmentType" id="AdjustmentType" required>
                                <option value="">-- Select Type --</option>
                                <option value="increase">Credit Increase</option>
                                <option value="decrease">Credit Decrease</option>
                                {{--                            <option value="revision">Credit Revision</option>--}}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="Amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                {{--                            <span class="input-group-text">KES</span>--}}
                                <input type="number" step="0.01" name="Amount" id="Amount" class="form-control"
                                       placeholder="Enter amount" required min="0.01">
                            </div>
                            <div class="form-text" id="amountHelp">
                                For increase/decrease: enter adjustment amount. For revision: enter new total limit.
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info py-2" id="calculationPreview" style="display: none;">
                                <strong>Preview:</strong> <span id="previewText"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ReferenceType" class="form-label">Reference Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" name="ReferenceType" id="ReferenceType" required>
                                <option value="">-- Select Reference --</option>
                                <option value="credit_review">Credit Review</option>
                                <option value="customer_request">Customer Request</option>
                                <option value="business_growth">Business Growth</option>
                                <option value="risk_assessment">Risk Assessment</option>
                                <option value="management_decision">Management Decision</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="EffectiveFrom" class="form-label">Effective From <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="EffectiveFrom" id="EffectiveFrom" class="form-control"
                                   value="{{ date('Y-m-d') }}" required min="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="Reason" class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="Reason" id="Reason" class="form-control" rows="3"
                                  placeholder="Provide detailed reason for this credit adjustment" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="Notes" class="form-label">Additional Notes</label>
                        <textarea name="Notes" id="Notes" class="form-control" rows="2"
                                  placeholder="Any additional notes or comments"></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('creditadjustment.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-sm btn-primary"
                                onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='Saving...'; this.form.submit();}">
                            <i class="fas fa-save me-1"></i> Create Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const creditSelect = document.getElementById('CreditID');
            const adjustmentTypeSelect = document.getElementById('AdjustmentType');
            const amountInput = document.getElementById('Amount');
            const calculationPreview = document.getElementById('calculationPreview');
            const previewText = document.getElementById('previewText');

            function updatePreview() {
                const selectedCredit = creditSelect.options[creditSelect.selectedIndex];
                const adjustmentType = adjustmentTypeSelect.value;
                const amount = parseFloat(amountInput.value) || 0;

                if (!selectedCredit.value || !adjustmentType || amount <= 0) {
                    calculationPreview.style.display = 'none';
                    return;
                }

                const currentLimit = parseFloat(selectedCredit.getAttribute('data-current-limit')) || 0;
                const customerName = selectedCredit.getAttribute('data-customer');
                let newLimit = 0;
                let preview = '';

                switch (adjustmentType) {
                    case 'increase':
                        newLimit = currentLimit + amount;
                        preview = `${customerName}: Current ${currentLimit.toLocaleString()} + ${amount.toLocaleString()} = ${newLimit.toLocaleString()}`;
                        break;
                    case 'decrease':
                        newLimit = Math.max(0, currentLimit - amount);
                        preview = `${customerName}: Current ${currentLimit.toLocaleString()} - ${amount.toLocaleString()} = ${newLimit.toLocaleString()}`;
                        break;
                    case 'revision':
                        newLimit = amount;
                        preview = `${customerName}: Revise from ${currentLimit.toLocaleString()} to ${newLimit.toLocaleString()}`;
                        break;
                }

                previewText.textContent = preview;
                calculationPreview.style.display = 'block';
            }

            creditSelect.addEventListener('change', updatePreview);
            adjustmentTypeSelect.addEventListener('change', updatePreview);
            amountInput.addEventListener('input', updatePreview);

            // Pre-populate if selected credit exists
            @if($selectedCredit)
            updatePreview();
            @endif
        });
    </script>
@endsection

@section('styles')
    <style>
        :root {
            --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
        }

        body, .card, .table, input, select, textarea {
            font-family: var(--font-sans);
        }
    </style>
@endsection

