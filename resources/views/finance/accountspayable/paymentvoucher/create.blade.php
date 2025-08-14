@extends('layouts.app')
@section('title', 'Create Payment Voucher')

@section('content')
    <div class="container mt-1">
        <div class="card shadow-sm rounded-4">
            <div class="card-header bg-light py-2 px-3">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-file-invoice-dollar text-info"></i>
                    Create Payment Voucher
                </h6>
            </div>

            <div class="card-body">
{{--                <p class="text-muted mb-3">Fill in the details below to create a new payment voucher.</p>--}}

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('paymentvoucher.store') }}" method="POST" novalidate>
                    @csrf

                    {{-- Voucher Number (hidden/commented) --}}
                    {{--
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="vouchernumber">Voucher Number</label>
                            <input type="text" id="VoucherNo" name="VoucherNo" class="form-control" value="{{ $VoucherNo }}" readonly>
                        </div>
                    </div>
                    --}}

                    {{-- Row 1: Invoice, Payment Method, Payment Type --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="InvoiceNo">Invoice Ref <span class="text-danger">*</span></label>
                            <select name="InvoiceNo" id="InvoiceNo" class="form-select @error('InvoiceNo') is-invalid @enderror" required>
                                <option value="">-- Select Invoice --</option>
                                @foreach($invoices as $invoice)
                                    <option value="{{ $invoice->Id }}"
                                            data-amount="{{ (float) $invoice->InvoiceAmount }}"
                                        @selected(old('InvoiceNo') == $invoice->Id)>
                                        {{ $invoice->InvoiceNumber }} - {{ $invoice->currency->Code }} {{ number_format($invoice->InvoiceAmount, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('InvoiceNo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="PaymentMethod">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select @error('PaymentMethod') is-invalid @enderror" name="PaymentMethod" id="PaymentMethod" required>
                                @forelse($paymentMethods as $paymentMethod)
                                    <option value="{{$paymentMethod->Description}}">{{$paymentMethod->Description}}</option>
                                @empty
                                    <option disabled>No Payment Method Found</option>
                                @endforelse
                            </select>
                            @error('PaymentMethod') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="paymentType">Payment Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('PaymentType') is-invalid @enderror" name="PaymentType" id="paymentType" required>
                                @forelse($paymentTypes as $paymentType)
                                    <option value="{{$paymentType->Description}}">{{$paymentType->Description}}</option>
                                @empty
                                    <option disabled>No Payment Type Found</option>
                                @endforelse
                            </select>
                            </select>
                            @error('PaymentType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Dynamic: Full/Partial -> Amount col-12 --}}
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

                    {{-- Dynamic: Scheduled -> Amount, Start Date, Frequency in one row (col-4 each) --}}
                    <div id="scheduleRow" class="row g-3 mb-3" style="display:none;">
                        <div class="col-md-4">
                            <label class="form-label" for="SchedAmount">Amount <span class="text-danger">*</span></label>
                            <input type="number"
                                   id="SchedAmount"
                                   class="form-control"
                                   step="0.01"
                                   min="0"
                                   placeholder="Enter amount">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="StartDate">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="StartDate" id="StartDate" value="{{ old('StartDate') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="Frequency">Frequency <span class="text-danger">*</span></label>
                            <select class="form-select" name="Frequency" id="Frequency">
                                <option value="Monthly"  @selected(old('Frequency') === 'Monthly')>Monthly</option>
                                <option value="Biweekly" @selected(old('Frequency') === 'Biweekly')>Biweekly</option>
                                <option value="Weekly"   @selected(old('Frequency') === 'Weekly')>Weekly</option>
                                <option value="Custom"   @selected(old('Frequency') === 'Custom')>Custom</option>
                            </select>
                        </div>
                    </div>

                    {{-- Description (always visible) --}}
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

                    {{-- Submit / Cancel buttons --}}
                    <div id="formButtons" class="text-end" style="display:none;">
                        <a href="{{ route('paymentvoucher.index') }}" class="btn btn-outline-secondary me-2">Back</a>
                        <button class="btn btn-success"
                                onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='💾 Saving…'; this.form.submit();}">
                            💾 Save Voucher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const paymentTypeEl = document.getElementById('paymentType');
            const invoiceEl     = document.getElementById('InvoiceNo');

            const amountRow     = document.getElementById('amountRow');
            const scheduleRow   = document.getElementById('scheduleRow');
            const formButtons   = document.getElementById('formButtons');

            const payAmountEl   = document.getElementById('PayAmount');
            const schedAmountEl = document.getElementById('SchedAmount');
            const startDateEl   = document.getElementById('StartDate');
            const freqEl        = document.getElementById('Frequency');

            function getSelectedInvoiceAmount() {
                const opt = invoiceEl.options[invoiceEl.selectedIndex];
                return opt ? parseFloat(opt.getAttribute('data-amount') || '0') : 0;
            }

            function setRequired(el, on) {
                if (!el) return;
                if (on) { el.setAttribute('required', 'required'); }
                else    { el.removeAttribute('required'); }
            }

            function updateVisibility() {
                const type = paymentTypeEl.value;

                // Hide all sections first
                amountRow.style.display    = 'none';
                scheduleRow.style.display  = 'none';
                formButtons.style.display  = 'none';

                // Reset required
                setRequired(payAmountEl, false);
                setRequired(schedAmountEl, false);
                setRequired(startDateEl, false);
                setRequired(freqEl, false);

                if (type === 'Full' || type === 'Partial') {
                    amountRow.style.display = 'flex';
                    setRequired(payAmountEl, true);

                    if (type === 'Full') {
                        const amt = getSelectedInvoiceAmount();
                        payAmountEl.value = amt ? amt.toFixed(2) : '';
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
                    payAmountEl.value = schedAmountEl.value;
                });
            }

            if (invoiceEl) {
                invoiceEl.addEventListener('change', () => {
                    const type = paymentTypeEl.value;
                    const amt = getSelectedInvoiceAmount();

                    if (type === 'Full') {
                        payAmountEl.value = amt ? amt.toFixed(2) : '';
                    } else if (type === 'Scheduled') {
                        schedAmountEl.value = amt ? amt.toFixed(2) : '';
                        payAmountEl.value = schedAmountEl.value;
                    }
                });
            }

            if (paymentTypeEl) {
                paymentTypeEl.addEventListener('change', updateVisibility);
            }

            document.addEventListener('DOMContentLoaded', updateVisibility);
        })();
    </script>
@endsection
