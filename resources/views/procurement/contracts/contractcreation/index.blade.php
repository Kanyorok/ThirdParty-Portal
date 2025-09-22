@extends('layouts.app')
@section('title', 'Procurement Contracts')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">📜 Procurement Contracts</h4>
                        <p class="text-muted mb-0">Manage contracts created from approved tender awards</p>
                    </div>
                    <div>
                        <a href="{{ route('procawards.index') }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-trophy"></i> View Awards
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

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Contract Status</label>
                                <select name="status_filter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="Draft Created" {{ ($filters['status_filter'] ?? '') === 'Draft Created' ? 'selected' : '' }}>Draft Created</option>
                                    <option value="Under Review" {{ ($filters['status_filter'] ?? '') === 'Under Review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="Approved" {{ ($filters['status_filter'] ?? '') === 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Sent to Legal" {{ ($filters['status_filter'] ?? '') === 'Sent to Legal' ? 'selected' : '' }}>Sent to Legal</option>
                                    <option value="Executed" {{ ($filters['status_filter'] ?? '') === 'Executed' ? 'selected' : '' }}>Executed</option>
                                    <option value="Terminated" {{ ($filters['status_filter'] ?? '') === 'Terminated' ? 'selected' : '' }}>Terminated</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search tender ref, title, or supplier..." 
                                       value="{{ $filters['search'] ?? '' }}">
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
                                        <th>Contract Value</th>
                            <th>Status</th>
                                        <th>Timeline</th>
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
                                                    @if($contract->tender)
                                                        <div class="text-muted small">
                                                            Award: {{ $contract->Id }}
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
                                                @if($contract->ContractValue)
                                                    <strong class="text-success">
                                                        {{ number_format($contract->ContractValue, 2) }}
                                                    </strong>
                                                    <div class="text-muted small">
                                                        {{ is_object($contract->tender->Currency) ? $contract->tender->Currency->Code : ($contract->tender->Currency ?? 'KES') }}
                                                    </div>
                                                @elseif($contract->AwardedAmount)
                                                    <strong class="text-info">
                                                        {{ number_format($contract->AwardedAmount, 2) }}
                                                    </strong>
                                                    <div class="text-muted small">
                                                        {{ is_object($contract->tender->Currency) ? $contract->tender->Currency->Code : ($contract->tender->Currency ?? 'KES') }} (Awarded)
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
                                                        <strong>Start:</strong> {{ $contract->ContractStartDate->format('M d, Y') }}
                                                    </div>
                                                    <div class="text-danger small">
                                                        <strong>End:</strong> {{ $contract->ContractEndDate->format('M d, Y') }}
                                                    </div>
                                                @elseif($contract->AwardDate)
                                                    <div class="text-muted small">
                                                        <strong>Awarded:</strong> {{ $contract->AwardDate->format('M d, Y') }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    @if($contract->hasContract())
                                                        <a href="{{ route('contracts.show', $contract->Id) }}" 
                                                           class="btn btn-sm btn-outline-info" title="View Contract">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if($contract->ContractStatus !== 'Executed')
                                                            <a href="{{ route('contracts.edit', $contract->Id) }}" 
                                                               class="btn btn-sm btn-outline-primary" title="Edit Contract">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endif
                                                    @elseif($contract->isContractReady())
                                                        <a href="{{ route('contracts.create', ['award_id' => $contract->Id]) }}" 
                                                           class="btn btn-sm btn-success" title="Create Contract">
                                                            <i class="fas fa-plus"></i> Create
                                                        </a>
                                                    @endif
                                                    
                                                    @if($contract->ContractStatus === 'Sent to Legal')
                                                        <span class="btn btn-sm btn-outline-warning" title="With Legal Department">
                                                            <i class="fas fa-balance-scale"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">No Contracts Found</h6>
                                                <p class="text-muted">No contracts have been created yet.</p>
                                                <a href="{{ route('procawards.index') }}" class="btn btn-primary">
                                                    <i class="fas fa-trophy"></i> View Awards to Create Contracts
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
                                {{ $contracts->appends($filters)->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-file-contract fa-2x mb-2"></i>
                                <h5>{{ $contracts->total() }}</h5>
                                <small>Total Contracts</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h5>{{ $contracts->where('ContractStatus', 'Draft Created')->count() + $contracts->where('ContractStatus', 'Under Review')->count() }}</h5>
                                <small>Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h5>{{ $contracts->where('ContractStatus', 'Executed')->count() }}</h5>
                                <small>Active</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-balance-scale fa-2x mb-2"></i>
                                <h5>{{ $contracts->where('ContractStatus', 'Sent to Legal')->count() }}</h5>
                                <small>With Legal</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection