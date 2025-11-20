@extends('layouts.app')
@section('title', 'Customer Data - CRU Dashboard')

@section('content')
<div class="container-fluid my-4">
    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-users me-2" style="color: #5a74e7;"></i>Customer Data Synchronization
            </h4>
            <p class="text-muted mb-0">Central Report Unit - Syncing customer data between ERP and CBS</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn shadow-sm" data-bs-toggle="modal" data-bs-target="#syncModal" style="background: linear-gradient(to right, #5a74e7, #6d3f9a); color: white; border: none;">
                <i class="fas fa-sync-alt me-2"></i>Sync Customer Data
            </button>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        {{-- Total Customers Card --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="text-muted small">Total Customers</div>
                        <div class="p-2 rounded" style="background: rgba(90, 116, 231, 0.1);">
                            <i class="fas fa-user-friends" style="color: #5a74e7;"></i>
                        </div>
                    </div>
                    <h2 class="mb-2 fw-bold" style="color: #5a74e7;">{{ number_format($stats['total_customers']) }}</h2>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                            <i class="fas fa-desktop me-1"></i>ERP: {{ $stats['erp_customers'] }}
                        </span>
                        <span class="badge" style="background-color: rgba(109, 63, 154, 0.1); color: #6d3f9a;">
                            <i class="fas fa-server me-1"></i>CBS: {{ $stats['cbs_customers'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Both Systems Card --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="text-muted small">In Both Systems</div>
                        <div class="p-2 rounded" style="background: rgba(109, 63, 154, 0.1);">
                            <i class="fas fa-link" style="color: #6d3f9a;"></i>
                        </div>
                    </div>
                    <h2 class="mb-2 fw-bold" style="color: #6d3f9a;">{{ number_format($stats['both_systems']) }}</h2>
                    <div class="small text-muted">
                        <i class="fas fa-info-circle me-1"></i>Customers synced across systems
                    </div>
                </div>
            </div>
        </div>

        {{-- Last Sync Date Card --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="text-muted small">Last Sync Date</div>
                        <div class="p-2 rounded" style="background: rgba(90, 116, 231, 0.15);">
                            <i class="fas fa-calendar-alt" style="color: #5a74e7;"></i>
                        </div>
                    </div>
                    <h5 class="mb-2 fw-bold" style="color: #5a74e7;">{{ $stats['last_sync_date']->format('M d, Y') }}</h5>
                    <div class="small text-muted">
                        <i class="far fa-clock me-1"></i>{{ $stats['last_sync_date']->format('h:i A') }}
                        <span class="ms-2" style="color: #28a745;">
                            ({{ $stats['last_sync_date']->diffForHumans() }})
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sync Operations Card --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="text-muted small">Sync Operations</div>
                        <div class="p-2 rounded" style="background: rgba(109, 63, 154, 0.1);">
                            <i class="fas fa-exchange-alt" style="color: #6d3f9a;"></i>
                        </div>
                    </div>
                    <h2 class="mb-2 fw-bold" style="color: #6d3f9a;">{{ number_format($stats['sync_operations']) }}</h2>
                    <div class="small">
                        @if($stats['sync_status'] === 'success')
                            <span class="badge" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                                <i class="fas fa-check-circle me-1"></i>System Healthy
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Customers Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-list me-2" style="color: #5a74e7;"></i>Synced Customers
                </h6>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="searchTable" placeholder="Search..." style="width: 200px; border-color: #5a74e7;">
                    <button class="btn btn-sm" onclick="exportTable()" style="background: rgba(90, 116, 231, 0.1); color: #5a74e7; border: 1px solid rgba(90, 116, 231, 0.3);">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="customersTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3" style="width: 60px;">#</th>
                            <th class="py-3">Name</th>
                            <th class="py-3">ID Number</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Types</th>
                            <th class="py-3">Sources</th>
                            <th class="py-3 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $index => $customer)
                            <tr>
                                <td class="px-4 text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; background: linear-gradient(135deg, #5a74e7, #6d3f9a); color: white; font-weight: bold;">
                                            {{ strtoupper(substr($customer['name'], 0, 1)) }}
                                        </div>
                                        <span class="fw-semibold">{{ $customer['name'] }}</span>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $customer['id_number'] }}</td>
                                <td class="text-muted">{{ $customer['email'] }}</td>
                                <td>
                                    @foreach($customer['types'] as $type)
                                        @php
                                            $typeColors = [
                                                'Tenant' => ['bg' => 'rgba(90, 116, 231, 0.1)', 'text' => '#5a74e7'],
                                                'Supplier' => ['bg' => 'rgba(40, 167, 69, 0.1)', 'text' => '#28a745'],
                                                'Client' => ['bg' => 'rgba(109, 63, 154, 0.1)', 'text' => '#6d3f9a']
                                            ];
                                            $color = $typeColors[$type] ?? ['bg' => 'rgba(108, 117, 125, 0.1)', 'text' => '#6c757d'];
                                        @endphp
                                        <span class="badge me-1" style="background-color: {{ $color['bg'] }}; color: {{ $color['text'] }};">
                                            {{ $type }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($customer['sources'] === 'Both')
                                        <span class="badge" style="background-color: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                            <i class="fas fa-sync me-1"></i>Both
                                        </span>
                                    @elseif($customer['sources'] === 'ERP')
                                        <span class="badge" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                                            <i class="fas fa-desktop me-1"></i>ERP
                                        </span>
                                    @else
                                        <span class="badge" style="background-color: rgba(109, 63, 154, 0.1); color: #6d3f9a;">
                                            <i class="fas fa-server me-1"></i>CBS
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm" title="View Balances" onclick="viewBalances({{ json_encode($customer) }})" style="background: rgba(90, 116, 231, 0.1); color: #5a74e7; border: 1px solid rgba(90, 116, 231, 0.3);">
                                        <i class="fas fa-wallet"></i>
                                    </button>
                                    <a href="#" class="btn btn-sm ms-1" title="Generate Statement" style="background: rgba(109, 63, 154, 0.1); color: #6d3f9a; border: 1px solid rgba(109, 63, 154, 0.3);">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    <p>No customers synced yet.</p>
                                    <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#syncModal" style="background: linear-gradient(to right, #5a74e7, #6d3f9a); color: white; border: none;">
                                        <i class="fas fa-sync-alt me-1"></i>Start Sync
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Sync Modal --}}
<div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="syncModalLabel">
                    <i class="fas fa-sync-alt me-2" style="color: #5a74e7;"></i>Sync Customer Data
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert d-flex align-items-start" role="alert" style="background-color: rgba(90, 116, 231, 0.1); border-color: rgba(90, 116, 231, 0.3); color: #5a74e7;">
                        <i class="fas fa-info-circle mt-1 me-2"></i>
                        <div>
                            <strong>Sync Information</strong>
                            <p class="mb-0 small">This will synchronize customer data from both ERP and CBS systems including Tenants, Suppliers, and Clients.</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Source Systems</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sync_erp" id="syncERP" checked style="border-color: #28a745;">
                            <label class="form-check-label" for="syncERP">
                                <i class="fas fa-desktop me-1" style="color: #28a745;"></i>Sync from ERP (Tenants & Suppliers)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sync_cbs" id="syncCBS" checked style="border-color: #6d3f9a;">
                            <label class="form-check-label" for="syncCBS">
                                <i class="fas fa-server me-1" style="color: #6d3f9a;"></i>Sync from CBS (Clients)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="syncType" class="form-label fw-semibold">Sync Type</label>
                        <select class="form-select" id="syncType" name="sync_type" style="border-color: #5a74e7;">
                            <option value="incremental" selected>Incremental Sync (New & Updated only)</option>
                            <option value="full">Full Sync (All records)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #6c757d; color: white; border: none;">Cancel</button>
                    <button type="submit" class="btn" style="background: linear-gradient(to right, #5a74e7, #6d3f9a); color: white; border: none;">
                        <i class="fas fa-sync-alt me-2"></i>Start Sync
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Balance View Modal --}}
<div class="modal fade" id="balanceModal" tabindex="-1" aria-labelledby="balanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0" style="background: linear-gradient(to right, rgba(90, 116, 231, 0.05), rgba(109, 63, 154, 0.05));">
                <div>
                    <h5 class="modal-title fw-bold" id="balanceModalLabel">
                        <i class="fas fa-wallet me-2" style="color: #5a74e7;"></i><span id="customerNameModal"></span>
                    </h5>
                    <p class="mb-0 small text-muted">Customer Balance Overview</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="balanceContent">
                    <!-- Will be populated dynamically -->
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #6c757d; color: white; border: none;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Sync History Modal --}}
<div class="modal fade" id="syncHistoryModal" tabindex="-1" aria-labelledby="syncHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0" style="background: linear-gradient(to right, rgba(90, 116, 231, 0.05), rgba(109, 63, 154, 0.05));">
                <h5 class="modal-title fw-bold" id="syncHistoryModalLabel">
                    <i class="fas fa-history me-2" style="color: #6d3f9a;"></i>Synchronization History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: rgba(90, 116, 231, 0.05);">
                            <tr>
                                <th class="px-4 py-3">Sync ID</th>
                                <th class="py-3">Date & Time</th>
                                <th class="py-3">Type</th>
                                <th class="py-3">Source</th>
                                <th class="py-3">Records Synced</th>
                                <th class="py-3">Duration</th>
                                <th class="py-3">Initiated By</th>
                                <th class="py-3">Status</th>
                                <th class="py-3 text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($syncHistory as $history)
                                <tr>
                                    <td class="px-4">
                                        <span class="fw-semibold" style="color: #5a74e7;">#{{ str_pad($history['id'], 4, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $history['date']->format('M d, Y') }}</div>
                                        <div class="small text-muted">{{ $history['date']->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $history['type'] === 'Full' ? 'rgba(90, 116, 231, 0.1)' : 'rgba(109, 63, 154, 0.1)' }}; color: {{ $history['type'] === 'Full' ? '#5a74e7' : '#6d3f9a' }};">
                                            {{ $history['type'] }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ $history['source'] }}</td>
                                    <td>
                                        <span class="fw-semibold" style="color: #5a74e7;">{{ number_format($history['records_synced']) }}</span>
                                    </td>
                                    <td class="text-muted">{{ $history['duration'] }}</td>
                                    <td class="text-muted">{{ $history['initiated_by'] }}</td>
                                    <td>
                                        @if($history['status'] === 'Success')
                                            <span class="badge" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                                                <i class="fas fa-check-circle me-1"></i>{{ $history['status'] }}
                                            </span>
                                        @elseif($history['status'] === 'Failed')
                                            <span class="badge" style="background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                                <i class="fas fa-times-circle me-1"></i>{{ $history['status'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm" title="View Details" onclick="viewSyncDetails({{ $history['id'] }})" style="background: rgba(90, 116, 231, 0.1); color: #5a74e7; border: 1px solid rgba(90, 116, 231, 0.3);">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($history['status'] === 'Failed')
                                            <button class="btn btn-sm ms-1" title="Retry Sync" style="background: rgba(40, 167, 69, 0.1); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.3);">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        <p>No synchronization history available.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing {{ count($syncHistory) }} sync operations
                    </div>
                    <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #6c757d; color: white; border: none;">
                        Close
                    </button>
                </div>
            </div>
        </div>
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

    .card:hover {
        transform: translateY(-2px);
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

    .form-control:focus,
    .form-select:focus {
        border-color: #5a74e7;
        box-shadow: 0 0 0 0.25rem rgba(90, 116, 231, 0.25);
    }

    .form-check-input:checked {
        background-color: #5a74e7;
        border-color: #5a74e7;
    }

    .form-check-input:focus {
        border-color: #5a74e7;
        box-shadow: 0 0 0 0.25rem rgba(90, 116, 231, 0.25);
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(90, 116, 231, 0.3);
        transition: all 0.3s ease;
    }
</style>
@endsection

@section('scripts')
<script>
    // Simple table search functionality
    document.getElementById('searchTable')?.addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#customersTable tbody tr');
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchValue) ? '' : 'none';
        });
    });

    // Export table functionality
    function exportTable() {
        alert('Export functionality will be implemented in the next phase.');
    }

    // View customer balances
    function viewBalances(customer) {
        // Update modal title
        document.getElementById('customerNameModal').textContent = customer.name;
        
        // Build balance content
        let content = '';
        
        // Check which types the customer has
        const hasTypes = customer.types;
        
        // ERP Balance Card (Tenant/Supplier)
        if (customer.sources === 'ERP' || customer.sources === 'Both') {
            const erpTypes = hasTypes.filter(t => t === 'Tenant' || t === 'Supplier');
            if (erpTypes.length > 0) {
                content += `
                    <div class="col-md-6">
                        <div class="card border-0" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.1));">
                            <div class="card-body">
                                <h6 class="text-muted mb-3">
                                    <i class="fas fa-desktop me-2"></i>ERP System Balance
                                </h6>
                                ${erpTypes.map(type => `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted">${type}</span>
                                            <span class="badge" style="background-color: rgba(90, 116, 231, 0.1); color: #5a74e7;">${type}</span>
                                        </div>
                                        <h4 class="mb-0" style="color: ${customer.erp_balance < 0 ? '#dc3545' : '#28a745'};">
                                            KES ${Math.abs(customer.erp_balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                            <small class="text-muted">${customer.erp_balance < 0 ? '(Dr)' : '(Cr)'}</small>
                                        </h4>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `;
            }
        }
        
        // CBS Balance Card (Client)
        if (customer.sources === 'CBS' || customer.sources === 'Both') {
            const cbsTypes = hasTypes.filter(t => t === 'Client');
            if (cbsTypes.length > 0) {
                content += `
                    <div class="col-md-6">
                        <div class="card border-0" style="background: linear-gradient(135deg, rgba(109, 63, 154, 0.05), rgba(109, 63, 154, 0.1));">
                            <div class="card-body">
                                <h6 class="text-muted mb-3">
                                    <i class="fas fa-server me-2"></i>CBS System Balance
                                </h6>
                                ${cbsTypes.map(type => `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted">${type}</span>
                                            <span class="badge" style="background-color: rgba(109, 63, 154, 0.1); color: #6d3f9a;">${type}</span>
                                        </div>
                                        <h4 class="mb-0" style="color: ${customer.cbs_balance < 0 ? '#dc3545' : '#28a745'};">
                                            KES ${Math.abs(customer.cbs_balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                            <small class="text-muted">${customer.cbs_balance < 0 ? '(Dr)' : '(Cr)'}</small>
                                        </h4>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `;
            }
        }
        
        // Total Balance Card
        const totalBalance = customer.erp_balance + customer.cbs_balance;
        content += `
            <div class="col-12">
                <div class="card border-0" style="background: linear-gradient(to right, rgba(90, 116, 231, 0.1), rgba(109, 63, 154, 0.1));">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Total Combined Balance</h6>
                        <h3 class="mb-0 fw-bold" style="color: ${totalBalance < 0 ? '#dc3545' : '#28a745'};">
                            KES ${Math.abs(totalBalance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            <small class="text-muted">${totalBalance < 0 ? '(Debit)' : '(Credit)'}</small>
                        </h3>
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('balanceContent').innerHTML = content;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('balanceModal'));
        modal.show();
    }

    // View sync details
    function viewSyncDetails(syncId) {
        alert(`Viewing details for Sync ID: ${syncId}\n\nThis will show:\n- Customer records synced\n- Type breakdown (Tenants, Suppliers, Clients)\n- Error logs (if any)\n- Detailed timing information\n\nTo be implemented in next phase.`);
    }
</script>
@endsection

