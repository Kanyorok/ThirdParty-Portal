@extends('layouts.app')
@section('title', 'Insurance Products')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3 text-end">
        <a href="{{ route('bancassurance.products.create') }}" class="btn btn-primary">New Product</a>
    </div>
        <table  id='InsuranceProduct' class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>         
                    <th>Provider</th>
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
                    <td>{{ $product->provider->Name ?? '-' }}</td>
                    <td>{{ $product->Name ?? '-' }}</td>
                    <td>{{ $product->type->Description ?? '-' }}</td>
                    <td>{{ $product->Description ?? '-' }}</td>
                    <td>
                        @if($product->IsActive)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('bancassurance.products.edit', $product->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('bancassurance.products.destroy', $product->Id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure you want to delete this Insurance Product?');">Delete
                        </button>
                    </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#InsuranceProduct').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
