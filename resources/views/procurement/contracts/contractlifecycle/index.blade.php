@extends('layouts.app')
@section('title', 'Contract Lifecycle Management')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">🔄 Contract Lifecycle Management</h4>
                        <p class="text-muted mb-0">Monitor and manage active and executed contracts</p>
                    </div>
                    <div>
                        <a href="{{ route('contracts.index') }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-list"></i> All Contracts
                        </a>
                        <a href="{{ route('contracts.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Contract
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

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-handshake fa-2x mb-2"></i>
                                <h5>{{ $contracts->where('ContractStatus', 'Executed')->count() }}</h5>
                                <small>Active Contracts</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h5>{{ $contracts->where('ContractStatus', 'Approved')->count() }}</h5>
                                <small>Pending Execution</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-times-circle fa-2x mb-2"></i>
                                <h5>{{ $contracts->where('ContractStatus', 'Terminated')->count() }}</h5>
                                <small>Terminated</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-2x mb-2"></i>
                                <h5>{{ $contracts->total() }}</h5>
                                <small>Total Contracts</small>
                            </div>
                        </div>
                    </div>
                </div>

        <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
            <div class="col-md-3">
                                <label class="form-label">Contract Status</label>
                                <select name="status_filter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="Approved" {{ request('status_filter') === 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Executed" {{ request('status_filter') === 'Executed' ? 'selected' : '' }}>Active/Executed</option>
                                    <option value="Terminated" {{ request('status_filter') === 'Terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>
                            <div class="col-md-6">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by contract ref, title, or supplier..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
            </div>
            </div>
        </form>
                    </div>
                </div>

        <!-- Contracts Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                            <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                                        <th>Contract Info</th>
                                        <th>Tender Details</th>
                                        <th>Supplier</th>
                            <th>Status</th>
                                        <th>Duration</th>
                                        <th>Value</th>
                                        <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                                    @forelse($contracts as $index => $contract)
                                        <tr>
                                            <td>{{ $index + 1 + ($contracts->currentPage() - 1) * $contracts->perPage() }}</td>
                                            <td>
                                                <div>
                                                    <strong class="text-primary">{{ $contract->ContractRef ?? 'PENDING' }}</strong>
                                                    @if($contract->ContractApprovedOn)
                                                        <div class="text-muted small">
                                                            Approved: {{ $contract->ContractApprovedOn->format('d/m/Y') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($contract->tender)
                                                    <div>
                                                        <strong>{{ $contract->tender->TenderNo }}</strong>
                                                        <div class="text-muted small">
                                                            {{ Str::limit($contract->tender->Title, 40) }}
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
                                                                {{ $contract->winningSupplier->ContactPerson }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $contract->contract_status_badge['class'] }}">
                                                    {{ $contract->contract_status_badge['text'] }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($contract->ContractStartDate && $contract->ContractEndDate)
                                                    <div class="text-success small">
                                                        <strong>Start:</strong> {{ $contract->ContractStartDate->format('d/m/Y') }}
                                                    </div>
                                                    <div class="text-danger small">
                                                        <strong>End:</strong> {{ $contract->ContractEndDate->format('d/m/Y') }}
                                                    </div>
                                                    @php
                                                        $now = now();
                                                        $daysRemaining = $now->diffInDays($contract->ContractEndDate, false);
                                                    @endphp
                                                    @if($daysRemaining > 0)
                                                        <div class="text-info small">
                                                            {{ $daysRemaining }} days remaining
                                                        </div>
                                                    @elseif($daysRemaining < 0)
                                                        <div class="text-warning small">
                                                            Expired {{ abs($daysRemaining) }} days ago
                                                        </div>
                                                    @else
                                                        <div class="text-warning small">Expires today</div>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Duration not set</span>
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
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('contracts.lifecycle.view', $contract->Id) }}" 
                                                       class="btn btn-sm btn-outline-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($contract->ContractStatus === 'Approved')
                                                        <a href="{{ route('contracts.lifecycle.execution', $contract->Id) }}" 
                                                           class="btn btn-sm btn-success" title="Execute Contract">
                                                            <i class="fas fa-handshake"></i>
                                                        </a>
                                                    @elseif($contract->ContractStatus === 'Executed')
                                                        <a href="{{ route('contracts.lifecycle.execution', $contract->Id) }}" 
                                                           class="btn btn-sm btn-primary" title="Monitor Execution">
                                                            <i class="fas fa-chart-line"></i>
                                                        </a>
                                                        <a href="{{ route('contracts.lifecycle.amend', $contract->Id) }}" 
                                                           class="btn btn-sm btn-warning" title="Amend Contract">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    @if(in_array($contract->ContractStatus, ['Approved', 'Executed']))
                                                        <a href="{{ route('contracts.lifecycle.terminate', $contract->Id) }}" 
                                                           class="btn btn-sm btn-danger" title="Terminate Contract">
                                                            <i class="fas fa-times-circle"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">No Lifecycle-Ready Contracts Found</h6>
                                                <p class="text-muted">No contracts in approved or executed status for lifecycle management.</p>
                                                <a href="{{ route('contracts.index') }}" class="btn btn-primary">
                                                    <i class="fas fa-list"></i> View All Contracts
                                                </a>
                            </td>
                        </tr>
                                    @endforelse
                        </tbody>
                    </table>
                        </div>

                        <!-- Pagination -->
                        @if($contracts->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $contracts->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection