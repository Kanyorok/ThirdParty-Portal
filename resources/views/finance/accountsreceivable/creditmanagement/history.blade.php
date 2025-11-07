@extends('layouts.app')
@section('title','Credit History')

@section('content')
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0">Credit History</h5>
                <p class="text-muted mb-0">{{ $credit->customer->ThirdPartyName ?? '-' }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('creditmanagement.show', $credit->Id) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Profile
                </a>
                <button class="btn btn-sm btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print History
                </button>
            </div>
        </div>

        <div class="row">
            <!-- Credit Profile Summary -->
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Current Credit Profile</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="small text-muted">Current Limit</div>
                                <div class="h6 mb-0">KES {{ number_format($credit->CreditLimit, 2) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Credit Used</div>
                                <div class="h6 mb-0">KES {{ number_format($used ?? 0, 2) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Available Credit</div>
                                <div class="h6 mb-0 text-success">KES {{ number_format($available ?? 0, 2) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Utilization</div>
                                <div class="h6 mb-0">{{ number_format($util ?? 0, 1) }}%</div>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div
                                        class="progress-bar {{ ($util ?? 0) < 50 ? 'bg-success' : (($util ?? 0) < 80 ? 'bg-warning' : 'bg-danger') }}"
                                        style="width: {{ $util ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credit History Timeline -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Credit Movement History</h6>
                        <div class="small text-muted">{{ $movements ? $movements->count() : 0 }} records</div>
                    </div>
                    <div class="card-body p-0">
                        @if($movements && $movements->count())
                            <div class="timeline">
                                @foreach($movements as $movement)
                                    <div class="timeline-item border-bottom px-4 py-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <div class="small text-muted">
                                                    @if($movement->EffectiveOn)
                                                        {{ \Carbon\Carbon::parse($movement->EffectiveOn)->format('M d, Y') }}
                                                    @else
                                                        -
                                                    @endif
                                                </div>
                                                <div class="text-muted small">
                                                    @if($movement->CreatedOn)
                                                        {{ \Carbon\Carbon::parse($movement->CreatedOn)->format('H:i') }}
                                                    @else
                                                        -
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                @php
                                                    $typeInfo = match($movement->MovementType) {
                                                        'initial_approval' => ['icon' => 'fas fa-check-circle', 'class' => 'text-success', 'label' => 'Initial Approval'],
                                                        'limit_set' => ['icon' => 'fas fa-plus-circle', 'class' => 'text-info', 'label' => 'Limit Set'],
                                                        'adjustment_increase' => ['icon' => 'fas fa-arrow-up', 'class' => 'text-success', 'label' => 'Credit Increase'],
                                                        'adjustment_decrease' => ['icon' => 'fas fa-arrow-down', 'class' => 'text-danger', 'label' => 'Credit Decrease'],
                                                        'adjustment_revision' => ['icon' => 'fas fa-edit', 'class' => 'text-info', 'label' => 'Credit Revision'],
                                                        'usage' => ['icon' => 'fas fa-shopping-cart', 'class' => 'text-warning', 'label' => 'Credit Usage'],
                                                        'payment' => ['icon' => 'fas fa-money-bill-wave', 'class' => 'text-success', 'label' => 'Payment Received'],
                                                        default => ['icon' => 'fas fa-circle', 'class' => 'text-muted', 'label' => 'Other']
                                                    };
                                                @endphp
                                                <span class="{{ $typeInfo['class'] }}">
                                                <i class="{{ $typeInfo['icon'] }} me-2"></i>
                                                {{ $typeInfo['label'] }}
                                            </span>
                                            </div>
                                            <div class="col-md-2 text-end">
                                                @if(in_array($movement->MovementType, ['adjustment_increase', 'initial_approval', 'limit_set']))
                                                    <span
                                                        class="fw-medium text-success">+{{ number_format($movement->Amount, 2) }}</span>
                                                @elseif(in_array($movement->MovementType, ['adjustment_decrease', 'usage']))
                                                    <span
                                                        class="fw-medium text-danger">-{{ number_format($movement->Amount, 2) }}</span>
                                                @else
                                                    <span
                                                        class="fw-medium">{{ number_format($movement->Amount, 2) }}</span>
                                                @endif
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small">{{ $movement->Notes ?? '-' }}</div>
                                            </div>
                                            <div class="col-md-2">
                                                @if($movement->ReferenceType === 'credit_adjustment' && $movement->ReferenceID)
                                                    <a href="{{ route('creditadjustment.show', $movement->ReferenceID) }}"
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-external-link-alt"></i> View
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center p-5">
                                <i class="fas fa-history text-muted fa-3x mb-3"></i>
                                <p class="text-muted">No credit movements recorded yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        :root {
            --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
        }

        body, .card, .table {
            font-family: var(--font-sans);
        }

        .timeline-item:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        .timeline-item:last-child {
            border-bottom: none !important;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .container, .container * {
                visibility: visible;
            }

            .btn {
                display: none !important;
            }
        }
    </style>
@endsection
