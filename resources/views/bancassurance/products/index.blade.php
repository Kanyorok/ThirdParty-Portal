@extends('layouts.app')
@section('title', 'Insurance Products')

@section('content')
<div class="container mt-4">
    <h4>📦 Insurance Products</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3 text-end">
        <a href="{{ route('bancassurance.products.create') }}" class="btn btn-primary">➕ New Product</a>
    </div>

    @if($products->isEmpty())
        <p class="text-muted">No insurance products found.</p>
    @else
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{ $product->Id }}</td>
                    <td>{{ $product->Name }}</td>
                    <td>{{ $product->Type ?? '-' }}</td>
                    <td>{{ $product->Description ?? '-' }}</td>
                    <td>
                        @if($product->IsActive)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('bancassurance.products.edit', $product->Id) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                        <a href="{{ route('bancassurance.products.map', $product->Id) }}" class="btn btn-sm btn-info">🔗 Map to Provider</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
