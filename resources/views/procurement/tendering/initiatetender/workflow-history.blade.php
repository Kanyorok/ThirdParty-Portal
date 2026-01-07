@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Workflow History - {{ $tender->TenderNo }}</h4>
                    <a href="{{ route('initiatetender.show', $tender->Id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Tender
                    </a>
                </div>

                <div class="card-body">
                    {{-- Tender Information --}}
                    <div class="mb-4 p-3 bg-light rounded">
                        <h5>Tender Details</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Tender No:</strong> {{ $tender->TenderNo }}</p>
                                <p><strong>Title:</strong> {{ $tender->Title }}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Current Status:</strong>
                                    <span class="badge badge-{{ $tender->ApprovalStatusBadge ?? 'secondary' }} text-dark border">
                                        {{ $tender->ApprovalStatus?->label ?? 'Pending' }}
                                    </span>
                                </p>
                                <p><strong>Current Stage:</strong>
                                    <span class="badge badge-info text-white">
                                        {{ $currentStage['name'] ?? 'N/A' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Approval Type:</strong> {{ $approvalType ?? 'N/A' }}</p>
                                <p><strong>Approvers Needed:</strong>
                                    <span class="font-weight-bold text-dark">
                                        {{ $approversNeeded ?? 0 }}
                                    </span>
                                </p>
                            </div>

                        </div>
                    </div>

                    {{-- Workflow History Timeline --}}
                    @if($history && $history->count() > 0)
                    <h5 class="mb-3">Approval History</h5>
                    <div class="timeline">
                        @foreach($history as $index => $item)
                        <div class="timeline-item">
                            <div class="timeline-marker 
                                        @if($item->isApproved === true) bg-success
                                        @elseif($item->isApproved === false) bg-danger
                                        @else bg-warning
                                        @endif">
                            </div>
                            <div class="timeline-content">
                                <div class="card mb-3 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="mb-1">
                                                    <span class="badge badge-lg 
                                                                @if($item->isApproved === true) badge-success
                                                                @elseif($item->isApproved === false) badge-danger
                                                                @else badge-warning
                                                                @endif">
                                                        {{ $item->getActionType() }}
                                                    </span>
                                                    <span class="badge badge-secondary">
                                                        {{ $item->stage->StageName ?? 'Stage ' . $item->Stage }}
                                                    </span>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="far fa-clock"></i>
                                                    {{ $item->CreatedOn->format('M d, Y h:i A') }}
                                                </small>
                                            </div>
                                            @if($index === 0)
                                            <span class="badge badge-primary">Latest</span>
                                            @endif
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-2">
                                                    <strong><i class="fas fa-user"></i> Actioned By:</strong>
                                                    {{ $item->creator->FullName ?? 'System' }}
                                                </p>
                                                <p class="mb-2">
                                                    <strong><i class="fas fa-info-circle"></i> Status:</strong>
                                                    {{ $item->status->Description ?? 'N/A' }}
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                @if($item->Amount)
                                                <p class="mb-2">
                                                    <strong><i class="fas fa-money-bill"></i> Amount:</strong>
                                                    {{ $tender->currency->CurrencySymbol ?? '' }}
                                                    {{ number_format($item->Amount, 2) }}
                                                </p>
                                                @endif
                                                @if($item->modifier)
                                                <p class="mb-2">
                                                    <strong><i class="fas fa-edit"></i> Modified By:</strong>
                                                    {{ $item->modifier->FullName }}
                                                </p>
                                                @endif
                                            </div>
                                        </div>

                                        @if($item->Notes)
                                        <div class="mt-3 p-3 bg-light rounded">
                                            <strong><i class="fas fa-comment"></i> Notes:</strong>
                                            <p class="mb-0 mt-2">{{ $item->Notes }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p class="mb-0">No workflow history available for this tender.</p>
                        <small class="text-muted">History will appear here once approval workflow actions are taken.</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
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
        width: 3px;
        background: linear-gradient(180deg, #007bff 0%, #e9ecef 100%);
    }

    .timeline-item {
        position: relative;
        padding-left: 60px;
        margin-bottom: 30px;
    }

    .timeline-marker {
        position: absolute;
        left: 11px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 0 0 3px #e9ecef;
        z-index: 1;
    }

    .timeline-content {
        padding-top: 0;
    }

    .badge-lg {
        padding: 0.5em 0.75em;
        font-size: 0.875rem;
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
    }
</style>
@endpush
@endsection