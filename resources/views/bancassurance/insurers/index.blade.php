@extends('layouts.app')
@section('title', 'Insurance Providers')

@section('content')
<div class="container mt-4">
    <h4>🏢 Insurance Providers</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3 text-end">
        <a href="{{ route('bancassurance.insurers.create') }}" class="btn btn-primary">➕ Add Provider</a>
    </div>

    @if($providers->isEmpty())
        <p class="text-muted">No providers registered yet.</p>
    @else
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Country</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($providers as $provider)
                <tr>
                    <td>{{ $provider->Id }}</td>
                    <td>{{ $provider->Name }}</td>
                    <td>{{ $provider->Country ?? '-' }}</td>
                    <td>{{ $provider->ContactPerson ?? '-' }}</td>
                    <td>{{ $provider->Email ?? '-' }}</td>
                    <td>{{ $provider->Phone ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $provider->IsActive ? 'success' : 'secondary' }}">
                            {{ $provider->IsActive ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($provider->CreatedAt)->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('bancassurance.insurers.edit', $provider->Id) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                        <a href="{{ route('bancassurance.insurers.products', $provider->Id) }}" class="btn btn-sm btn-info">📦 View Products</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
