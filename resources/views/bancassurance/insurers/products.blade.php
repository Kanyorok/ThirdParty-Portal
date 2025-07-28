@extends('layouts.app')
@section('title', 'Mapped Products – ' . $provider->Name)

@section('content')
<div class="container mt-4">
    <h4>📦 Products for {{ $provider->Name }}</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($products->isEmpty())
        <p class="text-muted">No products currently mapped.</p>
    @else
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Policy Type</th>
                    <th>Commission Type</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $product->ProductName }}</td>
                    <td>{{ $product->PolicyType }}</td>
                    <td>{{ $product->CommissionType }}</td>
                    <td>
                        <form method="POST" action="{{ route('bancassurance.insurers.products.detach', ['providerId' => $provider->Id, 'productId' => $product->MappingId]) }}" onsubmit="return confirm('Detach this product?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">🗑 Detach</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
