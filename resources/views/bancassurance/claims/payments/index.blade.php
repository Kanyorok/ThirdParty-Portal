@extends('layouts.app')

@section('title', 'Claim Payments')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    /* 🧩 Table Styling */
    #claimpayment thead th {
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
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- ✅ Initiate Payment --}}
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('bancassurance.claims.payments.initiate') }}"
            class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Initiate Payment
        </a>
    </div>

    {{-- ✅ Payments Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="claimpayment" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Policy</th>
                            <th>Customer</th>
                            <th>Claim Type</th>
                            <th>Paid Amount</th>
                            <th>Payment Date</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $pay)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $pay->claim->policy->PolicyNumber ?? '-'}}</td>
                            <td>{{ $pay->claim->policy->customer->ThirdParty->ThirdPartyName ?? '-'}}</td>
                            <td>{{ $pay->claim->claimtype->Description ?? '-'}}</td>
                            <td class="text-end">{{ number_format($pay->PaymentAmount, 2) ?? '-'}}</td>
                            <td>{{ \Carbon\Carbon::parse($pay->PaymentDate)->format('d/m/Y') }}</td>
                            <td>{{ $pay->PaymentReference ?? '-'}}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                No payments found.
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
        $('#claimpayment').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search payments..."
            }
        });
    });
</script>
@endsection