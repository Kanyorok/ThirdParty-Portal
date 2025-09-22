@extends('layouts.app')
@section('title', 'Premium Payments')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">

            <a href="{{ route('bancassurance.premiums.create') }}" class="btn btn-primary">Record Premium Payment</a>
        </div>

        <table id="PremiumPaymentsTable" class="table table-striped table-bordered mt-3">
            <thead>
            <tr>
                <th>#</th>
                <th>Policy Number</th>
                <th>Customer ID</th>
                <th>Payment Frequency</th>
                <th>Payment Date</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $payment->policies->PolicyNumber ?? '-'}}</td>
                    <td>{{ $payment->CustomerID ?? 'N/A' }}</td>
                    <td>{{ $payment->PaymentFrequency ?? '-'}}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->PaymentDate)->format('d/m/Y') }}</td>
                    <td>{{ number_format($payment->Amount, 2) }}</td>
                    <td>
                        <a href="{{ route('bancassurance.premiums.show', $payment->Id) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('bancassurance.premiums.printReceipt', $payment->Id) }}"
                           class="btn btn-sm btn-secondary">Print Receipt</a>
                        <a href="{{ route('bancassurance.premiums.edit', $payment->Id) }}"
                           class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('bancassurance.premiums.destroy', $payment->Id) }}" method="POST"
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this payment?');">Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
            @endforelse
            </tbody>
        </table>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#PremiumPaymentsTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
