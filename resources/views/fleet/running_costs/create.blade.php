@extends('layouts.app')
@section('title', 'Log Running Cost')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">➕ Log Running Cost</h4>

    <form method="POST" action="{{ route('fleet.running_costs.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="VehicleID" class="form-label">Vehicle</label>
                <select name="VehicleID" class="form-select" required>
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->VehicleID }}">{{ $vehicle->RegistrationNumber }} - {{ $vehicle->Make }} {{ $vehicle->Model }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="CostType" class="form-label">Cost Type</label>
                <input type="text" name="CostType" class="form-control" placeholder="e.g. Tyres, Insurance, Oil" required>
            </div>

            <div class="col-md-4">
                <label for="CostDate" class="form-label">Cost Date</label>
                <input type="date" name="CostDate" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label for="Amount" class="form-label">Amount</label>
                <input type="number" step="0.01" name="Amount" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label for="Vendor" class="form-label">Vendor (Optional)</label>
                <input type="text" name="Vendor" class="form-control">
            </div>

            <div class="col-md-12">
                <label for="Notes" class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="3" placeholder="Additional remarks..."></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">💾 Save Entry</button>
        </div>
    </form>
</div>
@endsection
