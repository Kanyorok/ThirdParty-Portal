@extends('layouts.app')
@section('title', 'Edit GL Posting Map')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">✏️ Edit GL Posting Map</h4>

        <form method="POST" action="{{ route('glpostingmap.update', $mapping->Id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Module</label>
                <input type="text" name="Module" class="form-control" value="{{ $mapping->Module }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Source Document Type</label>
                <input type="text" name="SourceDocType" class="form-control" value="{{ $mapping->SourceDocType }}"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Transaction Type</label>
                <select name="TransactionTypeID" class="form-select" required>
                    @foreach ($transactionTypes as $type)
                        <option
                            value="{{ $type->Id }}" {{ $mapping->TransactionTypeID == $type->Id ? 'selected' : '' }}>
                            {{ $type->Code }} — {{ $type->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Debit GL</label>
                <input type="text" name="DebitGL" class="form-control" value="{{ $mapping->DebitGL }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Credit GL</label>
                <input type="text" name="CreditGL" class="form-control" value="{{ $mapping->CreditGL }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Narration</label>
                <input type="text" name="PostingNarration" class="form-control"
                       value="{{ $mapping->PostingNarration }}">
            </div>

            <button type="submit" class="btn btn-success">💾 Update Mapping</button>
            <a href="{{ route('glpostingmap.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
