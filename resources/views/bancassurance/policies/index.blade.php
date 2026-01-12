@extends('layouts.app')
@section('title', 'Policy Proposals')

@section('content')
<div class="container my-4" style="max-width: 1200px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
        <a href="{{ route('bancassurance.policies.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-circle me-2"></i> New Proposal
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-medium text-muted">Status</label>
                    <select name="status" class="form-select rounded-pill shadow-sm">
                        <option value="">-- All --</option>
                        @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium text-muted">From (Start Date)</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control rounded-pill shadow-sm">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium text-muted">To (End Date)</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control rounded-pill shadow-sm">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium text-muted">Customer</label>
                    <input type="text" name="customer" value="{{ request('customer') }}"
                        class="form-control rounded-pill shadow-sm" placeholder="e.g. John Doe">
                </div>

                <div class="col-12 text-end mt-3">
                    <button class="btn btn-secondary rounded-pill px-4">
                        <i class="bi bi-funnel me-2"></i> Filter
                    </button>
                    <a href="{{ route('bancassurance.policies.index') }}" class="btn btn-outline-dark rounded-pill px-4">
                        <i class="bi bi-arrow-clockwise me-2"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.95rem; white-space: nowrap;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th style="min-width: 120px;">Customer</th>
                            <th style="min-width: 130px;">Product</th>
                            <th style="min-width: 100px;">Insurer</th>
                            <th class="text-end" style="min-width: 110px;">Sum Assured</th>
                            <th class="text-end" style="min-width: 90px;">Premium</th>
                            <th style="min-width: 100px;">Start Date</th>
                            <th style="min-width: 100px;">End Date</th>
                            <th style="min-width: 80px;">Status</th>
                            <th class="text-end" style="min-width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($policies as $policy)
                        <tr>
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td>{{ $policy->customer->thirdParty->ThirdPartyName ?? '-' }}</td>
                            <td>{{ $policy->product->Name ?? '-' }}</td>
                            <td>{{ $policy->insurer->Name ?? '—' }}</td>
                            <td class="text-end">{{ number_format($policy->SumAssured, 2) }}</td>
                            <td class="text-end">{{ number_format($policy->PremiumAmount, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->PolicyStartDate)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d M Y') }}</td>
                            <td>
                                <span class="badge 
                                        @if($policy->Status->value === 'approved') bg-success 
                                        @elseif($policy->Status->value === 'pending') bg-warning text-dark
                                        @elseif($policy->Status->value === 'rejected') bg-danger
                                        @else bg-secondary
                                        @endif
                                        rounded-pill px-3 py-2">
                                    {{ $policy->Status->Label() }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('bancassurance.policies.print', $policy->Id) }}"
                                target="_blank"
                                class="btn btn-outline-primary btn-sm">
                                    🖨️ Print
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No policy proposals found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
