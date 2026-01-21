@extends('layouts.app')

@section('title', 'Cheque Management')



@section('content')
    <div class="container-fluid my-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-money-check-alt me-2"></i> </h4>
            <div class="d-flex gap-2">
                <a href="{{ route('finance.cheques.issued.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-pen-nib me-1"></i> Issue Cheque
                </a>
                <a href="{{ route('finance.cheques.received.create') }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-hand-holding-usd me-1"></i> Receive Cheque
                </a>
            </div>
        </div>

        <div class="card shadow-sm rounded-3">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link {{ $dir === 'ISSUED' ? 'active' : '' }}"
                           href="{{ route('finance.cheques.index', ['dir' => 'ISSUED']) }}">
                           <i class="fas fa-arrow-circle-up me-1"></i> Issued Cheques
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $dir === 'RECEIVED' ? 'active' : '' }}"
                           href="{{ route('finance.cheques.index', ['dir' => 'RECEIVED']) }}">
                           <i class="fas fa-arrow-circle-down me-1"></i> Received Cheques
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Filters --}}
            <div class="card-body border-bottom bg-light py-3">
                <form action="{{ route('finance.cheques.index') }}" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="dir" value="{{ $dir }}">
                    
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach($chequeLeafStatuses as $leafStatus)
                                <option value="{{ $leafStatus->Value }}" {{ request('status') == $leafStatus->Value ? 'selected' : '' }}>
                                    {{ $leafStatus->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small text-muted fw-bold">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                   value="{{ request('search') }}" 
                                   placeholder="Cheque No, Party Name, Amount...">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('finance.cheques.index', ['dir' => $dir]) }}" class="btn btn-outline-secondary ms-1">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-striped">
                        <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Status</th>
                            <th>Cheque Details</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Party</th>
                            <th>Bank / Book</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="ps-3">{{ $loop->iteration }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($row->Status) {
                                            'Cleared' => 'success',
                                            'Bounced' => 'danger',
                                            'Deposited' => 'info',
                                            'OnHand' => 'warning text-dark',
                                            'Issued' => 'primary',
                                            'Cancelled' => 'secondary',
                                            default => 'dark'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }} rounded-pill">
                                        {{ $row->Status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row->ChequeNumber }}</div>
                                    @if($row->IsPostDated)
                                        <small class="badge bg-light text-danger border border-danger">PDC</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $row->ChequeDate }}</div>
                                    @if($row->IsPostDated)
                                        <small class="text-muted">Due: {{ $row->DueDate }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ number_format($row->Amount, 2) }}</div>
                                    <small class="text-muted">{{ $row->currency?->Code }}</small>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;" title="{{ $row->PartyName }}">
                                        {{ $row->PartyName ?: '-' }}
                                    </div>
                                    <small class="text-muted">{{ $row->PartyType }}</small>
                                </td>
                                <td>
                                    @if($row->Direction === 'ISSUED')
                                        <div class="text-truncate" style="max-width: 150px;" title="{{ $row->bankAccount?->bank?->BankName }}">
                                            {{ $row->bankAccount?->bank?->BankName }}
                                        </div>
                                        <small class="text-muted">{{ $row->chequeBook?->ChequeBookID ? 'Book #'.$row->chequeBook->ChequeBookID : '' }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('finance.cheques.show', $row->ChequeID) }}"
                                       class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p class="mb-0">No {{ strtolower($dir) }} cheques found.</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0">
                {{ $rows->appends(['dir' => $dir, 'status' => request('status'), 'search' => request('search')])->links() }}
            </div>
        </div>
    </div>
@endsection


