@extends('layouts.app')
@section('title', 'Tenant Maintenance')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        /* Make table responsive */
        .table-responsive {
            overflow-x: auto;
        }

        /* Prevent long text from breaking layout */
        table td, table th {
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        /* Allow selected cells to wrap (email, name, phone) */
        .wrap-text {
            white-space: normal !important;
        }

        /* Style table header */
        table.dataTable thead th {
            background: #f8f9fa;
            font-weight: 600;
        }

        /* Make action buttons stay inside the table */
        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
    </style>
@endsection


@section('content')
<div class="container mt-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('thirdparty.parties.index') }}" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i> Add Tenant
        </a>
    </div>

    <p class="text-muted">
        <small>This screen displays a list of all tenants — both active and inactive.</small>
    </p>

    @if($newtenants->count())
        <div class="card shadow-sm">
            <div class="card-body">

                <!-- Responsive Wrapper -->
                <div class="table-responsive">
                    <table id="addtenant"
                           class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th>Type</th>
                            <th class="wrap-text">Name</th>
                            <th class="wrap-text">ID / Reg No.</th>
                            <th class="wrap-text">Phone</th>
                            <th class="wrap-text">Email</th>
                            <th>Status</th>
                            <th style="width: 20%">Actions</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($newtenants as $newtenant)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $newtenant->type->Description ?? '-' }}</td>

                                <td class="wrap-text">{{ $newtenant->thirdParty->ThirdPartyName ?? '-' }}</td>

                                <td class="wrap-text">{{ $newtenant->thirdParty->RegistrationNumber ?? '-' }}</td>

                                <td class="wrap-text">{{ $newtenant->thirdParty->Phone ?? '-' }}</td>

                                <td class="wrap-text">{{ $newtenant->thirdParty->Email ?? '-' }}</td>

                                <td>
                                    @if($newtenant->IsActive)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle me-1"></i> Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('addtenant.show', $newtenant->Id) }}"
                                           class="btn btn-sm btn-info text-white"
                                           title="View Tenant">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>
                <!-- END Responsive Wrapper -->

            </div>
        </div>
    @else
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i> No tenants registered yet.
        </div>
    @endif
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#addtenant').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
