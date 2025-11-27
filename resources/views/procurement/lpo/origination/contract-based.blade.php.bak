@extends('layouts.app')
@section('title', 'Contract-Based LPO Creation')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">📄 Contract-Based LPO Creation</h4>
                        <p class="text-muted mb-0">Create Local Purchase Orders from active/executed contracts</p>
                    </div>
                    <div>
                        <a href="{{ route('lpo.origination.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <a href="{{ route('contracts.lifecycle.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-contract"></i> Manage Contracts
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Active Contracts Available for LPO -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-handshake"></i> Active Contracts Available for LPO Creation
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Contract Details</th>
                                    <th>Tender Information</th>
                                    <th>Supplier</th>
                                    <th>Contract Value</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($activeContracts as $contract)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong class="text-success">{{ $contract->ContractRef }}</strong>
                                                @if($contract->ContractApprovedOn)
                                                    <div class="text-muted small">
                                                        Approved: {{ $contract->ContractApprovedOn->format('d/m/Y') }}
                                                    </div>
                                                @endif
                                                @if($contract->PaymentTerms)
                                                    <div class="text-muted small">
                                                        <i class="fas fa-credit-card me-1"></i>
                                                        {{ Str::limit($contract->PaymentTerms, 30) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($contract->tender)
                                                <div>
                                                    <strong>{{ $contract->tender->TenderNo }}</strong>
                                                    <div class="text-muted small">
                                                        {{ Str::limit($contract->tender->Title, 50) }}
                                                    </div>
                                                    <div class="text-muted small">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ $contract->tender->OpeningDate?->format('d/m/Y') }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($contract->winningSupplier)
                                                <div>
                                                    <strong>{{ $contract->winningSupplier->SupplierName }}</strong>
                                                    @if($contract->winningSupplier->ContactPerson)
                                                        <div class="text-muted small">
                                                            <i class="fas fa-user me-1"></i>
                                                            {{ $contract->winningSupplier->ContactPerson }}
                                                        </div>
                                                    @endif
                                                    @if($contract->winningSupplier->PhoneNumber)
                                                        <div class="text-muted small">
                                                            <i class="fas fa-phone me-1"></i>
                                                            {{ $contract->winningSupplier->PhoneNumber }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($contract->ContractValue)
                                                <strong class="text-success">
                                                    {{ number_format($contract->ContractValue, 2) }}
                                                </strong>
                                                <div class="text-muted small">
                                                    {{ is_object($contract->tender->Currency) ? $contract->tender->Currency->Code : ($contract->tender->Currency ?? 'KES') }}
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($contract->ContractStartDate && $contract->ContractEndDate)
                                                <div class="small">
                                                    <div class="text-success">
                                                        <i class="fas fa-play me-1"></i>
                                                        {{ $contract->ContractStartDate->format('d/m/Y') }}
                                                    </div>
                                                    <div class="text-danger">
                                                        <i class="fas fa-stop me-1"></i>
                                                        {{ $contract->ContractEndDate->format('d/m/Y') }}
                                                    </div>
                                                    @php
                                                        $now = now();
                                                        $daysRemaining = $now->diffInDays($contract->ContractEndDate, false);
                                                    @endphp
                                                    @if($daysRemaining > 0)
                                                        <div class="text-info">
                                                            {{ $daysRemaining }} days remaining
                                                        </div>
                                                    @elseif($daysRemaining < 0)
                                                        <div class="text-warning">
                                                            Expired {{ abs($daysRemaining) }} days ago
                                                        </div>
                                                    @else
                                                        <div class="text-warning">Expires today</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">Duration not set</span>
                                            @endif
                                        </td>
                                        <td>
                                                <span class="badge {{ $contract->contract_status_badge['class'] }}">
                                                    {{ $contract->contract_status_badge['text'] }}
                                                </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('lpo.create.contract', $contract->Id) }}"
                                                   class="btn btn-sm btn-success" title="Create LPO from Contract">
                                                    <i class="fas fa-plus-circle"></i> Create LPO
                                                </a>

                                                <button type="button"
                                                        class="btn btn-sm btn-outline-info dropdown-toggle dropdown-toggle-split"
                                                        data-bs-toggle="dropdown" title="More Actions">
                                                    <span class="visually-hidden">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('contracts.lifecycle.view', $contract->Id) }}">
                                                            <i class="fas fa-eye me-2"></i>View Contract
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item"
                                                                onclick="showContractSummary({{ $contract->Id }})">
                                                            <i class="fas fa-info-circle me-2"></i>Contract Summary
                                                        </button>
                                                    </li>
                                                    @if($contract->DeliveryTerms)
                                                        <li>
                                                            <button type="button" class="dropdown-item"
                                                                    onclick="showDeliveryTerms('{{ addslashes($contract->DeliveryTerms) }}')">
                                                                <i class="fas fa-truck me-2"></i>Delivery Terms
                                                            </button>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No Active Contracts Available</h6>
                                            <p class="text-muted">No executed contracts available for LPO creation at
                                                this time.</p>
                                            <div class="mt-3">
                                                <a href="{{ route('contracts.lifecycle.index') }}"
                                                   class="btn btn-primary me-2">
                                                    <i class="fas fa-plus"></i> Manage Contracts
                                                </a>
                                                <a href="{{ route('lpo.origination.award-based') }}"
                                                   class="btn btn-outline-warning">
                                                    <i class="fas fa-trophy"></i> Try Award-Based LPO
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($activeContracts->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $activeContracts->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Information Panel -->
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle"></i> Contract-Based LPO Guidelines
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-info">✅ Benefits:</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                Pre-negotiated terms and pricing
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                Established supplier relationship
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                Faster procurement process
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                Reduced compliance overhead
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-info">📋 Requirements:</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                Contract must be in 'Executed' status
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                Contract should not be expired
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                Items must be within contract scope
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                LPO value should not exceed contract balance
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-pie"></i> Contract Summary
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-success mb-0">{{ $activeContracts->total() }}</h4>
                                        <small class="text-muted">Total Contracts</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-info mb-0">
                                            {{ $activeContracts->where('ContractStatus', 'Executed')->count() }}
                                        </h4>
                                        <small class="text-muted">Executed</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="small text-muted">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    <strong>Tip:</strong> Use contract-based LPOs for recurring purchases covered under
                                    framework agreements.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contract Summary Modal -->
    <div class="modal fade" id="contractSummaryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Contract Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contractSummaryContent">
                    <!-- Contract details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Terms Modal -->
    <div class="modal fade" id="deliveryTermsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delivery Terms</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="deliveryTermsContent">
                    <!-- Delivery terms will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showContractSummary(contractId) {
            // You can implement AJAX loading or use the data from the page
            const modal = new bootstrap.Modal(document.getElementById('contractSummaryModal'));
            document.getElementById('contractSummaryContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            modal.show();

            // TODO: Implement AJAX call to fetch contract details
            setTimeout(() => {
                document.getElementById('contractSummaryContent').innerHTML = `
                    <p><strong>Contract ID:</strong> ${contractId}</p>
                    <p>Additional contract details can be loaded via AJAX here.</p>
                `;
            }, 1000);
        }

        function showDeliveryTerms(terms) {
            const modal = new bootstrap.Modal(document.getElementById('deliveryTermsModal'));
            document.getElementById('deliveryTermsContent').innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-truck me-2"></i>
                    ${terms}
                </div>
            `;
            modal.show();
        }
    </script>
@endsection
