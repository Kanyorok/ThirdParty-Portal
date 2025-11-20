@extends('layouts.app')

@section('title', 'Mapped Products – ' . $provider->Name)

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* 🧩 Table Styling */
    #productlist thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        text-align: center;
    }

    .table td,
    .table th {
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

    /* 🎨 Badges */
    .badge {
        font-size: 0.85rem;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Products for {{ $provider->Name }}</h4>
        <a href="{{ route('bancassurance.insurers.index') }}" class="btn btn-sm btn-secondary rounded-pill">
            <i class="bi bi-arrow-left-circle me-1"></i> Back
        </a>
    </div>

    {{-- Info Note --}}
    <p class="text-muted small mb-3">
        <i class="bi bi-box-seam me-2 text-primary"></i>
        Below is the list of products mapped to this provider.
    </p>

    {{-- Products Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="productlist" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Policy Type</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $product->Name ?? '-' }}</td>
                            <td>{{ $product->Type ?? '-' }}</td>
                            <td>{{ $product->Description ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $product->IsActive ? 'success' : 'secondary' }}">
                                    {{ $product->IsActive ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                No products found for this provider.
                            </td>
                        </tr>
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
    $(document).ready(function() {
        $('#productlist').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search products..."
            }
        });
    });
</script>
@endsection