@extends('layouts.app')
@section('title', 'Insurance Product Riders')

@section('styles')

<style>
    /* 🧩 Table Styling */
    #InsuranceProductRider thead th {
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
        font-size: 0.75rem;
    }
    .btn i {
        vertical-align: middle;
        font-size: 0.7rem; /* smaller icons */
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

    {{-- Header / Action --}}
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('bancassurance.riders.create') }}" 
           class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Add Rider
        </a>
    </div>

    {{-- Info Note --}}
    <p class="text-muted small mb-3">
        <i class="bi bi-archive me-2 text-primary"></i>
        Below is the list of all product riders and add-ons.
    </p>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-pill py-2 px-3 mb-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Riders Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="InsuranceProductRider" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Provider</th>
                            <th>Product</th>
                            <th>Rider Name</th>
                            <th>Additional Premium</th>
                            <th>Optional?</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riders as $rider)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $rider->provider->Name ?? '-'}}</td>
                            <td>{{ $rider->product->Name ?? '-'}}</td>
                            <td>{{ $rider->RiderName ?? '-'}}</td>
                            <td class="text-end">{{ number_format($rider->AdditionalPremium, 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $rider->IsOptional ? 'info' : 'secondary' }}">
                                    {{ $rider->IsOptional ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $rider->IsActive ? 'success' : 'danger' }}">
                                    {{ $rider->IsActive ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('bancassurance.riders.edit', $rider->Id) }}" 
                                       class="btn btn-outline-warning rounded-pill px-2" 
                                       title="Edit Rider">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('bancassurance.riders.destroy', $rider->Id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger rounded-pill px-2" 
                                                onclick="return confirm('Are you sure you want to delete this Rider?');" 
                                                title="Delete Rider">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
        $('#InsuranceProductRider').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search riders..."
            },
            columnDefs: [
                { orderable: false, targets: [7] } // Disable sorting on Actions
            ]
        });
    });
</script>
@endsection
