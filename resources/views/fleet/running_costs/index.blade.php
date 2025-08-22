@extends('layouts.app')
@section('title', 'Running Costs')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between mb-3">
            <h4 class="mb-0">🛢️ Running Cost Entries</h4>
            <a href="{{ route('fleet.running_costs.create') }}" class="btn btn-primary">➕ New Entry</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Vehicle</th>
                    <th>Cost Type</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Vendor</th>
                    <th>Notes</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($costs as $cost)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $cost->vehicle->RegistrationNumber ?? '-' }}</td>
                        <td>{{ $cost->CostType }}</td>
                        <td>{{ $cost->CostDate }}</td>
                        <td>{{ number_format($cost->Amount, 2) }}</td>
                        <td>{{ $cost->Vendor ?? '-' }}</td>
                        <td>{{ $cost->Notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No running cost records found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
