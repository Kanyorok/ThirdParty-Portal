@extends('layouts.app')
@section('title', 'Edit Premium Payment')
@section('content')
    <div class="container mt-4">
        <h4>✏️ Edit Premium Payment</h4>

        <form method="POST" action="{{ route('bancassurance.premiums.update', $payment->Id) }}">
            @csrf
            @method('PUT')

            <div class="col-md-6 mb-3">
                <label class="form-label">Select Policy</label>
                <select id="request-select" name="PolicyID" class="form-select" required>
                    <option value="">-- Select Policy --</option>
                    @foreach ($policies as $policy)
                        <option
                            value="{{ $policy->Id }}"
                            data-paymentfrequency="{{ $policy->paymentfrequency->Description }}"
                            data-customerid="{{ $policy->CustomerID }}" {{-- Numeric ID --}}
                            data-customername="{{ $policy->customer->FullName }}" {{-- Display name --}}
                            {{ $payment->PolicyID == $policy->Id ? 'selected' : '' }}>
                            {{ $policy->PolicyNumber }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Customer Name</label>
                    <input type="text" id="customer-id-display" class="form-control" readonly>
                    <input type="hidden" name="CustomerID" id="customer-id"
                           value="{{ old('CustomerID', $payment->CustomerID) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Frequency</label>
                    <input type="text" id="payment-frequency-display" class="form-control" readonly>
                    <input type="hidden" name="PaymentFrequency" id="payment-frequency-id"
                           value="{{ old('PaymentFrequency', $payment->PaymentFrequency) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Date</label>
                <input type="date" name="PaymentDate" id="payment-date" class="form-control"
                       value="{{ \Carbon\Carbon::parse($payment->PaymentDate)->format('Y-m-d') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Next Payment Date</label>
                <input type="date" name="NextPaymentDate" id="next-payment-date" class="form-control"
                       value="{{ \Carbon\Carbon::parse($payment->NextPaymentDate)->format('Y-m-d') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Amount Paid</label>
                <input type="number" name="Amount" step="0.01" class="form-control" value="{{ $payment->Amount }}"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Mode</label>
                <select name="PaymentMode" class="form-select">
                    <option value="">--Select Payment Mode--</option>
                    @foreach ($paymentModes as $paymentMode)
                        <option
                            value="{{ $paymentMode->ID }}" {{ $payment->PaymentMode == $paymentMode->ID ? 'selected' : '' }}>
                            {{ $paymentMode->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Reference Number</label>
                <input type="text" name="ReferenceNumber" class="form-control" value="{{ $payment->ReferenceNumber }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Notes (optional)</label>
                <textarea name="Notes" class="form-control" rows="2">{{ $payment->Notes }}</textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success">💾 Update Payment</button>
            </div>
        </form>
    </div>

    {{-- JavaScript --}}
    <script>
        const frequencyMap = {
            'Monthly': 1,
            'Quarterly': 3,
            'Semi-Annually': 6,
            'Annually': 12
        };

        document.addEventListener('DOMContentLoaded', function () {
            const selected = document.querySelector('#request-select option:checked');
            if (selected) {
                document.getElementById('customer-id-display').value = selected.getAttribute('data-customername') || '';
                document.getElementById('payment-frequency-display').value = selected.getAttribute('data-paymentfrequency') || '';
                document.getElementById('customer-id').value = selected.getAttribute('data-customerid') || '';
                document.getElementById('payment-frequency-id').value = selected.getAttribute('data-paymentfrequency') || '';
            }
        });

        document.getElementById('request-select').addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const frequency = selected.getAttribute('data-paymentfrequency');
            const customerId = selected.getAttribute('data-customerid');
            const customerName = selected.getAttribute('data-customername');

            document.getElementById('customer-id-display').value = customerName || '';
            document.getElementById('customer-id').value = customerId || '';
            document.getElementById('payment-frequency-display').value = frequency || '';
            document.getElementById('payment-frequency-id').value = frequency || '';

            autoCalculateNextPaymentDate();
        });

        document.getElementById('payment-date').addEventListener('change', autoCalculateNextPaymentDate);

        function autoCalculateNextPaymentDate() {
            const paymentDateInput = document.getElementById('payment-date');
            const frequency = document.getElementById('payment-frequency-display').value;
            const nextDateInput = document.getElementById('next-payment-date');

            if (!paymentDateInput.value || !frequencyMap[frequency]) {
                nextDateInput.value = '';
                return;
            }

            const monthsToAdd = frequencyMap[frequency];
            const date = new Date(paymentDateInput.value);
            date.setMonth(date.getMonth() + monthsToAdd);

            const nextDateStr = date.toISOString().split('T')[0];
            nextDateInput.value = nextDateStr;
        }
    </script>
@endsection


