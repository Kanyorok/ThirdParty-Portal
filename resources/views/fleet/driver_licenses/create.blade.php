@extends('layouts.app')
@section('title', 'Add License Entry')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">📝 Add License Entry – {{ $driver->FullName }}</h4>

        <form action="{{ route('fleet.licenses.store') }}" method="POST">
            @csrf

            <input type="hidden" name="DriverID" value="{{ $driver->DriverID }}">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="LicenseNumber" class="form-label">License Number</label>
                    <input type="text" name="LicenseNumber" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="LicenseCategory" class="form-label">Category</label>
                    <input type="text" name="LicenseCategory" class="form-control">
                </div>

                <div class="col-md-4">
                    <label for="IssueDate" class="form-label">Issue Date</label>
                    <input type="date" name="IssueDate" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label for="ExpiryDate" class="form-label">Expiry Date</label>
                    <input type="date" name="ExpiryDate" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label for="RenewalDate" class="form-label">Renewal Date</label>
                    <input type="date" name="RenewalDate" class="form-control">
                </div>

                <div class="col-md-12">
                    <label for="Notes" class="form-label">Notes</label>
                    <textarea name="Notes" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">💾 Save Entry</button>
                <a href="{{ route('fleet.licenses.index', $driver->DriverID) }}"
                   class="btn btn-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
@endsection
