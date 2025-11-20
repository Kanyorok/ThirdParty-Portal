@extends('layouts.app')

@section('title', 'Insurance Claims Register')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    /* 🧩 Table Styling */
    #claim thead th {
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

    /* 🎨 Button Group & Badge Styling */
    .btn-group .btn {
        margin-right: 4px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .badge {
        font-size: 0.85rem;
    }

    .btn i {
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- ✅ Header / Action --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('bancassurance.claims.create') }}"
            class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Initiate New Claim
        </a>
        <a href="{{ route('bancassurance.claims.closed') }}"
            class="btn btn-sm btn-outline-secondary rounded-pill shadow-sm">
            View Closed Claims
        </a>
    </div>

    <p class="text-muted small mb-3">
        <i class="bi bi-file-earmark-text me-2 text-primary"></i>
        List of all initiated insurance claims.
    </p>

    {{-- ✅ Claims Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="claim" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Policy No.</th>
                            <th>Claim Type</th>
                            <th>Claim Reason</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($claims as $claim)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $claim->policy->PolicyNumber ?? '-'}}</td>
                            <td>{{ $claim->claimtype->Description ?? '-'}}</td>
                            <td>{{ $claim->ClaimReason ?? '-'}}</td>
                            <td>{{ number_format($claim->ClaimAmount, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($claim->ClaimDate)->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">
                                    {{ $claim->status->Description ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('bancassurance.claims.assessForm', $claim->Id) }}"
                                        class="btn btn-outline-info rounded-pill px-2" title="Assess Claim">
                                        <i class="bi bi-check-circle"></i> Assess
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                No claims found.
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
        $('#claim').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search claims..."
            },
            columnDefs: [{
                    orderable: false,
                    targets: [7]
                } // Disable sorting on Actions
            ]
        });
    });
</script>
@endsection