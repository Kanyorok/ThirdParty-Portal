@extends('layouts.app')
@section('title', 'Contract Details')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">📄 Contract Details</h4>
                        <p class="text-muted mb-0">
                            Contract Reference: <strong>{{ $contract->ContractRef ?? 'PENDING' }}</strong>
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Contracts
                        </a>
                        @if($contract->ContractStatus !== 'Executed' && $contract->ContractStatus !== 'Terminated')
                            <a href="{{ route('contracts.edit', $contract->Id) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit Contract
                            </a>
                        @endif
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Contract Status Card -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-file-contract fa-3x {{ strpos($contract->contract_status_badge['class'], 'success') !== false ? 'text-success' : (strpos($contract->contract_status_badge['class'], 'warning') !== false ? 'text-warning' : 'text-primary') }} mb-3"></i>
                                <h5>Contract Status</h5>
                                <span class="badge {{ $contract->contract_status_badge['class'] }} fs-6">
                                    {{ $contract->contract_status_badge['text'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-calendar-alt fa-3x text-info mb-3"></i>
                                <h5>Contract Duration</h5>
                                @if($contract->ContractStartDate && $contract->ContractEndDate)
                                    <div class="text-muted">
                                        {{ $contract->ContractStartDate->format('M d, Y') }} - 
                                        {{ $contract->ContractEndDate->format('M d, Y') }}
                                    </div>
                                    <small class="text-success">
                                        ({{ $contract->ContractStartDate->diffInDays($contract->ContractEndDate) }} days)
                                    </small>
                                @else
                                    <span class="text-muted">Duration not set</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-money-bill-wave fa-3x text-success mb-3"></i>
                                <h5>Contract Value</h5>
                                @if($contract->ContractValue)
                                    <strong class="text-success fs-5">
                                        {{ number_format($contract->ContractValue, 2) }}
                                    </strong>
                                    <div class="text-muted">{{ is_object($contract->tender->Currency) ? $contract->tender->Currency->Code : ($contract->tender->Currency ?? 'KES') }}</div>
                                @else
                                    <span class="text-muted">Value not set</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Award Information -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">🏆 Award Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Tender Reference:</strong></td>
                                        <td>{{ $contract->tender->TenderNo ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tender Title:</strong></td>
                                        <td>{{ $contract->tender->Title ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Award Date:</strong></td>
                                        <td>{{ $contract->AwardDate ? $contract->AwardDate->format('M d, Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Award Status:</strong></td>
                                        <td>
                                            <span class="badge {{ $contract->status_badge['class'] }}">
                                                {{ $contract->status_badge['text'] }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Winning Supplier:</strong></td>
                                        <td>{{ $contract->winningSupplier->SupplierName ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Contact Person:</strong></td>
                                        <td>{{ $contract->winningSupplier->ContactPerson ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ $contract->winningSupplier->Email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td>{{ $contract->winningSupplier->Mobile ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contract Details -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">📋 Contract Terms & Conditions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary">💳 Payment Terms</h6>
                                <div class="bg-light p-3 rounded">
                                    {{ $contract->PaymentTerms ?: 'Payment terms not specified' }}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary">🚚 Delivery Terms</h6>
                                <div class="bg-light p-3 rounded">
                                    {{ $contract->DeliveryTerms ?: 'Delivery terms not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        @if($contract->SpecialConditions)
                            <div class="mt-3">
                                <h6 class="text-primary">⚖️ Special Conditions</h6>
                                <div class="bg-light p-3 rounded">
                                    {{ $contract->SpecialConditions }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Contract Actions -->
                @if($contract->hasContract())
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">⚡ Contract Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if($contract->ContractStatus === 'Draft Created')
                                    <div class="col-md-3">
                                        <button class="btn btn-success w-100" onclick="submitForReview()">
                                            <i class="fas fa-paper-plane"></i>
                                            Submit for Review
                                        </button>
                                    </div>
                                @endif
                                
                                @if($contract->ContractStatus === 'Approved')
                                    <div class="col-md-3">
                                        <button class="btn btn-primary w-100" onclick="executeContract()">
                                            <i class="fas fa-handshake"></i>
                                            Execute Contract
                                        </button>
                                    </div>
                                @endif
                                
                                @if($contract->ContractStatus === 'Executed')
                                    <div class="col-md-3">
                                        <a href="{{ route('contracts.lifecycle.execution', $contract->Id) }}" class="btn btn-info w-100">
                                            <i class="fas fa-chart-line"></i>
                                            Monitor Performance
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="{{ route('contracts.lifecycle.amend', $contract->Id) }}" class="btn btn-warning w-100">
                                            <i class="fas fa-edit"></i>
                                            Amend Contract
                                        </a>
                                    </div>
                                @endif

                                @if(in_array($contract->ContractStatus, ['Draft Created', 'Under Review', 'Approved', 'Executed']))
                                    <div class="col-md-3">
                                        <a href="{{ route('contracts.lifecycle.terminate', $contract->Id) }}" class="btn btn-danger w-100">
                                            <i class="fas fa-times-circle"></i>
                                            Terminate Contract
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Contract History/Timeline -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">📊 Contract Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Award Approved</h6>
                                    <p class="timeline-description">
                                        Tender awarded to {{ $contract->winningSupplier->SupplierName ?? 'N/A' }}
                                    </p>
                                    <small class="text-muted">{{ $contract->ApprovedOn ? $contract->ApprovedOn->format('M d, Y H:i') : 'N/A' }}</small>
                                </div>
                            </div>
                            
                            @if($contract->hasContract())
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Contract Created</h6>
                                        <p class="timeline-description">
                                            Contract {{ $contract->ContractRef }} created
                                        </p>
                                        <small class="text-muted">{{ $contract->ModifiedOn ? $contract->ModifiedOn->format('M d, Y H:i') : 'N/A' }}</small>
                                    </div>
                                </div>
                            @endif
                            
                            @if($contract->ContractApprovedOn)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Contract Approved</h6>
                                        <p class="timeline-description">
                                            Contract approved and ready for execution
                                        </p>
                                        <small class="text-muted">{{ $contract->ContractApprovedOn->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 50px;
        }
        
        .timeline-marker {
            position: absolute;
            left: 14px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 3px #dee2e6;
        }
        
        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            position: relative;
        }
        
        .timeline-title {
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .timeline-description {
            margin-bottom: 5px;
        }
    </style>

    <script>
        function submitForReview() {
            if (confirm('Submit this contract for review? Once submitted, you will not be able to make changes until it is reviewed.')) {
                // TODO: Implement contract review submission
                alert('Feature coming soon: Contract review workflow');
            }
        }
        
        function executeContract() {
            if (confirm('Execute this contract? This will mark the contract as active and binding.')) {
                // TODO: Implement contract execution
                alert('Feature coming soon: Contract execution workflow');
            }
        }
    </script>
@endsection
