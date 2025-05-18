@extends('layouts.app')

@section('title', 'Tender Details - ' . ($tender->TenderNo ?? 'N/A'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #3A7BD5;
        --primary-hover: #2E68BD;
        --secondary: #6C757D;
        --success: #198754;
        --info: #0D6EFD;
        --warning: #FFC107;
        --danger: #DC3545;
        --light: #F8F9FA;
        --dark: #212529;
        --border: #E0E5EC;
        --bg: #F7F8FA;
        --card-bg: #FFFFFF;
        --text-muted: #6C757D;
        --text-dark: #212529;
        --text-light: #495057;
        --font-family: 'Inter', sans-serif;
        --shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        --radius: 0.625rem;
        --transition: all 0.2s ease;
    }

    body {
        background-color: var(--bg);
        color: var(--text-dark);
        font-family: var(--font-family);
        line-height: 1.6;
    }

    /* Header Section */
    .page-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--border);
    }

    .page-title {
        color: var(--dark);
        font-weight: 700;
        font-size: 1.75rem;
        display: flex;
        align-items: center;
    }

    .page-title i {
        color: var(--primary);
        margin-right: 0.75rem;
        font-size: 1.5rem;
    }

    /* Main Card */
    .card-main {
        background: var(--card-bg);
        border: none;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .card-header {
        background: var(--card-bg);
        border-bottom: 1px solid var(--border);
        padding: 1.25rem 1.5rem;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .card-title {
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--dark);
        display: flex;
        align-items: center;
    }

    .card-title i {
        color: var(--primary);
        margin-right: 0.5rem;
    }

    /* Tabs */
    .nav-tabs {
        border-bottom: 1px solid var(--border);
        padding: 0 0.5rem;
        background: var(--light);
    }

    .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: var(--text-light);
        font-weight: 600;
        padding: 0.75rem 1.25rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .nav-link.active,
    .nav-link:hover {
        color: var(--primary);
        border-bottom-color: var(--primary);
        background: transparent;
    }

    .tab-content {
        padding: 1.5rem;
    }

    /* Detail Grid */
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .detail-label {
        font-weight: 500;
        color: var(--text-muted);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
    }

    .detail-value {
        font-size: 0.95rem;
        color: var(--text-dark);
        font-weight: 500;
    }

    .detail-content {
        background: var(--light);
        padding: 1rem;
        border-radius: 0.375rem;
        border: 1px solid var(--border);
        line-height: 1.7;
    }

    /* Timeline */
    .timeline {
        list-style: none;
        padding-left: 0;
        position: relative;
    }

    .timeline:before {
        content: '';
        position: absolute;
        left: 14px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--border);
        border-radius: 3px;
    }

    .timeline-item {
        margin-bottom: 1.5rem;
        position: relative;
        padding-left: 45px;
    }

    .timeline-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        border: 3px solid var(--bg);
    }

    .timeline-content {
        background: white;
        padding: 1rem;
        border-radius: 0.5rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
    }

    /* Status Classes */
    .completed {
        color: var(--success);
    }

    .pending {
        color: var(--warning);
    }

    .overdue {
        color: var(--danger);
    }

    .default {
        color: var(--secondary);
    }

    /* Buttons */
    .btn {
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        font-weight: 600;
        border-radius: 0.375rem;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-outline-primary:hover {
        background: var(--primary);
        color: white;
    }

    /* Empty States */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 2rem;
        margin-bottom: 0.75rem;
        opacity: 0.7;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .nav-tabs .nav-link {
            padding: 0.5rem;
            font-size: 0.8rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-lg-4 py-4">

    <div class="page-header">
        <h1 class="page-title">
            <i class="far fa-file-alt"></i>Tender Details
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('initiatetender.index') }}" class="btn btn-light border">
                <i class="fas fa-chevron-left"></i> Back
            </a>
            @if($tender->Status instanceof \App\Enums\TenderStatusEnum && ($tender->Status === \App\Enums\TenderStatusEnum::Draft || $tender->Status === \App\Enums\TenderStatusEnum::PendingApproval))
            <a href="{{ route('initiatetender.edit', $tender->Id) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endif
        </div>
    </div>

    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-info-circle"></i>
            {{ $tender->TenderNo ?? 'N/A' }} - {{ Str::limit($tender->Title ?? 'Untitled Tender', 50) }}
        </div>
        @if($tender->Status instanceof \App\Enums\TenderStatusEnum)
        <span class="badge rounded-pill bg-{{ $tender->Status->badgeClass() }}-subtle text-{{ $tender->Status->badgeClass() }}">
            {{ $tender->Status->displayName() ?? $tender->Status->name }}
        </span>
        @else
        <span class="badge rounded-pill bg-secondary-subtle text-secondary">
            {{ $tender->Status ?? 'Unknown' }}
        </span>
        @endif
    </div>

    <div class="card-body p-0">
        <ul class="nav nav-tabs" id="tenderTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                    <i class="fas fa-binoculars"></i> Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="scope-tab" data-bs-toggle="tab" data-bs-target="#scope" type="button" role="tab">
                    <i class="fas fa-bullseye"></i> Scope
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab">
                    <i class="fas fa-calendar-check"></i> Timeline
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                    <i class="fas fa-folder-open"></i> Documents
                </button>
            </li>
        </ul>

        <div class="tab-content" id="tenderTabsContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="detail-grid">
                    <div>
                        <div class="detail-label">Tender Number</div>
                        <div class="detail-value">{{ $tender->TenderNo ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="detail-label">Title</div>
                        <div class="detail-value">{{ $tender->Title ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="detail-label">Type</div>
                        <div class="detail-value">
                            @if($tender->TenderType instanceof \App\Enums\TenderTypeEnum)
                            <span class="badge bg-{{ $tender->TenderType->badgeClass() }}-subtle text-{{ $tender->TenderType->badgeClass() }}">
                                {{ $tender->TenderType->displayName() }}
                            </span>
                            @else {{ $tender->TenderType ?? 'N/A' }} @endif
                        </div>
                    </div>
                    <div>
                        <div class="detail-label">Category</div>
                        <div class="detail-value">
                            @if($tender->TenderCategory instanceof \App\Enums\TenderCategoryEnum)
                            {{ $tender->TenderCategory->displayName() }}
                            @else {{ $tender->TenderCategory ?? 'N/A' }} @endif
                        </div>
                    </div>
                    <div>
                        <div class="detail-label">Procurement Method</div>
                        <div class="detail-value">{{ $tender->procurementMode->ModeName ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="detail-label">PR ID</div>
                        <div class="detail-value">{{ $tender->RelatedPRID ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="detail-label">Estimated Value</div>
                        <div class="detail-value">{{ $tender->Currency ?? '' }} {{ number_format($tender->EstimatedValue ?? 0, 2) }}</div>
                    </div>
                    <div>
                        <div class="detail-label">Start Date</div>
                        <div class="detail-value">
                            @if($tender->StartDate)
                            {{ \Carbon\Carbon::parse((string)$tender->StartDate)->format('M d, Y') }}
                            @else N/A @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scope Tab -->
            <div class="tab-pane fade" id="scope" role="tabpanel">
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">Scope of Work</h6>
                    <div class="detail-content">
                        {!! nl2br(e($tender->ScopeOfWork ?? 'No scope provided.')) !!}
                    </div>
                </div>
                <div>
                    <h6 class="fw-semibold mb-3">Instructions</h6>
                    <div class="detail-content">
                        {!! nl2br(e($tender->Instructions ?? 'No instructions provided.')) !!}
                    </div>
                </div>
            </div>

            <!-- Timeline Tab -->
            <div class="tab-pane fade" id="timeline" role="tabpanel">
                <h6 class="fw-semibold mb-3">Key Dates</h6>
                <div class="detail-grid mb-4">
                    <div>
                        <div class="detail-label">Submission Deadline</div>
                        <div class="detail-value">
                            @if($tender->SubmissionDeadline)
                            {{ \Carbon\Carbon::parse((string)$tender->SubmissionDeadline)->format('M d, Y, h:i A') }}
                            @else N/A @endif
                        </div>
                    </div>
                    <div>
                        <div class="detail-label">Opening Date</div>
                        <div class="detail-value">
                            @if($tender->OpeningDate)
                            {{ \Carbon\Carbon::parse((string)$tender->OpeningDate)->format('M d, Y, h:i A') }}
                            @else N/A @endif
                        </div>
                    </div>
                </div>

                <h6 class="fw-semibold mb-3">Procurement Stages</h6>
                @if($tender->stages && $tender->stages->count() > 0)
                <ul class="timeline">
                    @foreach($tender->stages->sortBy('StartDate') as $stage)
                    @php
                    $stageStatus = 'default';
                    $icon = 'fa-hourglass-half';

                    try {
                    $startDate = $stage->StartDate ? \Carbon\Carbon::parse((string)$stage->StartDate) : null;
                    $endDate = $stage->EndDate ? \Carbon\Carbon::parse((string)$stage->EndDate) : null;
                    $actualEndDate = $stage->ActualEndDate ? \Carbon\Carbon::parse((string)$stage->ActualEndDate) : null;

                    if ($actualEndDate) {
                    $stageStatus = 'completed';
                    $icon = 'fa-check';
                    } elseif ($endDate && now()->gt($endDate)) {
                    $stageStatus = 'overdue';
                    $icon = 'fa-exclamation-triangle';
                    } elseif ($startDate && $endDate && now()->between($startDate, $endDate)) {
                    $stageStatus = 'pending';
                    $icon = 'fa-cogs';
                    }
                    } catch (\Exception $e) {}
                    @endphp

                    <li class="timeline-item">
                        <span class="timeline-icon bg-{{ $stageStatus }}">
                            <i class="fas {{ $icon }}"></i>
                        </span>
                        <div class="timeline-content">
                            <div class="fw-semibold">{{ $stage->Stage }}</div>
                            <div class="small text-muted">
                                Planned:
                                {{ $startDate ? $startDate->format('M d') : 'N/A' }} -
                                {{ $endDate ? $endDate->format('M d, Y') : 'N/A' }}
                            </div>
                            <div class="small text-muted">Duration: {{ $stage->DurationDays }} days</div>
                            @if($actualEndDate)
                            <div class="small text-success fw-semibold">
                                Completed: {{ $actualEndDate->format('M d, Y') }}
                            </div>
                            @elseif($stageStatus == 'overdue')
                            <div class="small text-danger fw-semibold">Overdue</div>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No timeline stages defined</p>
                </div>
                @endif
            </div>

            <!-- Documents Tab -->
            <div class="tab-pane fade" id="documents" role="tabpanel">
                <h6 class="fw-semibold mb-3">Attachments</h6>
                @if($tender->attachments && $tender->attachments->count() > 0)
                <div class="list-group">
                    @foreach($tender->attachments as $file)
                    <a href="{{-- route('tender.document.download', $file->id) --}}"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-file-pdf text-danger me-2"></i>
                            {{ $file->original_name ?? 'Document' }}
                        </div>
                        <small class="text-muted">{{-- $file->size_in_kb --}} KB</small>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <i class="fas fa-file-excel"></i>
                    <p>No documents attached</p>
                </div>
                @endif

                @if($tender->TenderType instanceof \App\Enums\TenderTypeEnum && $tender->TenderType === \App\Enums\TenderTypeEnum::Restricted)
                <h6 class="fw-semibold mt-4 mb-3">Invited Suppliers</h6>
                @if($tender->invitedSuppliers && $tender->invitedSuppliers->count() > 0)
                <div class="list-group">
                    @foreach($tender->invitedSuppliers as $supplier)
                    <div class="list-group-item">
                        <div class="fw-semibold">{{ $supplier->name }}</div>
                        <small class="text-muted">{{ $supplier->email }}</small>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <p>No suppliers invited</p>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab persistence
        const hash = window.location.hash;
        if (hash) {
            const tab = document.querySelector(`.nav-link[data-bs-target="${hash}"]`);
            if (tab) bootstrap.Tab.getOrCreateInstance(tab).show();
        }

        // Update URL hash when tab changes
        document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                if (history.pushState) {
                    history.pushState(null, null, target);
                } else {
                    window.location.hash = target;
                }
            });
        });
    });
</script>
@endpush