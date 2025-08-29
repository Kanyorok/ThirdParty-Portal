@extends('layouts.app')

@section('title', 'Suppliers List')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" xintegrity="sha384-l+Smptr1K+gHDY4BMeKiX4pZKdfbJlZWc8rUE9wRfRC/B7RrxdCwq5Gk5P5c9f2u" crossorigin="anonymous">
<style>
    .status-pill {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 999px;
        font-weight: 600;
        color: white;
    }

    .status-pill.approved {
        background-color: #198754;
    }

    .status-pill.pending {
        background-color: #ffc107;
        color: #212529;
    }

    .status-pill.rejected {
        background-color: #dc3545;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 1;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="fw-bold mb-1"><i class="bi bi-truck me-2"></i>Suppliers</h3>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary shadow-sm d-flex align-items-center">
            <i class="fa fa-plus-circle me-2"></i>New Supplier
        </a>
    </div>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    <div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="suppliers-table" class="table table-hover table-bordered align-middle mb-0 w-100">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Index</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Category</th>
                            <th>Email</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables renders here --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" xintegrity="sha384-H+K7U5CnXl1h5ywQIfXbsE5tRysvOa8u/9KTJgXtAlLzYnM4ecGiZ1c1HvrcfDKS" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" xintegrity="sha384-lD2zUvUpY2X3rT/2xTytH9+EvflYFbOPa5zAiL5BQFqZffw5Clm1rKHvx3MHeQxT" crossorigin="anonymous"></script>
<script>
    const ProcurementUI = {
        initDataTable() {
            const table = $('#suppliers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("suppliers.index") }}',
                },
                columns: [{
                        data: 'Id',
                        name: 'Id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'ThirdPartyName',
                        name: 'ThirdPartyName'
                    },
                    {
                        data: 'Prequalified',
                        name: 'IsPrequalified',
                        render: function(data) {
                            if (data === 'Yes') return '<span class="status-pill approved">Approved</span>';
                            if (data === 'No') return '<span class="status-pill rejected">Rejected</span>';
                            return '<span class="status-pill pending">Pending</span>';
                        }
                    },
                    {
                        data: 'category_names',
                        name: 'category_names'
                    },
                    {
                        data: 'Email',
                        name: 'Email'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                dom: 'lfrtip',
                pageLength: 10,
                responsive: true,
                language: {
                    emptyTable: "Oops. No suppliers found.",
                    loadingRecords: "Loading...",
                    search: "Search:",
                    searchPlaceholder: "Search..."
                }
            });
        }
    };

    $(document).ready(function() {
        ProcurementUI.initDataTable();
    });
</script>
@endsection