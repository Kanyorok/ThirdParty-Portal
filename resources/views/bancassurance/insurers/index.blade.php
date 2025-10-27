@extends('layouts.app')

@section('title', 'Insurance Providers')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* 🧩 Table Styling */
    #InsuranceProvider thead th {
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

    /* 🎨 Badge & Button Styling */
    .badge {
        font-size: 0.85rem;
    }
    .btn i {
        vertical-align: middle;
        font-size: 0.85rem;
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

    {{-- Header Action Button --}}
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('bancassurance.insurers.create') }}" 
           class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Add Provider
        </a>
    </div>

    {{-- Info Note --}}
    <p class="text-muted small mb-3">
        <i class="bi bi-building me-2 text-primary"></i>
        List of all insurance providers in the system.
    </p>

    {{-- Providers Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="InsuranceProvider" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Provider Number</th>
                            <th>Name</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($providers as $provider)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $provider->InsuranceProviderNO ?? '-' }}</td>
                            <td>{{ $provider->Name ?? '-' }}</td>
                            <td>{{ $provider->ContactPerson ?? '-' }}</td>
                            <td>{{ $provider->Email ?? '-' }}</td>
                            <td>{{ $provider->Phone ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $provider->IsActive ? 'success' : 'secondary' }}">
                                    {{ $provider->IsActive ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('bancassurance.insurers.products', $provider->Id) }}" 
                                       class="btn btn-outline-info rounded-pill px-2" 
                                       title="View Products">
                                        <i class="bi bi-box-seam"></i>
                                    </a>
                                    <a href="{{ route('bancassurance.insurers.edit', $provider->Id) }}" 
                                       class="btn btn-outline-warning rounded-pill px-2" 
                                       title="Edit Provider">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    @if($provider->getProductByProvider()->exists())
                                        <button class="btn btn-outline-secondary rounded-pill px-2" disabled 
                                                title="Provider in Use">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    @else
                                        <form action="{{ route('bancassurance.insurers.destroy', $provider->Id) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this Provider?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger rounded-pill px-2" title="Delete">
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
        $('#InsuranceProvider').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search providers..."
            },
            columnDefs: [
                { orderable: false, targets: [7] } // Disable sorting on Actions
            ]
        });
    });
</script>
@endsection
