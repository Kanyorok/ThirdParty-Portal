@extends('layouts.app')

@section('title', 'Closed Claims')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    /* 🧩 Table Styling */
    #claimclosed thead th {
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

    /* ✨ Badges */
    .badge {
        font-size: 0.85rem;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- ✅ Header / Action --}}
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('bancassurance.claims.initiateClosureForm') }}"
            class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Initiate Closure
        </a>
    </div>

    <p class="text-muted small mb-3">
        <i class="bi bi-file-earmark-check me-2 text-primary"></i>
        List of all closed claims.
    </p>

    {{-- ✅ Success Message --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-pill py-2 px-3 mb-3 shadow-sm" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- ✅ Closed Claims Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="claimclosed" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Claim Type</th>
                            <th>Policy Number</th>
                            <th>Customer</th>
                            <th>Approved Amount</th>
                            <th>Closure Status</th>
                            <th>Closure Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($closedClaims as $claim)
                        <tr>
                            <td class="text-center">{{ $claim->Id ?? '-' }}</td>
                            <td>{{ $claim->claim->claimtype->Description ?? '-' }}</td>
                            <td>{{ $claim->claim->policy->PolicyNumber ?? '-' }}</td>
                            <td>{{ $claim->claim->policy->customer->ThirdParty->ThirdPartyName ?? '-' }}</td>
                            <td>{{ number_format($claim->paidamount->PaymentAmount, 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-success">
                                    {{ $claim->FinalStatus->label() }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($claim->ClosureDate)->format('d M Y') }}</td>                        </tr>
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
    $(document).ready(function() {
        $('#claimclosed').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search closed claims..."
            }
        });
    });
</script>
@endsection