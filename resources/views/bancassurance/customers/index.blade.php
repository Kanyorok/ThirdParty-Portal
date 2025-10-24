@extends('layouts.app')

@section('title', 'Customer List')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    /* 🧩 Table Styling */
    #Customerregistry thead th {
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

    /* 🎨 Button Group Spacing */
    .btn-group .btn {
        margin-right: 4px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }

    /* ✨ Small Touches */
    .badge {
        font-size: 0.8rem;
    }
    .btn i {
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- ✅ Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-pill py-2 px-3 mb-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ✅ Header --}}
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('bancassurance.customers.create') }}" 
           class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="bi bi-person-plus me-1"></i> New Customer
        </a>
    </div>

    <p class="text-muted small mb-3">
        <i class="bi bi-people-fill me-2 text-primary"></i>
        Below is the list of all registered customers and their details.
    </p>

    {{-- ✅ Data Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="Customerregistry" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>National ID</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Date of Birth</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $customer->thirdParty->ThirdPartyName ?? '-' }}</td>
                                <td>{{ $customer->NationalID ?? '-' }}</td>
                                <td>{{ $customer->thirdParty->Phone ?? '-' }}</td>
                                <td>{{ $customer->thirdParty->Email ?? '-' }}</td>
                                <td>
                                    {{ $customer->DateOfBirth ? \Carbon\Carbon::parse($customer->DateOfBirth)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- Portfolio --}}
                                        <a href="{{ route('bancassurance.customers.portfolio', $customer->Id) }}" 
                                           class="btn btn-outline-info rounded-pill px-2" 
                                           title="View Portfolio">
                                            <i class="bi bi-folder2-open"></i>
                                        </a>

                                        {{-- Communication --}}
                                        <a href="{{ route('bancassurance.customers.communication.index') }}" 
                                           class="btn btn-outline-secondary rounded-pill px-2" 
                                           title="Customer Communication">
                                            <i class="bi bi-chat-left-text"></i>
                                        </a>

                                        {{-- Beneficiary --}}
                                        <a href="{{ route('bancassurance.customers.beneficiaries.create') }}" 
                                           class="btn btn-outline-primary rounded-pill px-2" 
                                           title="Add Beneficiary">
                                            <i class="bi bi-person-plus"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                    No customers found.
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
    $(document).ready(function () {
        $('#Customerregistry').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search customers..."
            },
            columnDefs: [
                { orderable: false, targets: [6] } // Disable sorting on Actions
            ]
        });
    });
</script>
@endsection
