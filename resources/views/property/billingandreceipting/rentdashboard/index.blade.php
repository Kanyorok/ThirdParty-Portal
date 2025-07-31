@extends('layouts.app')
@section('title', 'Rent Collection Dashboard')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Rent Collection Dashboard</h4>

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

    <!-- Filters -->
    <form class="row g-2 mb-3" method="GET" action="{{ route('rentdashboard.index') }}">
        <div class="col-md-3">
            <select name="property_id" class="form-select">
                <option value="">All Properties</option>
                @foreach($properties as $property)
                    <option value="{{ $property->Id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                        {{ $property->PropertyName }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <select name="tenant_id" class="form-select">
                <option value="">All Tenants</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->Id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                        {{ $tenant->TenantName }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <input type="month" name="billing_month" class="form-control" value="{{ request('billing_month') }}">
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>


    <!-- Data Grid -->
    <div class="table-responsive mb-4">
        <table class="table table-striped table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tenant</th>
                    <th>Property</th>
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
                    $property = $invoice->lease->property->PropertyName ?? 'N/A';
                    $unit = $invoice->lease->unit->UnitCode ?? 'N/A';
                    $due = $invoice->RentAmount + $invoice->ServicesCharge + $invoice->ParkingFee + $invoice->OtherCharges;
                    $paid = $invoice->receipts->sum('AmountPaidNow');
                    $balance = $due - $paid;
                    $status = $paid == 0 ? 'Unpaid' : ($paid < $due ? 'Partial' : 'Paid');
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $tenant }}</td>
                    <td>{{ $property }}</td>
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
    <!-- Chart Container -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Invoices vs Collections by Month</h6>
        </div>
        <div class="card-body">
            <canvas id="collectionChart" height="100"></canvas>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('collectionChart').getContext('2d');

    const labels = {!! json_encode($chartData->keys()) !!};
    const invoicedData = {!! json_encode($chartData->pluck('invoiced')->values()) !!};
    const collectedData = {!! json_encode($chartData->pluck('collected')->values()) !!};

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Invoiced',
                    data: invoicedData,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Collected',
                    data: collectedData,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount (KES)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            },
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    });
</script>
@endpush

@endsection
