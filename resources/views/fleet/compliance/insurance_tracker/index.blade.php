@extends('layouts.app')
@section('title', 'Insurance Tracker')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🚗 Insurance Tracker</h4>

    <a href="{{ route('fleet.insurance_tracker.create') }}" class="btn btn-primary mb-3">➕ Add Insurance Record</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Vehicle</th>
                <th>Policy Number</th>
                <th>Provider</th>
                <th>Premium Amount</th>
                <th>Start Date</th>
                <th>Expiry Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ optional($record->vehicle)->RegistrationNumber }}</td>
                    <td>{{ $record->PolicyNumber }}</td>
                    <td>{{ $record->Provider }}</td>
                    <td>{{ number_format($record->PremiumAmount, 2) }}</td>
                    <td>{{ $record->StartDate }}</td>
                    <td>{{ $record->ExpiryDate }}</td>
                    <td>{{ $record->Status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
