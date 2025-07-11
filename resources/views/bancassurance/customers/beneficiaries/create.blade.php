@extends('layouts.app')
@section('title', 'Add Beneficiary')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">➕ Add Beneficiary for: {{ $customer->FullName }}</h4>

    <form method="POST" action="{{ route('bancassurance.customers.beneficiaries.store', $customer->Id) }}">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="FullName" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">ID Number</label>
                <input type="text" name="IDNumber" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Relationship</label>
                <input type="text" name="Relationship" class="form-control" placeholder="e.g. Spouse, Child">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="Phone" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="Email" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">% Share</label>
                <input type="number" step="0.01" name="PercentageShare" class="form-control" placeholder="e.g. 50.00">
            </div>
        </div>

        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="IsPrimary" value="1" id="primaryCheck">
            <label class="form-check-label" for="primaryCheck">Primary Beneficiary</label>
        </div>

        <div class="text-end">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-save"></i> Save Beneficiary
            </button>
        </div>
    </form>
</div>
@endsection
