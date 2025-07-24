@extends('layouts.app')
@section('title', 'Rent Collection Dashboard')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🏠 Rent Collection Dashboard</h4>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="card-title">Collected</h6>
                    <h4>KES {{ number_format($collected) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h6 class="card-title">Due Soon</h6>
                    <h4>KES {{ number_format($dueSoon) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h6 class="card-title">Overdue</h6>
                    <h4>KES {{ number_format($overdue) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h6 class="card-title">Partial Payments</h6>
                    <h4>KES {{ number_format($partial) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters (static options for now) -->
    <form class="row g-2 mb-3">
        <div class="col-md-3">
            <select class="form-select">
                <option selected>All Branches</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select">
                <option selected>All Properties</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select">
                <option selected>All Tenants</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="month" class="form-control">
        </div>
    </form>

    <!-- Data Grid -->
    <div class="table-responsive mb-4">
        <table class="table table-striped table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tenant</th>
                    <th>Unit</th>
                    <th>Due Date</th>
                    <th>Amount Due</th>
                    <th>Amount Paid</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                @php
                    $tenant = $invoice->lease->tenant->TenantName ?? 'N/A';
                    $unit = $invoice->lease->unit->UnitCode ?? 'N/A';
                    $due = $invoice->RentAmount + $invoice->ServicesCharge + $invoice->ParkingFee + $invoice->OtherCharges;
                    $paid = $invoice->receipts->sum('AmountPaidNow');
                    $balance = $due - $paid;
                    $status = $paid == 0 ? 'Unpaid' : ($paid < $due ? 'Partial' : 'Paid');
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $tenant }}</td>
                    <td>{{ $unit }}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->InvoiceDate)->format('Y-m-d') }}</td>
                    <td>KES {{ number_format($due) }}</td>
                    <td>KES {{ number_format($paid) }}</td>
                    <td>
                        <span class="badge bg-{{ $status == 'Paid' ? 'success' : ($status == 'Partial' ? 'warning' : 'danger') }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td>
                        @foreach($invoice->receipts as $receipt)
                            <a href="{{ route('rentreceipt.show', $receipt->Id) }}" class="btn btn-sm btn-info mb-1">View</a>
                        @endforeach
                        @if($invoice->receipts->isEmpty())
                            <span class="text-muted">No Receipts</span>
                        @endif

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Chart Placeholder -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">📈 Collection Trend</h6>
        </div>
        <div class="card-body">
            <div style="height: 300px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                <span>[Bar Chart Placeholder]</span>
            </div>
        </div>
    </div>
</div>
@endsection
