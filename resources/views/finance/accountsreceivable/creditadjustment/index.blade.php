@extends('layouts.app')
@section('title','Credit Adjustments')

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
                <i class="fas fa-adjust text-primary me-2"></i> Credit Adjustments
            </h6>
            <div class="d-flex gap-2">
                <a href="{{ route('creditmanagement.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Profiles
                </a>
{{--                <a href="{{ route('creditadjustment.createWithId') }}" class="btn btn-sm btn-primary shadow-sm">--}}
{{--                    <i class="fas fa-plus me-1"></i> New Adjustment--}}
{{--                </a>--}}
            </div>
        </div>

        <div class="card-body px-4 py-3">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-center small text-muted">
                            <th>#</th>
                            <th>Customer</th>
                            <th>Adjustment Type</th>
                            <th>Amount</th>
                            <th>New Limit</th>
                            <th>Requested By</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @if($adjustments->count())
                            @foreach($adjustments as $adjustment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-medium text-start">{{ $adjustment->customer->ThirdPartyName ?? '-' }}</td>
                                    <td>
                                        @php
                                            $typeClass = match($adjustment->AdjustmentType) {
                                                'increase' => 'text-success',
                                                'decrease' => 'text-danger',
                                                'revision' => 'text-info',
                                                default => 'text-muted'
                                            };
                                            $typeIcon = match($adjustment->AdjustmentType) {
                                                'increase' => 'fas fa-arrow-up',
                                                'decrease' => 'fas fa-arrow-down',
                                                'revision' => 'fas fa-edit',
                                                default => 'fas fa-question'
                                            };
                                        @endphp
                                        <span class="{{ $typeClass }}">
                                            <i class="{{ $typeIcon }} me-1"></i>
                                            {{ $adjustment->getAdjustmentTypeLabel() }}
                                        </span>
                                    </td>
                                    <td class="fw-medium">{{ number_format($adjustment->Amount, 2) }}</td>
                                    <td>{{ number_format($adjustment->NewCreditLimit, 2) }}</td>
                                    <td>{{ $adjustment->requestedByUser->Name ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $adjustment->getStatusBadgeClass() }} px-2 py-1">
                                            {{ ucfirst($adjustment->ApprovalStatus) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">{{ $adjustment->CreatedOn->format('M d, Y') }}</div>
                                        <div class="text-muted small">{{ $adjustment->CreatedOn->format('H:i') }}</div>
                                    </td>
                                    <td>
                                        <a href="{{ route('creditadjustment.show', $adjustment->Id) }}" class="btn btn-sm btn-outline-info me-1" title="View Adjustment">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($adjustment->isPending())
                                            <a href="{{ route('creditadjustment.edit', $adjustment->Id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Adjustment">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger custom-delete-btn"
                                                    title="Delete Adjustment"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#customDeleteConfirmModal"
                                                    data-name="Adjustment for {{ $adjustment->customer->ThirdPartyName ?? 'Customer' }}"
                                                    data-route="{{ route('creditadjustment.destroy', $adjustment->Id) }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            <i>No credit adjustments have been made yet.</i>
                                        </p>
                                        <a href="{{ route('creditadjustment.create') }}" class="btn btn-primary px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Create Adjustment
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Pagination if needed --}}
            @if($adjustments->count() > 0)
                <nav class="mt-3">
                    <ul class="pagination pagination-sm justify-content-end mb-0">
                        <li class="page-item disabled"><span class="page-link">«</span></li>
                        <li class="page-item active"><span class="page-link">1</span></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">»</a></li>
                    </ul>
                </nav>
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
        background-color: #fdfdfd;
        transition: background-color .2s ease-in-out;
    }
    .card { border-radius: .75rem; }
    .btn-sm { padding: .25rem .55rem; }
</style>
@endsection

