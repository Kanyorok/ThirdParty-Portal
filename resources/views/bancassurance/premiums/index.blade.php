@extends('layouts.app')
@section('title', 'Premium Payments')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">

    {{-- Action Button --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('bancassurance.premiums.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Record Premium Payment
        </a>
    </div>

    {{-- Premium Payments Table --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <table id="PremiumPaymentsTable" class="table table-striped table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th>Policy Number</th>
                        <th>Customer ID</th>
                        <th>Payment Frequency</th>
                        <th>Payment Date</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $payment->policies->PolicyNumber ?? '-' }}</td>
                            <td>{{ $payment->CustomerID ?? 'N/A' }}</td>
                            <td>{{ $payment->PaymentFrequency ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->PaymentDate)->format('d M Y') }}</td>                           <td class="text-end">{{ number_format($payment->Amount, 2) }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('bancassurance.premiums.show', $payment->Id) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('bancassurance.premiums.printReceipt', $payment->Id) }}" target="_blank" class="btn btn-sm btn-secondary" title="Print">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <form action="{{ route('bancassurance.premiums.destroy', $payment->Id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Return Payment"
                                            onclick="return confirm('Are you sure you want to return this payment?');">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No premium payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#PremiumPaymentsTable').DataTable({
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
