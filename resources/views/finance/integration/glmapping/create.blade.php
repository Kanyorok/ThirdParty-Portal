@extends('layouts.app')
@section('title', 'GL Posting Map - Create')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🔗 GL Posting Map - Create</h4>

    <form method="POST" action="{{ route('glpostingmap.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Module</label>
            <input type="text" name="Module" class="form-control" placeholder="e.g., Procurement" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Source Document Type</label>
            <input type="text" name="SourceDocType" class="form-control" placeholder="e.g., GRN, Invoice" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Transaction Type</label>
            <select name="TransactionTypeID" class="form-select" required>
                <option value="" disabled selected>-- Select Transaction Type --</option>
                @foreach ($transactionTypes as $type)
                    <option value="{{ $type->Id }}">{{ $type->Code }} — {{ $type->Description }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Debit GL</label>
            <input type="text" name="DebitGL" class="form-control" placeholder="e.g., 1500" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Credit GL</label>
            <input type="text" name="CreditGL" class="form-control" placeholder="e.g., 2100" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Narration</label>
            <input type="text" name="PostingNarration" class="form-control" placeholder="e.g., Payables from GRN">
        </div>

        <button type="submit" class="btn btn-success">💾 Save Mapping</button>
        <a href="{{ route('glpostingmap.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
