@extends('layouts.app')
@section('title', 'Initiate Claim Closure')

@section('content')
    <div class="container mt-4">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('bancassurance.claims.storeClosureFromList') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Select Claim to Close <span class="text-danger">*</span></label>
                <select name="ClaimId" class="form-select" required>
                    <option value="">-- Choose Claim --</option>
                    @foreach($claims as $claim)
                        <option value="{{ $claim->ClaimId}}">
                            {{ $claim->claim->policy->PolicyNumber }} – {{ $claim->claim->policy->customer->FullName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Closure Status <span class="text-danger">*</span></label>
                <select name="FinalStatus" class="form-select" required>
                    <option value="">-- Select Status --</option>
                    @foreach ($status as $case)
                        <option value="{{ $case->value }}">{{ $case->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Closure Date <span class="text-danger">*</span></label>
                <input type="date" name="ClosureDate" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                <textarea name="FinalRemarks" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Close Claim</button>
                <a href="{{ route('bancassurance.claims.closed') }}" class="btn btn-secondary">Back to Closed Claims</a>
            </div>
        </form>
    </div>
@endsection
