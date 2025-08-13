@extends('layouts.app')
@section('title', 'Add Insurance Record')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">➕ Add Insurance Record</h4>

    <form action="{{ route('fleet.insurance_tracker.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="VehicleID" class="form-label">Vehicle</label>
            <select name="VehicleID" id="VehicleID" class="form-select" required>
                <option value="">-- Select Vehicle --</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->VehicleID }}">{{ $v->RegistrationNumber }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="PolicyNumber" class="form-label">Policy Number</label>
            <input type="text" class="form-control" name="PolicyNumber" required>
        </div>

        <div class="mb-3">
            <label for="Provider" class="form-label">Provider</label>
            <input type="text" class="form-control" name="Provider" required>
        </div>

        <div class="mb-3">
            <label for="PremiumAmount" class="form-label">Premium Amount</label>
            <input type="number" step="0.01" class="form-control" name="PremiumAmount" required>
        </div>

        <div class="mb-3">
            <label for="StartDate" class="form-label">Start Date</label>
            <input type="date" class="form-control" name="StartDate" required>
        </div>

        <div class="mb-3">
            <label for="ExpiryDate" class="form-label">Expiry Date</label>
            <input type="date" class="form-control" name="ExpiryDate" required>
        </div>

        <div class="mb-3">
            <label for="Status" class="form-label">Status</label>
            <select name="Status" class="form-select" required>
                <option value="Active">Active</option>
                <option value="Expired">Expired</option>
                <option value="Pending">Pending</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="Notes" class="form-label">Notes</label>
            <textarea name="Notes" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-success">💾 Save Insurance Record</button>
    </form>
</div>
@endsection
