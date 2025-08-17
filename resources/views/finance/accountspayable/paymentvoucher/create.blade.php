@extends('layouts.app')
@section('title', 'Create Payment Voucher')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info"><i class="fas fa-file-invoice-dollar me-1"></i> Create Payment Voucher</h5>
                <a href="{{ route('paymentvoucher.index') }}" class="btn btn-outline-secondary btn-sm p-2">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body p-3">
                @if ($errors->any() || session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Please correct the following errors:</strong>
                        </div>
                        @if(session('error'))
                            <div class="mb-2">{{ session('error') }}</div>
                        @endif
                        @if ($errors->any())
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form id="paymentVoucherForm" action="{{ route('paymentvoucher.store') }}" method="POST">
                    @csrf
                    <input type="hidden" id="VoucherNo" name="VoucherNo" value="{{ $VoucherNo }}">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="InvoiceNo">Invoice Ref <span class="text-danger">*</span></label>
                            <select name="InvoiceNo" id="InvoiceNo" class="form-select @error('InvoiceNo') is-invalid @enderror" required>
                                <option value="">-- Select Invoice --</option>
                                @foreach($invoices as $invoice)
                                    <option value="{{ $invoice['Id'] }}"
                                            data-amount="{{ (float) $invoice['Balance'] }}"
                                            data-currency="{{ $invoice['CurrencyCode'] }}"
                                        @selected(old('InvoiceNo') == $invoice['Id'])>
                                        {{ $invoice['InvoiceNumber'] }} - {{ $invoice['CurrencyCode'] }} {{ number_format($invoice['InvoiceAmount'], 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('InvoiceNo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="PaymentMethod">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select @error('PaymentMethod') is-invalid @enderror" name="PaymentMethod" id="PaymentMethod" required>
                                <option value="" disabled selected>-- Select Payment Method --</option>
                                @forelse($paymentMethods as $paymentMethod)
                                    <option value="{{ $paymentMethod->Description }}" @selected(old('PaymentMethod') == $paymentMethod->Description)>
                                        {{ $paymentMethod->Description }}
                                    </option>
                                @empty
                                    <option disabled>No Payment Method Found</option>
                                @endforelse
                            </select>
                            @error('PaymentMethod') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="paymentType">Payment Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('PaymentType') is-invalid @enderror" name="PaymentType" id="paymentType" required>
                                <option value="" disabled selected>-- Select Payment Type --</option>
                                @forelse($paymentTypes as $paymentType)
                                    <option value="{{ $paymentType->Description }}" @selected(old('PaymentType') == $paymentType->Description)>
                                        {{ $paymentType->Description }}
                                    </option>
                                @empty
                                    <option disabled>No Payment Type Found</option>
                                @endforelse
                            </select>
                            @error('PaymentType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="amountRow" class="row g-3 mb-3" style="display:none;">
                        <div class="col-12">
                            <label class="form-label" for="PayAmount">Amount <span class="text-danger">*</span></label>
                            <input type="number"
                                   id="PayAmount"
                                   class="form-control @error('TotAmnt') is-invalid @enderror"
                                   name="TotAmnt"
                                   step="0.01"
                                   min="0"
                                   value="{{ old('TotAmnt') }}"
                                   placeholder="Enter amount">
                            @error('TotAmnt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="scheduleRow" class="row g-3 mb-3" style="display:none;">
                        <div class="col-md-4">
                            <label class="form-label" for="SchedAmount">Amount <span class="text-danger">*</span></label>
                            <input type="number"
                                   id="SchedAmount"
                                   class="form-control @error('TotAmnt') is-invalid @enderror"
                                   step="0.01"
                                   min="0"
                                   value="{{ old('TotAmnt') }}"
                                   placeholder="Enter amount">
                            @error('TotAmnt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="StartDate">Start Date <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control @error('StartDate') is-invalid @enderror"
                                   name="StartDate"
                                   id="StartDate"
                                   value="{{ old('StartDate') }}">
                            @error('StartDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="Frequency">Frequency <span class="text-danger">*</span></label>
                            <select class="form-select @error('Frequency') is-invalid @enderror" name="Frequency" id="Frequency">
                                <option value="" disabled selected>-- Select Frequency --</option>
                                <option value="Monthly" @selected(old('Frequency') === 'Monthly')>Monthly</option>
                                <option value="Biweekly" @selected(old('Frequency') === 'Biweekly')>Biweekly</option>
                                <option value="Weekly" @selected(old('Frequency') === 'Weekly')>Weekly</option>
                                <option value="Custom" @selected(old('Frequency') === 'Custom')>Custom</option>
                            </select>
                            @error('Frequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="descriptionRow" class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label" for="Description">Description</label>
                            <textarea class="form-control @error('Description') is-invalid @enderror"
                                      name="Description"
                                      id="Description"
                                      rows="3"
                                      placeholder="Enter voucher description">{{ old('Description') }}</textarea>
                            @error('Description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="formButtons" class="text-end" style="display:none;">
                        <a href="{{ route('paymentvoucher.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        <button type="submit"
                                id="submitButton"
                                class="btn btn-outline-success">
                            <i class="fas fa-save me-1"></i> Save Voucher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Form styling */
        .form-control, .form-select {
            font-size: 0.875rem;
            padding: 0.5rem;
        }

        /* Card styling */
        .card {
            border: none;
            border-radius: 0.5rem;
        }

        /* Button styling */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Hover effect for buttons */
        .btn-outline-success:hover, .btn-outline-secondary:hover {
            transition: background-color 0.2s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .form-control, .form-select {
                font-size: 0.75rem;
                padding: 0.4rem;
            }
            .btn-sm {
                padding: 0.2rem 0.4rem;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        (function() {
            const paymentTypeEl = document.getElementById('paymentType');
            const invoiceEl = document.getElementById('InvoiceNo');
            const amountRow = document.getElementById('amountRow');
            const scheduleRow = document.getElementById('scheduleRow');
            const formButtons = document.getElementById('formButtons');
            const payAmountEl = document.getElementById('PayAmount');
            const schedAmountEl = document.getElementById('SchedAmount');
            const startDateEl = document.getElementById('StartDate');
            const freqEl = document.getElementById('Frequency');
            const form = document.getElementById('paymentVoucherForm');
            const submitButton = document.getElementById('submitButton');

            function getSelectedInvoiceAmount() {
                const opt = invoiceEl?.options[invoiceEl.selectedIndex];
                return opt ? parseFloat(opt.getAttribute('data-amount') || '0') : 0;
            }

            function setRequired(el, on) {
                if (!el) return;
                el[on ? 'setAttribute' : 'removeAttribute']('required', 'required');
            }

            function setReadonly(el, on) {
                if (!el) return;
                el[on ? 'setAttribute' : 'removeAttribute']('readonly', 'readonly');
            }

            function resetSubmitButton() {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-save me-1"></i> Save Voucher';
                }
            }

            function updateVisibility() {
                const type = paymentTypeEl?.value || '';

                // Hide all sections first
                amountRow.style.display = 'none';
                scheduleRow.style.display = 'none';
                formButtons.style.display = 'none';

                // Reset required and readonly
                setRequired(payAmountEl, false);
                setRequired(schedAmountEl, false);
                setRequired(startDateEl, false);
                setRequired(freqEl, false);
                setReadonly(payAmountEl, false);

                if (type === 'Full' || type === 'Partial') {
                    amountRow.style.display = 'flex';
                    setRequired(payAmountEl, true);
                    if (type === 'Full') {
                        const amt = getSelectedInvoiceAmount();
                        payAmountEl.value = amt ? amt.toFixed(2) : '';
                        setReadonly(payAmountEl, true);
                    }
                    formButtons.style.display = 'block';
                } else if (type === 'Scheduled') {
                    scheduleRow.style.display = 'flex';
                    setRequired(schedAmountEl, true);
                    setRequired(startDateEl, true);
                    setRequired(freqEl, true);
                    const amt = getSelectedInvoiceAmount();
                    schedAmountEl.value = amt ? amt.toFixed(2) : '';
                    payAmountEl.value = schedAmountEl.value;
                    formButtons.style.display = 'block';
                }
            }

            if (schedAmountEl) {
                schedAmountEl.addEventListener('input', () => {
                    if (payAmountEl) payAmountEl.value = schedAmountEl.value;
                });
            }

            if (invoiceEl) {
                invoiceEl.addEventListener('change', () => {
                    const type = paymentTypeEl?.value || '';
                    const amt = getSelectedInvoiceAmount();
                    if (type === 'Full') {
                        payAmountEl.value = amt ? amt.toFixed(2) : '';
                        setReadonly(payAmountEl, true);
                    } else if (type === 'Scheduled') {
                        schedAmountEl.value = amt ? amt.toFixed(2) : '';
                        payAmountEl.value = schedAmountEl.value;
                    }
                });
            }

            if (paymentTypeEl) {
                paymentTypeEl.addEventListener('change', updateVisibility);
            }

            if (form && submitButton) {
                form.addEventListener('submit', (event) => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        resetSubmitButton();
                        form.classList.add('was-validated');
                    } else {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
                    }
                });
            }

            // Reset button on page load or error
            document.addEventListener('DOMContentLoaded', () => {
                updateVisibility();
                resetSubmitButton();
                form.classList.add('needs-validation');
            });
        })();
    </script>
@endsection
