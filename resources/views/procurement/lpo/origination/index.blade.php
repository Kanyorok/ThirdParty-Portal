@extends('layouts.app')
@section('title', 'LPO Origination Dashboard')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">🛒 Local Purchase Order (LPO) Origination</h4>
                        <p class="text-muted mb-0">Create LPOs from contracts, awards, or direct procurement plans</p>
                    </div>
                    <div>
                        <a href="{{ route('purchaseOrder.index') }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-list"></i> All LPOs
                        </a>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-plus"></i> Quick Create
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('lpo.origination.contract-based') }}">
                                    <i class="fas fa-file-contract text-success"></i> From Contract</a></li>
                                <li><a class="dropdown-item" href="{{ route('lpo.origination.award-based') }}">
                                    <i class="fas fa-trophy text-warning"></i> From Award</a></li>
                                <li><a class="dropdown-item" href="{{ route('lpo.origination.direct-procurement') }}">
                                    <i class="fas fa-shipping-fast text-info"></i> Direct Procurement</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('purchaseOrder.create') }}">
                                    <i class="fas fa-file-alt text-primary"></i> Traditional RFQ-Based</a></li>
                            </ul>
                        </div>
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

                <!-- LPO Origination Methods Overview -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-file-contract fa-3x text-success"></i>
                                </div>
                                <h5 class="card-title text-success">Contract-Based LPOs</h5>
                                <p class="card-text text-muted small">
                                    Create LPOs linked to active/executed contracts with pre-defined terms and suppliers.
                                </p>
                                <div class="row text-center">
                                    <div class="col">
                                        <h3 class="text-success mb-0">{{ $contractBasedCount }}</h3>
                                        <small class="text-muted">Active Contracts</small>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('lpo.origination.contract-based') }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus"></i> Create from Contract
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-trophy fa-3x text-warning"></i>
                                </div>
                                <h5 class="card-title text-warning">Award-Based LPOs</h5>
                                <p class="card-text text-muted small">
                                    Create LPOs from approved tender/RFQ awards that don't require formal contracts.
                                </p>
                                <div class="row text-center">
                                    <div class="col">
                                        <h3 class="text-warning mb-0">{{ $awardBasedCount }}</h3>
                                        <small class="text-muted">Available Awards</small>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('lpo.origination.award-based') }}" class="btn btn-warning btn-sm text-dark">
                                        <i class="fas fa-plus"></i> Create from Award
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-shipping-fast fa-3x text-info"></i>
                                </div>
                                <h5 class="card-title text-info">Direct Procurement</h5>
                                <p class="card-text text-muted small">
                                    Create LPOs directly from approved procurement plan items designated for direct procurement.
                                </p>
                                <div class="row text-center">
                                    <div class="col">
                                        <h3 class="text-info mb-0">{{ $directProcurementCount }}</h3>
                                        <small class="text-muted">Plan Items Ready</small>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('lpo.origination.direct-procurement') }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-plus"></i> Create Direct LPO
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-file-alt fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title text-primary">RFQ-Based (Traditional)</h5>
                                <p class="card-text text-muted small">
                                    Traditional LPO creation from RFQ responses - the existing workflow.
                                </p>
                                <div class="row text-center">
                                    <div class="col">
                                        <h3 class="text-primary mb-0">
                                            <i class="fas fa-check-circle"></i>
                                        </h3>
                                        <small class="text-muted">Available</small>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('purchaseOrder.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Create from RFQ
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent LPOs by Type -->
                <div class="row">
                    <!-- Contract-Based Recent LPOs -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-file-contract"></i> Recent Contract-Based LPOs
                                </h6>
                            </div>
                            <div class="card-body">
                                @forelse($recentContractLPOs as $lpo)
                                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                                        <div class="flex-grow-1">
                                            <strong class="text-success">{{ $lpo->OrderNo }}</strong>
                                            <div class="small text-muted">
                                                Contract: {{ $lpo->getOriginationReferenceDisplay() }}
                                            </div>
                                            <div class="small text-muted">
                                                Supplier: {{ $lpo->supplier?->SupplierName ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">
                                                {{ $lpo->OrderDate?->format('M d') }}
                                            </div>
                                            @if($lpo->TotalAmount)
                                                <div class="small text-success">
                                                    {{ number_format($lpo->TotalAmount, 2) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <div>No contract-based LPOs yet</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Award-Based Recent LPOs -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-trophy"></i> Recent Award-Based LPOs
                                </h6>
                            </div>
                            <div class="card-body">
                                @forelse($recentAwardLPOs as $lpo)
                                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                                        <div class="flex-grow-1">
                                            <strong class="text-warning">{{ $lpo->OrderNo }}</strong>
                                            <div class="small text-muted">
                                                Tender: {{ $lpo->getOriginationReferenceDisplay() }}
                                            </div>
                                            <div class="small text-muted">
                                                Supplier: {{ $lpo->supplier?->SupplierName ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">
                                                {{ $lpo->OrderDate?->format('M d') }}
                                            </div>
                                            @if($lpo->TotalAmount)
                                                <div class="small text-warning">
                                                    {{ number_format($lpo->TotalAmount, 2) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <div>No award-based LPOs yet</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Direct Procurement Recent LPOs -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-shipping-fast"></i> Recent Direct Procurement LPOs
                                </h6>
                            </div>
                            <div class="card-body">
                                @forelse($recentDirectLPOs as $lpo)
                                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                                        <div class="flex-grow-1">
                                            <strong class="text-info">{{ $lpo->OrderNo }}</strong>
                                            <div class="small text-muted">
                                                Plan: {{ $lpo->getOriginationReferenceDisplay() }}
                                            </div>
                                            <div class="small text-muted">
                                                Supplier: {{ $lpo->supplier?->SupplierName ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">
                                                {{ $lpo->OrderDate?->format('M d') }}
                                            </div>
                                            @if($lpo->TotalAmount)
                                                <div class="small text-info">
                                                    {{ number_format($lpo->TotalAmount, 2) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <div>No direct procurement LPOs yet</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt"></i> Quick Actions & Guidelines
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="text-primary">LPO Origination Guidelines:</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong>Contract-Based:</strong> Use for items covered under executed contracts
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-warning me-2"></i>
                                                <strong>Award-Based:</strong> Use for approved awards below contract threshold
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-info me-2"></i>
                                                <strong>Direct Procurement:</strong> Use for pre-approved plan items under direct procurement method
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                <strong>RFQ-Based:</strong> Traditional method for competitive procurement processes
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-primary">System Status:</h6>
                                <div class="mb-2">
                                    <span class="badge bg-success me-2">✓</span>
                                    Contract-based LPO Creation
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-success me-2">✓</span>
                                    Award-based LPO Creation  
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-success me-2">✓</span>
                                    Direct Procurement LPO Creation
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-success me-2">✓</span>
                                    Traditional RFQ-based LPOs
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
