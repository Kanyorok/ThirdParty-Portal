@extends('layouts.app')
@section('title', 'Initiate Claim Payment')

@section('content')
    <div class="container mt-4">
        <h4>💳 Initiate Claim Payment</h4>

        <form action="{{ route('bancassurance.claims.payments.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Select Claim</label>
                <select name="ClaimID" id="ClaimID" class="form-select" required onchange="populateClaimDetails(this)">
                    <option value="">-- Choose Unpaid Claim --</option>
                    @foreach($unpaidClaims as $claim)
                        <option
                            value="{{ $claim->Id }}"
                            data-amount="{{ $claim->ApprovedAmount }}"
                            data-customer="{{ $claim->CustomerName }}"
                            data-policy="{{ $claim->PolicyNumber }}"
                        >
                            {{ $claim->PolicyNumber }} – {{ $claim->CustomerName }}
                            (KES {{ number_format($claim->ApprovedAmount, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Customer Name</label>
                <input type="text" id="CustomerName" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Policy Number</label>
                <input type="text" id="PolicyNumber" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Amount to Pay (KES)</label>
                <input type="number" name="AmountPaid" id="AmountPaid" class="form-control" step="0.01" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Date</label>
                <input type="date" name="PaymentDate" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Mode</label>
                <select name="PaymentMode" class="form-select" required>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cheque">Cheque</option>
                    <option value="MPesa">MPesa</option>
                    <option value="Cash">Cash</option>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success">💸 Process Payment</button>
            </div>
        </form>
    </div>

    <script>
        function populateClaimDetails(select) {
            const selected = select.options[select.selectedIndex];
            document.getElementById('CustomerName').value = selected.getAttribute('data-customer') || '';
            document.getElementById('PolicyNumber').value = selected.getAttribute('data-policy') || '';
            document.getElementById('AmountPaid').value = selected.getAttribute('data-amount') || '';
        }
    </script>
@endsection
