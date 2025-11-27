@extends('layouts.app')

@section('title', 'Commissions Earned')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* 🧩 Table Styling */
    #commissionsTable thead th {
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

    /* 🎨 Buttons */
    .payout-btn {
        font-size: 0.85rem;
        padding: 0.25rem 0.6rem;
    }

    .pending-btn {
        font-size: 0.8rem;
        padding: 0.25rem 0.6rem;
    }

    /* ✨ Badge styling for Status */
    .status-badge {
        font-size: 0.85rem;
        padding: 0.35em 0.6em;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- ✅ Header / Optional filter form can go here --}}
    <p class="text-muted small mb-3">
        <i class="bi bi-currency-dollar me-2 text-primary"></i>
        List of commissions earned and their payout status.
    </p>

    {{-- ✅ Commissions Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="commissionsTable" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Policy</th>
                            <th>Claim Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $e)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $e->policy->PolicyNumber ?? '-'}}</td>
                            <td>{{ $e->claimtype->Description ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($e->ClaimAmount, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($e->ClaimDate)->format('d M Y') }}</td>                           <td class="text-center">
                                @if(strtolower($e->status->Description ?? '') === 'paid')
                                <span class="badge bg-success status-badge">Paid</span>
                                @else
                                <span class="badge bg-warning text-dark status-badge">Pending</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(strtolower($e->status->Description ?? '') === 'paid')
                                <a href="{{ route('bancassurance.commissions.payouts.pay', $e->Id) }}"
                                    class="btn btn-sm btn-success payout-btn">
                                    <i class="bi bi-cash-stack me-1"></i> Payout
                                </a>
                                @else
                                <button class="btn btn-sm btn-secondary pending-btn" disabled>
                                    ⌛ Pending Payment
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
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
        $('#commissionsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search commissions..."
            },
            columnDefs: [{
                    orderable: false,
                    targets: [6]
                } // Actions column
            ]
        });
    });
</script>
@endsection