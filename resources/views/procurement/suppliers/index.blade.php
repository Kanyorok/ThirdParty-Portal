@extends('layouts.app')

@section('title', 'Suppliers List')

@section('content')
<div class="container">
    <h3>All Suppliers</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('suppliers.create') }}" class="btn btn-primary mb-3">+ New Supplier</a>

    @if($suppliers->count())
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Supplier Name</th>
                    <th>Prequalification Status</th>
                    <th>Category</th>
                    <th>Contact Email</th>
                    <th>Contact Phone</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $supplier->SupplierName }}</td>
                        <td>
                            @if($supplier->IsPrequalified)
                                <span class="badge bg-success">Prequalified</span>
                            @else
                                <span class="badge bg-warning">Not Prequalified</span>
                            @endif
                        </td>
                        <td>{{ $supplier->category->Name ?? '-' }}</td>
                        <td>{{ $supplier->ContactEmail }}</td>
                        <td>{{ $supplier->ContactPhone }}</td>
                        <td>{{ $supplier->Address }}</td>
                        <td>
                            <a href="{{ route('suppliers.edit', $supplier->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('suppliers.destroy', $supplier->Id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this supplier?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No suppliers found.</p>
    @endif
</div>
@endsection
