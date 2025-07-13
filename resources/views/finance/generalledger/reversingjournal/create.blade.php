@extends('layouts.app')
@section('title', 'Reversing Journal Entry')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">🔁 Reversing Journal Entry</h4>

        <form method="POST" action="#">
            @csrf
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Original Journal Ref #</label>
                    <input type="text" name="OriginalReferenceNumber" class="form-control"
                           placeholder="e.g., JV20240601">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Reversal Date</label>
                    <input type="date" name="ReversalDate" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Reason</label>
                    <input type="text" name="Reason" class="form-control" placeholder="e.g., Accrual reversal">
                </div>
            </div>

            <div class="alert alert-warning">
                This journal will automatically reverse the original entry lines.
            </div>

            <button class="btn btn-danger">Reverse Journal</button>
            <a href="/finance/journalentry" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
