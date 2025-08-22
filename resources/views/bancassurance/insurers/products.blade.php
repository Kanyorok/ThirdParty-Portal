@extends('layouts.app')
@section('title', 'Mapped Products – ' . $provider->Name)

@section('content')
<div class="container mt-4">
    <h4>Products for {{ $provider->Name }}</h4>

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
                    <th>Description </th>
                    <th>Is Active </th>                 
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $product->Name }}</td>
                    <td>{{ $product->Type }}</td>
                    <td>{{ $product->Description }}</td>
                    <td>{{ $product->IsActive ? 'Yes' : 'No' }}</td>              
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
