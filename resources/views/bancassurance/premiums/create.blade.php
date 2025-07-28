@extends('layouts.app')
@section('title', 'Record Premium Payment')

@section('content')
<div class="container mt-4">
    <h4>💳 Record Premium Payment</h4>

    <form method="POST" action="{{ route('bancassurance.premiums.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Select Policy</label>
            <select name="PolicyID" class="form-select" required>
                <option value="">-- Select Policy --</option>
                @foreach($policies as $p)
                    <option value="{{ $p->Id }}">
                        {{ $p->PolicyNumber }} - {{ $p->CustomerName }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Date</label>
            <input type="date" name="PaymentDate" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Amount Paid</label>
            <input type="number" name="Amount" step="0.01" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Mode</label>
            <select name="PaymentMode" class="form-select" required>
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Mobile Money">Mobile Money</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Reference Number</label>
            <input type="text" name="ReferenceNumber" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Notes (optional)</label>
            <textarea name="Notes" class="form-control" rows="2"></textarea>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">💾 Record Payment</button>
        </div>
    </form>
</div>
@endsection
