@extends('layouts.app')

@section('title', 'Commission Payout History')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* 🧩 Table Styling */
    #payout thead th {
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

    /* 🎨 Badges & Buttons */
    .badge {
        font-size: 0.85rem;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    <p class="text-muted small mb-3">
        <i class="bi bi-cash-stack me-2 text-primary"></i>
        The list below consists of all commission payouts made.
    </p>

    {{-- ✅ Payout Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="payout" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Policy Number</th>
                            <th>Paid Amount</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payouts as $p)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $p->policies->PolicyNumber ?? '-'}}</td>
                            <td class="text-end">{{ number_format($p->PaidAmount, 2) ?? '-'}}</td>
                            <td>{{ $p->PayoutReference ?? '-'}}</td>
                            <td>{{ \Carbon\Carbon::parse($p->PaymentDate)->format('d M Y') ?? '-'}}</td>
                            <td>{{ $p->paymentmodes->Description ?? '-'}}</td>
                            <td>{{ $p->Remarks ?? '-'}}</td>
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
    $(document).ready(function() {
        $('#payout').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search payouts..."
            }
        });
    });
</script>
@endsection