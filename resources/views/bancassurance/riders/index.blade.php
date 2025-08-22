@extends('layouts.app')
@section('title', 'Insurance Product Riders')

@section('content')
    <div class="container mt-4">
        <h4>🧩 Riders & Add-ons</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="mb-3 text-end">
            <a href="{{ route('bancassurance.riders.create') }}" class="btn btn-primary">➕ Add Rider</a>
        </div>

        @if($riders->isEmpty())
            <p class="text-muted">No riders added yet.</p>
        @else
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Provider</th>
                    <th>Product</th>
                    <th>Rider Name</th>
                    <th>Description</th>
                    <th>Additional Premium (KES)</th>
                    <th>Optional?</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
                </thead>
                <tbody>
                @foreach($riders as $rider)
                    <tr>
                        <td>{{ $rider->Id }}</td>
                        <td>{{ $rider->ProviderName }}</td>
                        <td>{{ $rider->ProductName }}</td>
                        <td>{{ $rider->RiderName }}</td>
                        <td>{{ $rider->Description ?? '-' }}</td>
                        <td>{{ number_format($rider->AdditionalPremium, 2) }}</td>
                        <td>
                        <span class="badge bg-{{ $rider->IsOptional ? 'info' : 'secondary' }}">
                            {{ $rider->IsOptional ? 'Yes' : 'No' }}
                        </span>
                        </td>
                        <td>
                        <span class="badge bg-{{ $rider->IsActive ? 'success' : 'danger' }}">
                            {{ $rider->IsActive ? 'Active' : 'Inactive' }}
                        </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($rider->CreatedAt)->format('d M Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
