@extends('layouts.app')
@section('title', 'Close Claim')

@section('content')
<div class="container mt-4">
    <h4>🚫 Close Claim – #{{ $claim->Id }} (Policy #{{ $claim->PolicyID }})</h4>

    <form action="{{ route('bancassurance.claims.close', $claim->Id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Final Status</label>
            <select name="FinalStatus" class="form-select" required>
                <option value="">-- Select Final Status --</option>
                @foreach($statuses as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Closure Date</label>
            <input type="date" name="ClosureDate" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Final Remarks</label>
            <textarea name="FinalRemarks" class="form-control" rows="4" placeholder="Enter any final remarks (optional)"></textarea>
        </div>

        <button type="submit" class="btn btn-danger">🚫 Close Claim</button>
        <a href="{{ route('bancassurance.claims.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
