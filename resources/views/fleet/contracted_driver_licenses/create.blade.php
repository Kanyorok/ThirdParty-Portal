@extends('layouts.app')
@section('title', 'Add License – Contracted Driver')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">➕ Add License – {{ $driver->FullName }}</h4>

    <form method="POST" action="{{ route('fleet.contracted_driver_licenses.store', $driver->ID) }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">License Number</label>
                <input type="text" name="LicenseNumber" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Category</label>
                <input type="text" name="LicenseCategory" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Issue Date</label>
                <input type="date" name="IssueDate" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Expiry Date</label>
                <input type="date" name="ExpiryDate" class="form-control" required>
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">💾 Save License</button>
        </div>
    </form>
</div>
@endsection
