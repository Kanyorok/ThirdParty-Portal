@extends('layouts.app')
@section('title', 'Mapped Products to Insurers')

@section('content')
    <div class="container mt-4">
        <h4>📦 Mapped Insurance Products</h4>
        <div class="text-end mb-3">
            <a href="{{ route('bancassurance.products.mapped.create') }}" class="btn btn-primary">
                ➕ Map Product to Provider
            </a>
        </div>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Product</th>
                <th>Insurer</th>
                <th>Policy Type</th>
                <th>Custom Name</th>
                <th>Commission Type</th>
            </tr>
            </thead>
            <tbody>
            @foreach($mappedProducts as $item)
            <tr>     
                <td>{{ $item->ProductName }}</td>
                <td>{{ $item->ProviderName }}</td>
                <td>{{ $item->PolicyTypeName }}</td>
                <td>{{ $item->CustomName }}</td>
                <td>{{ $item->CommissionType }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
