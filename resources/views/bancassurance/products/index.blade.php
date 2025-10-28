@extends('layouts.app')

@section('title', 'Insurance Products')

@section('styles')

<style>
    /* 🧩 Table Styling */
    #InsuranceProduct thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        text-align: center;
    }
    .table td, .table th {
        vertical-align: middle !important;
    }
    table.dataTable tbody tr:hover {
        background-color: #f9fbfd;
    }

    /* 🔍 DataTables Inputs */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px;
        padding: 4px 12px;
        border: 1px solid #ced4da;
    }
    .dataTables_wrapper .dataTables_length select {
        border-radius: 20px;
        padding: 3px 10px;
        border: 1px solid #ced4da;
    }

    /* 🎨 Badges & Buttons */
    .badge {
        font-size: 0.75rem; /* slightly smaller */
    }
    .btn i {
        vertical-align: middle;
        font-size: 0.7rem; /* smaller action icons */
    }
    .btn-group .btn {
        margin-right: 4px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-pill py-2 px-3 mb-3 shadow-sm" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Header Action Button --}}
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('bancassurance.products.create') }}" 
           class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> New Product
        </a>
    </div>

    {{-- Info Note --}}
    <p class="text-muted small mb-3">
        <i class="bi bi-box-seam me-2 text-primary"></i>
        Below is the list of all insurance products.
    </p>

    {{-- Products Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="InsuranceProduct" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
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
                        @forelse($products as $product)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $product->provider->Name ?? '-' }}</td>
                            <td>{{ $product->Name ?? '-' }}</td>
                            <td>{{ $product->type->Description ?? '-' }}</td>
                            <td>{{ $product->Description ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $product->IsActive ? 'success' : 'secondary' }}">
                                    {{ $product->IsActive ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('bancassurance.products.edit', $product->Id) }}" 
                                       class="btn btn-outline-warning rounded-pill px-2" 
                                       title="Edit Product">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    @if($product->policies()->exists())
                                    <button class="btn btn-outline-secondary rounded-pill px-2" disabled 
                                            title="Product in Use">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                    @else
                                    <form action="{{ route('bancassurance.products.destroy', $product->Id) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger rounded-pill px-2" title="Delete Product">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#InsuranceProduct').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search products..."
            },
            columnDefs: [
                { orderable: false, targets: [6] } // Disable sorting on Actions
            ]
        });
    });
</script>
@endsection
