@extends('layouts.app')

@section('title', 'Communication Log')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    /* 🧩 Table Styling */
    #Customercontacts thead th {
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

    /* 🎨 Buttons */
    .btn-action {
        display: flex;
        justify-content: center;
        gap: 6px;
    }
    .btn-action .btn {
        border-radius: 25px;
        padding: 4px 10px;
        font-size: 0.8rem;
        transition: all 0.2s ease-in-out;
    }
    .btn-action .btn i {
        font-size: 0.9rem;
        vertical-align: middle;
    }

    /* 🟡 Edit Button */
    .btn-outline-warning {
        color: #f0ad4e;
        border-color: #f0ad4e;
    }
    .btn-outline-warning:hover {
        background-color: #f0ad4e;
        color: #fff;
        box-shadow: 0 0 6px rgba(240, 173, 78, 0.5);
    }

    /* 🔴 Delete Button */
    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: #fff;
        box-shadow: 0 0 6px rgba(220, 53, 69, 0.4);
    }

    /* 🟢 Add New Button */
    .btn-primary {
        border-radius: 25px;
        padding: 6px 14px;
        transition: all 0.2s ease-in-out;
    }
    .btn-primary:hover {
        background-color: #0b5ed7;
        box-shadow: 0 0 6px rgba(13, 110, 253, 0.4);
    }

    /* ✨ Small Touches */
    .badge {
        font-size: 0.8rem;
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
        <a href="{{ route('bancassurance.customers.communication.create') }}" 
           class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> New Communication Log
        </a>
    </div>

    <p class="text-muted small mb-3">
        <i class="bi bi-chat-dots-fill me-2 text-primary"></i>
        Below is the list of all communication logs for customers.
    </p>

    {{-- ✅ Data Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="Customercontacts" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Summary</th>
                            <th>Handled By</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->customers->thirdParty->ThirdPartyName ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->ContactDate)->format('d/m/Y') }}</td>
                                <td>{{ $log->contacttypes->Description ?? '-' }}</td>
                                <td>{{ $log->Summary ?? '-' }}</td>
                                <td>{{ $log->employees->FirstName ?? '-' }}</td>
                                <td>{{ $log->Notes ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-action">
                                        {{-- ✏️ Edit --}}
                                        <a href="{{ route('bancassurance.customers.communication.edit', $log->Id) }}"
                                           class="btn btn-outline-warning" title="Edit Log">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        {{-- 🗑️ Delete --}}
                                        <form action="{{ route('bancassurance.customers.communication.destroy', $log->Id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this contact?');"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete Log">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                    No communication logs found.
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
        $('#Customercontacts').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search communication logs..."
            },
            columnDefs: [
                { orderable: false, targets: [6] } // Disable sorting on Actions
            ]
        });
    });
</script>
@endsection
