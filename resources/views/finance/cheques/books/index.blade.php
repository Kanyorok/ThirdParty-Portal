@extends('layouts.app')

@section('title', 'Cheque Books')

@section('content')
    <div class="container-fluid my-3">
        <div class="card shadow-sm rounded-3">
            {{-- Header --}}
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-book text-info me-2"></i> Manage Cheque Books
                </h6>
                <a href="{{ route('finance.chequebooks.create') }}" class="btn btn-info btn-sm text-white">
                    <i class="fas fa-plus-circle me-1"></i> New Cheque Book
                </a>
            </div>

            {{-- Filters --}}
            <div class="card-body border-bottom pb-0">
                <form action="{{ route('finance.chequebooks.index') }}" method="GET" id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Bank Account</label>
                        <select name="bank_id" class="form-select select2">
                            <option value="">All Accounts</option>
                            @foreach($bankAccounts as $ba)
                                <option value="{{ $ba->AccountID }}" {{ request('bank_id') == $ba->AccountID ? 'selected' : '' }}>
                                    {{ optional($ba->bank)->BankName }} - {{ $ba->AccountNumber }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-toggle-on"></i></span>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                @foreach($chequeBookStatuses as $status)
                                    <option value="{{ strtolower($status->Value) }}" {{ request('status') == strtolower($status->Value) ? 'selected' : '' }}>
                                        {{ $status->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small text-muted">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Book Name, Prefix, ID...">
                        </div>
                    </div>

                    <div class="col-md-4 d-flex justify-content-end mb-3">
                        <button type="submit" class="btn btn-sm btn-primary me-2">
                            <i class="fas fa-filter me-1"></i> Apply
                        </button>
                        <a href="{{ route('finance.chequebooks.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped mb-0">
                        <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Bank Account</th>
                            <th>Book Name</th>
                            <th>Range</th>
                            <th>Next Leaf</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($rows as $b)
                            <tr>
                                <td class="ps-3">{{ $b->ChequeBookID }}</td>
                                <td>
                                    <div class="fw-bold">{{ optional($b->bankAccount->bank)->BankName }}</div>
                                    <small class="text-muted">{{ $b->bankAccount?->AccountNumber }}</small>
                                </td>
                                <td>
                                    {{ $b->BookName ?? '-' }}
                                    @if($b->Prefix || $b->Suffix)
                                        <br><small class="text-muted">Format: {{ $b->Prefix }}...{{ $b->Suffix }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $b->StartNumber }}</span>
                                    <i class="fas fa-arrow-right small text-muted mx-1"></i>
                                    <span class="badge bg-light text-dark border">{{ $b->EndNumber }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">{{ $b->NextLeafNumber }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                            @php $pct = $b->LeavesTotal > 0 ? ($b->LeavesIssued / $b->LeavesTotal) * 100 : 0; @endphp
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <small class="ms-2 text-muted">{{ $b->LeavesIssued }}/{{ $b->LeavesTotal }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($b->IsActive)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group">
                                        <a href="{{ route('finance.chequebooks.show', $b->ChequeBookID) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Manage Leaves" data-bs-toggle="tooltip">
                                            <i class="fas fa-list"></i>
                                        </a>
                                        <a href="{{ route('finance.chequebooks.edit', $b->ChequeBookID) }}"
                                           class="btn btn-sm btn-outline-primary" title="Edit Book" data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger custom-delete-btn"
                                                title="Delete Book" 
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="{{ $b->BookName ?? 'Book #'.$b->ChequeBookID }}"
                                                data-route="{{ route('finance.chequebooks.destroy', $b->ChequeBookID) }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-book-open fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p>No cheque books found.</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0">
                {{ $rows->links() }}
            </div>
        </div>
    </div>

    @include('components.modals.delete-confirm')
@endsection

@section('scripts')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>

    <style>
        .btn-group .btn { margin-left: 2px; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            // Initialize Select2
            $('.select2').select2({
                width: '100%'
            });
        });
    </script>
@endsection
