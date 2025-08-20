@extends('layouts.app')
@section('title', 'Initiate Policy Renewal')

@section('content')
<div class="container mt-4">
    <h4>🔁 Renew Policy – {{ $policy->PolicyNumber }}</h4>

    <form method="POST" action="{{ route('bancassurance.policies.storeRenewal', $policy->Id) }}">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Current Policy End Date</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d M Y') }}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Renewal Date</label>
                <input type="date" name="RenewalDate" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">New Start Date</label>
                <input type="date" name="NewStartDate" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">New End Date</label>
                <input type="date" name="NewEndDate" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes / Comments</label>
            <textarea name="Notes" class="form-control" rows="3" placeholder="Optional comments..."></textarea>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">
                🔁 Confirm Renewal
            </button>
        </div>
    </form>
</div>
@endsection
