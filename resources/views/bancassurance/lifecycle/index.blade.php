@extends('layouts.app')
@section('title', 'Product Lifecycle Tracker')

@section('content')
    <div class="container mt-4">
        <h4>🔄 Product Lifecycle Tracker</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @elseif(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($lifecycles->isEmpty())
            <p class="text-muted">No mapped provider products found.</p>
        @else
            <table class="table table-bordered table-hover mt-3">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Insurance Provider</th>
                    <th>Product Name</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($lifecycles as $item)
                    <tr>
                        <td>{{ $item->Id }}</td>
                        <td>{{ $item->ProviderName }}</td>
                        <td>{{ $item->ProductName }}</td>
                        <td>
                        <span class="badge bg-{{ $item->IsActive ? 'success' : 'secondary' }}">
                            {{ $item->IsActive ? 'Active' : 'Inactive' }}
                        </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($item->CreatedAt)->format('d M Y') }}</td>
                        <td>
                            <form action="{{ route('bancassurance.lifecycle.toggle', $item->Id) }}" method="POST"
                                  onsubmit="return confirm('Toggle status for this product?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    {{ $item->IsActive ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
