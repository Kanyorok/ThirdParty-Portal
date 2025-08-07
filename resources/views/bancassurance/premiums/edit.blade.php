@extends('layouts.app')
@section('title', 'Edit Premium Payment')

@section('content')
<div class="container mt-4">
    <h4>✏️ Edit Premium Payment</h4>

    <form method="POST" action="{{ route('bancassurance.premiums.update', $payment->Id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Select Policy</label>
            <select name="PolicyID" class="form-select" required>
                <option value="">-- Select Policy --</option>
                @foreach($policies as $p)
                    <option value="{{ $p->Id }}" {{ $payment->PolicyID == $p->Id ? 'selected' : '' }}>
                        {{ $p->PolicyNumber }} - {{ $p->CustomerName }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Date</label>
            <input type="date" name="PaymentDate" class="form-control" value="{{ $payment->PaymentDate }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Amount Paid</label>
            <input type="number" name="Amount" step="0.01" class="form-control" value="{{ $payment->Amount }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Mode</label>
            <select name="PaymentMode" class="form-select">
                <option value="">--Select Payment Mode--</option>
                @foreach ($paymentModes as $mode)
                    <option value="{{ $mode->ID }}" {{ $payment->PaymentMode == $mode->ID ? 'selected' : '' }}>
                        {{ $mode->Description }}
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
@endsection
