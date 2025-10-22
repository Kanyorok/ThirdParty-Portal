@extends('layouts.app')
@section('title','Credit Management')

@section('content')
    <div class="container my-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-muted">
                    <i class="fas fa-user-shield text-info me-2"></i> Credit Profiles
                </h6>
                <a href="{{ route('creditmanagement.create') }}" class="btn btn-sm btn-info shadow-sm">
                    <i class="fas fa-plus me-1"></i> New Credit Profile
                </a>
            </div>

            {{-- Filters Toolbar --}}
            <div class="card-body border-bottom px-4 py-3">
                <form method="GET" action="{{ route('creditmanagement.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label small mb-1">Search</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                   placeholder="Customer name, ID number, email..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label small mb-1">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">All</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>
                                    Approved
                                </option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>
                                    Rejected
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label small mb-1">From</label>
                            <input type="date" class="form-control form-control-sm" id="date_from" name="date_from"
                                   value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label small mb-1">To</label>
                            <input type="date" class="form-control form-control-sm" id="date_to" name="date_to"
                                   value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('creditmanagement.index') }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Advanced Filters Toggle --}}
                <div class="mt-2">
                    <button class="btn btn-outline-info btn-sm" type="button" data-bs-toggle="collapse"
                            data-bs-target="#advancedFilters" aria-expanded="false">
                        <i class="fas fa-sliders-h me-1"></i> Advanced
                    </button>
                </div>

                <div class="collapse mt-3" id="advancedFilters">
                    <form method="GET" action="{{ route('creditmanagement.index') }}" class="row g-3 border-top pt-3">
                        <div class="col-md-3">
                            <label for="credit_from" class="form-label small mb-1">Credit Limit From</label>
                            <input type="number" class="form-control form-control-sm" id="credit_from"
                                   name="credit_from"
                                   placeholder="0.00" step="0.01" value="{{ request('credit_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="credit_to" class="form-label small mb-1">Credit Limit To</label>
                            <input type="number" class="form-control form-control-sm" id="credit_to" name="credit_to"
                                   placeholder="0.00" step="0.01" value="{{ request('credit_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="search_adv" class="form-label small mb-1">Search (Advanced)</label>
                            <input type="text" class="form-control form-control-sm" id="search_adv" name="search"
                                   placeholder="Customer name, ID number, email..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="status_adv" class="form-label small mb-1">Status</label>
                            <select class="form-select form-select-sm" id="status_adv" name="status">
                                <option value="">All</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>
                                    Approved
                                </option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>
                                    Rejected
                                </option>
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> Apply
                            </button>
                            <a href="{{ route('creditmanagement.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Data Table --}}
            <div class="card-body px-4 py-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                        <tr class="text-center small text-muted fw-semibold">
                            <th>#</th>
                            <th>Customer</th>
                            <th>ID Number</th>
                            <th>Credit Limit</th>
                            <th>Risk</th>
                            <th>Status</th>
                            <th>Last Review</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="text-center">
                        @if($credits->total() > 0)
                            @foreach($credits as $credit)
                                <tr>
                                    <td>{{ ($credits->currentPage() - 1) * $credits->perPage() + $loop->iteration }}</td>
                                    <td class="fw-medium">{{ $credit->customer->ThirdPartyName ?? '-'}}</td>
                                    <td>{{ $credit->customer->RegistrationNumber  ?? '-'}}</td>
                                    <td>{{ number_format($credit->CreditLimit,2) ?? '-' }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $credit->risk_badge_class }} px-2 py-1">{{ $credit->RiskLevel ?? 'Medium' }}</span>
                                        <div class="small text-muted mt-1">Score: {{ $credit->RiskScore ?? 50 }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $status = strtolower($credit->Status ?? 'pending');
                                            $statusClass = match($status) {
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'pending' => 'bg-warning text-dark',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }} px-2 py-1">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            Issued {{ \Carbon\Carbon::parse($credit->EffectiveFrom)->format('Y-m-d') ?? '-' }}</div>
                                        <div class="progress mt-1" style="height:6px;">
                                            <div
                                                class="progress-bar {{ ($credit->utilization ?? 0) < 50 ? 'bg-success' : ((($credit->utilization ?? 0) < 80) ? 'bg-warning' : 'bg-danger') }}"
                                                style="width: {{ $credit->utilization ?? 0 }}%"></div>
                                        </div>
                                        <div class="small fw-light">{{ number_format($credit->utilization ?? 0,2) }}%
                                            used
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('creditmanagement.show', $credit->Id) }}"
                                               class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('creditadjustment.index', ['customer_id' => $credit->CustomerID]) }}"
                                               class="btn btn-sm btn-outline-warning" title="Adjustments">
                                                <i class="fas fa-list"></i>
                                            </a>
                                            @php $isApproved = strtolower($credit->Status ?? '') === 'approved'; @endphp
                                            @if($isApproved)
                                                <a href="{{ route('creditadjustment.createWithId', $credit->Id) }}"
                                                   class="btn btn-sm btn-outline-success" title="Add Adjustment">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('creditmanagement.edit', $credit->Id) }}"
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger custom-delete-btn"
                                                        title="Delete"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#customDeleteConfirmModal"
                                                        data-name="{{ $credit->customer->ThirdPartyName ?? 'Credit Profile #'.$credit->Id }}"
                                                        data-route="{{ route('creditmanagement.destroy', $credit->Id) }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="p-0">
                                    <div class="text-center p-5 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            No credit profiles have been added yet.
                                        </p>
                                        <a href="{{ route('creditmanagement.create') }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Add Credit Profile
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($credits->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing {{ $credits->firstItem() }} to {{ $credits->lastItem() }} of {{ $credits->total() }}
                            results
                        </div>
                        <nav>
                            {{ $credits->links('pagination::bootstrap-4') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('components.modals.delete-confirm')
@endsection

@section('styles')
    <style>
        :root {
            --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
        }
        body, .card, .table { font-family: var(--font-sans); }

        .table-hover tbody tr:hover {
            background-color: #fafafa;
            transition: background-color .2s ease-in-out;
        }

        .card {
            border-radius: .75rem;
        }

        .btn-sm {
            padding: .25rem .55rem;
        }

        .form-label.small {
            font-size: 0.75rem;
            color: #6c757d;
        }
    </style>
@endsection
