@extends('layouts.app')

@section('title', 'Suppliers List')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold"> Suppliers List</h3>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($suppliers->count())
        <div class="table-responsive">
            <table id="suppliers" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Supplier Name</th>
                        <th>Prequalification Status</th>
                        <th>Category</th>
                        <th>Contact Email</th>
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
                                    <span class="badge bg-warning text-dark">Not Prequalified</span>
                                @endif
                            </td>
                            <td>{{ $supplier->category->Name ?? '-' }}</td>
                            <td>{{ $supplier->ContactEmail ?? '-' }}</td>
                            <td class="d-flex gap-1">
                                <a href="{{ route('suppliers.show', $supplier->Id) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('suppliers.edit', $supplier->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('suppliers.destroy', $supplier->Id) }}" method="POST" onsubmit="return confirm('Delete this supplier?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info">No suppliers found.</div>
    @endif
</div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#suppliers').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
