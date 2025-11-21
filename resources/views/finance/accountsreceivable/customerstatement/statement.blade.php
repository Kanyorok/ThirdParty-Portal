@extends('layouts.app')
@section('title', 'Customer Statement')

@section('content')
<div class="container-fluid my-4">
    {{-- Header with Back Button --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('customerdata.index') }}" class="btn btn-sm shadow-sm mb-2" style="background: rgba(90, 116, 231, 0.1); color: #5a74e7; border: 1px solid rgba(90, 116, 231, 0.3);">
                <i class="fas fa-arrow-left me-1"></i>Back to Customers
            </a>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-file-invoice me-2" style="color: #5a74e7;"></i>Customer Statement
            </h4>
        </div>
        <div>
            <button class="btn shadow-sm" onclick="window.print()" style="background: linear-gradient(to right, #5a74e7, #6d3f9a); color: white; border: none;">
                <i class="fas fa-print me-2"></i>Print Statement
            </button>
        </div>
    </div>

    {{-- Customer Information Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body" style="background: linear-gradient(to right, rgba(90, 116, 231, 0.05), rgba(109, 63, 154, 0.05));">
            <div class="row">
                <div class="col-md-8">
                    <div class="d-flex align-items-start mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #5a74e7, #6d3f9a); color: white; font-size: 24px; font-weight: bold;">
                            {{ strtoupper(substr($customer['name'], 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">{{ $customer['name'] }}</h4>
                            <div class="d-flex gap-2 mb-2">
                                @foreach($customer['types'] as $type)
                                    @php
                                        $typeColors = [
                                            'Tenant' => ['bg' => 'rgba(90, 116, 231, 0.1)', 'text' => '#5a74e7'],
                                            'Supplier' => ['bg' => 'rgba(40, 167, 69, 0.1)', 'text' => '#28a745'],
                                            'Client' => ['bg' => 'rgba(109, 63, 154, 0.1)', 'text' => '#6d3f9a']
                                        ];
                                        $color = $typeColors[$type];
                                    @endphp
                                    <span class="badge" style="background-color: {{ $color['bg'] }}; color: {{ $color['text'] }};">
                                        {{ $type }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-id-card me-2" style="color: #5a74e7;"></i>
                                <div>
                                    <small class="text-muted d-block">ID Number</small>
                                    <strong>{{ $customer['id_number'] }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-envelope me-2" style="color: #6d3f9a;"></i>
                                <div>
                                    <small class="text-muted d-block">Email</small>
                                    <strong>{{ $customer['email'] }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone me-2" style="color: #28a745;"></i>
                                <div>
                                    <small class="text-muted d-block">Phone</small>
                                    <strong>{{ $customer['phone'] }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt me-2" style="color: #ffc107;"></i>
                                <div>
                                    <small class="text-muted d-block">Address</small>
                                    <strong>{{ $customer['address'] }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-end">
                        <small class="text-muted d-block mb-1">Statement Date</small>
                        <h5 class="mb-3" style="color: #5a74e7;">{{ now()->format('F d, Y') }}</h5>
                        <img src="{{ asset('assets/images/logo-dark.png') }}" alt="Company Logo" class="img-fluid" style="max-height: 60px;" onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tenant Statement (ERP) --}}
    @if(in_array('Tenant', $customer['types']) && count($tenantTransactions) > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background: linear-gradient(to right, rgba(90, 116, 231, 0.1), rgba(90, 116, 231, 0.05));">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-home me-2" style="color: #5a74e7;"></i>Tenant Account Statement
                        <span class="badge ms-2" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                            <i class="fas fa-desktop me-1"></i>ERP System
                        </span>
                    </h5>
                    @php
                        $tenantBalance = end($tenantTransactions)['balance'];
                    @endphp
                    <h5 class="mb-0" style="color: {{ $tenantBalance < 0 ? '#dc3545' : '#28a745' }};">
                        Balance: KES {{ number_format(abs($tenantBalance), 2) }}
                        <small class="text-muted">{{ $tenantBalance < 0 ? '(Dr)' : '(Cr)' }}</small>
                    </h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: rgba(90, 116, 231, 0.05);">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="py-3">Description</th>
                                <th class="py-3 text-end">Debit (KES)</th>
                                <th class="py-3 text-end">Credit (KES)</th>
                                <th class="py-3 text-end pe-4">Balance (KES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenantTransactions as $transaction)
                                <tr>
                                    <td class="px-4 text-muted">{{ $transaction['date']->format('M d, Y') }}</td>
                                    <td>{{ $transaction['description'] }}</td>
                                    <td class="text-end" style="color: #dc3545;">
                                        {{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '-' }}
                                    </td>
                                    <td class="text-end" style="color: #28a745;">
                                        {{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '-' }}
                                    </td>
                                    <td class="text-end pe-4 fw-semibold" style="color: {{ $transaction['balance'] < 0 ? '#dc3545' : '#28a745' }};">
                                        {{ number_format(abs($transaction['balance']), 2) }}
                                        <small>{{ $transaction['balance'] < 0 ? 'Dr' : 'Cr' }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Supplier Statement (ERP) --}}
    @if(in_array('Supplier', $customer['types']))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background: linear-gradient(to right, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05));">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-truck me-2" style="color: #28a745;"></i>Supplier Account Statement
                        <span class="badge ms-2" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                            <i class="fas fa-desktop me-1"></i>ERP System
                        </span>
                    </h5>
                    @if(count($supplierTransactions) > 0)
                        @php
                            $supplierBalance = end($supplierTransactions)['balance'];
                        @endphp
                        <h5 class="mb-0" style="color: {{ $supplierBalance < 0 ? '#dc3545' : '#28a745' }};">
                            Balance: KES {{ number_format(abs($supplierBalance), 2) }}
                            <small class="text-muted">{{ $supplierBalance < 0 ? '(Dr)' : '(Cr)' }}</small>
                        </h5>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                @if(count($supplierTransactions) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: rgba(40, 167, 69, 0.05);">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="py-3">Description</th>
                                    <th class="py-3 text-end">Debit (KES)</th>
                                    <th class="py-3 text-end">Credit (KES)</th>
                                    <th class="py-3 text-end pe-4">Balance (KES)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supplierTransactions as $transaction)
                                    <tr>
                                        <td class="px-4 text-muted">{{ $transaction['date']->format('M d, Y') }}</td>
                                        <td>{{ $transaction['description'] }}</td>
                                        <td class="text-end" style="color: #dc3545;">
                                            {{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '-' }}
                                        </td>
                                        <td class="text-end" style="color: #28a745;">
                                            {{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '-' }}
                                        </td>
                                        <td class="text-end pe-4 fw-semibold" style="color: {{ $transaction['balance'] < 0 ? '#dc3545' : '#28a745' }};">
                                            {{ number_format(abs($transaction['balance']), 2) }}
                                            <small>{{ $transaction['balance'] < 0 ? 'Dr' : 'Cr' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x mb-3 text-muted d-block"></i>
                        <p class="text-muted">No supplier transactions recorded</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Client Statement (CBS) --}}
    @if(in_array('Client', $customer['types']) && count($clientTransactions) > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background: linear-gradient(to right, rgba(109, 63, 154, 0.1), rgba(109, 63, 154, 0.05));">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-user-circle me-2" style="color: #6d3f9a;"></i>Client Account Statement
                        <span class="badge ms-2" style="background-color: rgba(109, 63, 154, 0.1); color: #6d3f9a;">
                            <i class="fas fa-server me-1"></i>CBS System
                        </span>
                    </h5>
                    @php
                        $clientBalance = end($clientTransactions)['balance'];
                    @endphp
                    <h5 class="mb-0" style="color: {{ $clientBalance < 0 ? '#dc3545' : '#28a745' }};">
                        Balance: KES {{ number_format(abs($clientBalance), 2) }}
                        <small class="text-muted">{{ $clientBalance < 0 ? '(Dr)' : '(Cr)' }}</small>
                    </h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: rgba(109, 63, 154, 0.05);">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="py-3">Description</th>
                                <th class="py-3 text-end">Debit (KES)</th>
                                <th class="py-3 text-end">Credit (KES)</th>
                                <th class="py-3 text-end pe-4">Balance (KES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientTransactions as $transaction)
                                <tr>
                                    <td class="px-4 text-muted">{{ $transaction['date']->format('M d, Y') }}</td>
                                    <td>{{ $transaction['description'] }}</td>
                                    <td class="text-end" style="color: #dc3545;">
                                        {{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '-' }}
                                    </td>
                                    <td class="text-end" style="color: #28a745;">
                                        {{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '-' }}
                                    </td>
                                    <td class="text-end pe-4 fw-semibold" style="color: {{ $transaction['balance'] < 0 ? '#dc3545' : '#28a745' }};">
                                        {{ number_format(abs($transaction['balance']), 2) }}
                                        <small>{{ $transaction['balance'] < 0 ? 'Dr' : 'Cr' }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Summary Card --}}
    <div class="card border-0 shadow-sm" style="background: linear-gradient(to right, rgba(90, 116, 231, 0.05), rgba(109, 63, 154, 0.05));">
        <div class="card-body">
            <div class="row g-4">
                @php
                    $tenantBal = count($tenantTransactions) > 0 ? end($tenantTransactions)['balance'] : 0;
                    $supplierBal = count($supplierTransactions) > 0 ? end($supplierTransactions)['balance'] : 0;
                    $clientBal = count($clientTransactions) > 0 ? end($clientTransactions)['balance'] : 0;
                    $totalBalance = $tenantBal + $supplierBal + $clientBal;
                @endphp
                
                @if(in_array('Tenant', $customer['types']))
                    <div class="col-md-3">
                        <div class="text-center p-3 rounded" style="background: white;">
                            <small class="text-muted d-block mb-2">Tenant Balance</small>
                            <h4 class="mb-0 fw-bold" style="color: {{ $tenantBal < 0 ? '#dc3545' : '#28a745' }};">
                                KES {{ number_format(abs($tenantBal), 2) }}
                            </h4>
                        </div>
                    </div>
                @endif
                
                @if(in_array('Supplier', $customer['types']))
                    <div class="col-md-3">
                        <div class="text-center p-3 rounded" style="background: white;">
                            <small class="text-muted d-block mb-2">Supplier Balance</small>
                            <h4 class="mb-0 fw-bold" style="color: {{ $supplierBal < 0 ? '#dc3545' : '#28a745' }};">
                                KES {{ number_format(abs($supplierBal), 2) }}
                            </h4>
                        </div>
                    </div>
                @endif
                
                @if(in_array('Client', $customer['types']))
                    <div class="col-md-3">
                        <div class="text-center p-3 rounded" style="background: white;">
                            <small class="text-muted d-block mb-2">Client Balance</small>
                            <h4 class="mb-0 fw-bold" style="color: {{ $clientBal < 0 ? '#dc3545' : '#28a745' }};">
                                KES {{ number_format(abs($clientBal), 2) }}
                            </h4>
                        </div>
                    </div>
                @endif
                
                <div class="col-md-3">
                    <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, #5a74e7, #6d3f9a); color: white;">
                        <small class="d-block mb-2" style="opacity: 0.9;">Total Combined Balance</small>
                        <h4 class="mb-0 fw-bold">
                            KES {{ number_format(abs($totalBalance), 2) }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center mt-4 text-muted">
        <small>
            <i class="fas fa-info-circle me-1"></i>
            This statement was generated on {{ now()->format('F d, Y \a\t h:i A') }}
        </small>
    </div>
</div>
@endsection

@section('styles')
<style>
    :root {
        --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
    }

    body, .card, .table {
        font-family: var(--font-sans);
    }

    .card {
        transition: transform 0.2s ease-in-out;
    }

    .table-hover tbody tr {
        transition: background-color 0.2s ease-in-out;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-weight: 500;
        padding: 0.35rem 0.65rem;
    }

    /* Print Styles */
    @media print {
        .btn, nav, header, footer {
            display: none !important;
        }
        
        .card {
            box-shadow: none !important;
            page-break-inside: avoid;
        }
        
        body {
            background: white !important;
        }
    }
</style>
@endsection

