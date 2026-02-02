@extends('layouts.app')
@section('title', 'Rent Collection Dashboard')

@section('content')
<div class="container mt-4">

<p class="text-muted">
    <small>Overview of lease invoices and collections</small>
</p>

{{-- Summary Cards --}}
<div class="row mb-4">
    @php
        $cards = [
            ['Collected', $collected, 'success'],
            ['Due Soon', $dueSoon, 'warning'],
            ['Overdue', $overdue, 'danger'],
            ['Partial Payments', $partial, 'secondary']
        ];
    @endphp

    @foreach($cards as [$title, $amount, $color])
        <div class="col-md-3 mb-2">
            <div class="card text-white bg-{{ $color }}">
                <div class="card-body">
                    <h6>{{ $title }}</h6>
                    <h4>KES {{ number_format($amount) }}</h4>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Filters --}}
@php
    $billingMonth = request('billing_month') 
        ?? \Carbon\Carbon::now()->format('Y-m');
@endphp

<form class="row g-2 mb-3" method="GET" action="{{ route('rentdashboard.index') }}">

    <div class="col-md-3">
        <select name="property_id" class="form-select">
            <option value="">All Properties</option>
            @foreach($properties as $property)
                <option value="{{ $property->Id }}"
                    {{ request('property_id') == $property->Id ? 'selected' : '' }}>
                    {{ $property->PropertyName }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <select name="tenant_id" class="form-select">
            <option value="">All Tenants</option>
            @foreach($tenants as $tenant)
                <option value="{{ $tenant->Id }}"
                    {{ request('tenant_id') == $tenant->Id ? 'selected' : '' }}>
                    {{ $tenant->thirdParty->ThirdPartyName }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <input type="month"
               name="billing_month"
               class="form-control"
               value="{{ $billingMonth }}">
    </div>

    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary w-100">Filter</button>
        <a href="{{ route('rentdashboard.index') }}"
           class="btn btn-outline-secondary w-100">
            Clear
        </a>
    </div>
</form>

{{-- Table --}}
<div class="table-responsive mb-3">
<table class="table table-bordered table-striped align-middle">
<thead class="table-light">
<tr>
    <th>#</th>
    <th>Tenant</th>
    <th>Property</th>
    <th>Unit</th>
    <th>Invoice Date</th>
    <th>Amount Due</th>
    <th>Amount Paid</th>
    <th>Balance</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
@forelse($invoices as $invoice)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $invoice->lease->tenant->thirdParty->ThirdPartyName ?? '-' }}</td>
    <td>{{ $invoice->lease->property->PropertyName ?? '-' }}</td>
    <td>{{ $invoice->lease->unit->UnitCode ?? '-' }}</td>
    <td>{{ \Carbon\Carbon::parse($invoice->InvoiceDate)->format('d M Y') }}</td>
    <td>{{ $invoice->currency->SymbolNative ?? '-' }} {{ number_format($invoice->DerivedDue) }}</td>
    <td>{{ $invoice->currency->SymbolNative ?? '-' }} {{ number_format($invoice->DerivedPaid) }}</td>
    <td>{{ $invoice->currency->SymbolNative ?? '-' }} {{ number_format($invoice->DerivedDue - $invoice->DerivedPaid) }}</td>
    <td>
        <span class="badge bg-{{ 
            $invoice->DerivedStatus === 'Fully Paid' ? 'success' :
            ($invoice->DerivedStatus === 'Partial Paid' ? 'warning' : 'danger')
        }}">
            {{ $invoice->DerivedStatus }}
        </span>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center text-muted">
        No records found
    </td>
</tr>
@endforelse
</tbody>
</table>
</div>

{{-- Pagination --}}
<div class="d-flex justify-content-end mb-4">
    {{ $invoices->links() }}
</div>

{{-- Chart --}}
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
const labels = {!! json_encode($chartData->keys()) !!};
const invoiced = {!! json_encode($chartData->pluck('invoiced')->values()) !!};
const collected = {!! json_encode($chartData->pluck('collected')->values()) !!};

new Chart(document.getElementById('collectionChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'Invoiced', data: invoiced, backgroundColor: '#dc3545' },
            { label: 'Collected', data: collected, backgroundColor: '#198754' }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
@endpush
@endsection
