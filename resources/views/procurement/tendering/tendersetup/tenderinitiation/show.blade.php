@extends('layouts.app')

@section('title', 'Tender Details - ' . ($tender->TenderNo ?? 'N/A'))

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #3A7BD5; /* Blueish */
            --primary-hover: #2E68BD;
            --secondary: #6C757D; /* Grey */
            --success: #198754; /* Green */
            --info: #0DCAF0;    /* Cyan/Info */
            --warning: #FFC107; /* Yellow */
            --danger: #DC3545;  /* Red */
            --light: #F8F9FA;  /* Light Grey */
            --dark: #212529;   /* Dark/Black */
            --border: #E0E5EC;
            --bg: #F7F8FA; /* Slightly off-white background */
            --card-bg: #FFFFFF;
            --text-muted: #6C757D;
            --text-dark: #212529;
            --text-light-shade: #495057; /* For less prominent text */
            --font-family: 'Inter', sans-serif;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            --radius: 0.625rem; /* 10px */
            --transition: all 0.2s ease-in-out;
        }

        body {
            background-color: var(--bg);
            color: var(--text-dark);
            font-family: var(--font-family), sans-serif;
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

        .card-main-details {
            background: var(--card-bg);
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-main-details .card-header {
            background: linear-gradient(to right, var(--primary), var(--primary-hover));
            color: white;
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.5rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .card-main-details .card-title {
            font-weight: 600;
            font-size: 1.2rem;
            color: white;
            display: flex;
            align-items: center;
        }

        .card-main-details .card-title i {
            margin-right: 0.6rem;
        }

        .card-main-details .card-header .badge {
            font-size: 0.9em;
        }


        /* Tabs */
        .nav-tabs {
            border-bottom: 1px solid var(--border);
            padding: 0 0.5rem;
            background: var(--light);
            border-top-left-radius: var(--radius);
            border-top-right-radius: var(--radius);
        }

        .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: var(--text-light-shade);
            font-weight: 600;
            padding: 0.85rem 1.35rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .nav-link.active,
        .nav-link:hover {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: transparent; /* Keep background transparent on hover/active */
        }
        .nav-link i {
            opacity: 0.8;
        }
        .nav-link.active i,
        .nav-link:hover i {
            opacity: 1;
        }


        .tab-content {
            padding: 1.75rem; /* Increased padding for tab content */
            background-color: var(--card-bg);
            border-bottom-left-radius: var(--radius);
            border-bottom-right-radius: var(--radius);
        }

        /* Detail Grid */
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Slightly wider minmax */
            gap: 1.75rem; /* Increased gap */
            margin-bottom: 1.75rem;
        }
        .detail-item div:first-child { /* For items within the grid cell */
            margin-bottom: 0.5rem;
        }

        .detail-label {
            font-weight: 500;
            color: var(--text-muted);
            font-size: 0.8rem; /* Slightly larger label */
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 0.3rem; /* Adjusted margin */
        }

        .detail-value {
            font-size: 1rem; /* Slightly larger value */
            color: var(--text-dark);
            font-weight: 500;
        }
        .detail-value .badge {
            vertical-align: middle;
        }

        .detail-content { /* For larger text blocks like scope/instructions */
            background: var(--light);
            padding: 1.25rem; /* Increased padding */
            border-radius: 0.5rem; /* Slightly larger radius */
            border: 1px solid var(--border);
            line-height: 1.7;
            font-size: 0.95rem;
        }
        .detail-content p:last-child {
            margin-bottom: 0;
        }

        /* Timeline Styling */
        .timeline {
            list-style: none;
            padding-left: 0;
            position: relative;
            margin-top: 1rem;
        }

        .timeline:before {
            content: '';
            position: absolute;
            left: 18px; /* Adjusted for icon size */
            top: 0;
            bottom: 0;
            width: 4px; /* Thicker line */
            background: var(--border);
            border-radius: 4px;
        }

        .timeline-item {
            margin-bottom: 2rem; /* Increased spacing */
            position: relative;
            padding-left: 55px; /* Adjusted for icon size and spacing */
        }

        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 40px; /* Larger icon */
            height: 40px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem; /* Larger icon font */
            border: 4px solid var(--card-bg); /* Border matches card background for "floating" effect */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .timeline-content {
            background: white;
            padding: 1.25rem;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06); /* Softer shadow */
        }
        .timeline-content .fw-semibold {
            font-size: 1.05rem;
            color: var(--text-dark);
        }
        .timeline-content .small {
            font-size: 0.85rem;
        }


        /* Badge styling (assuming enums have badgeClass method returning primary, success, etc.) */
        .badge.bg-secondary-subtle { background-color: rgba(var(--secondary-rgb), 0.15) !important; color: var(--secondary) !important; }
        :root {
            --primary-rgb: 58, 123, 213; /* From your --primary #3A7BD5 */
            --success-rgb: 25, 135, 84;  /* From your --success #198754 */
            --info-rgb:    13, 202, 240; /* From your --info #0DCAF0 */
            --secondary-rgb: 108, 117, 125;
            --warning-rgb: 255, 193, 7;
            --danger-rgb: 220, 53, 69;
            --dark-rgb: 33, 37, 41;
        }


        /* Buttons */
        .btn {
            font-size: 0.9rem; /* Slightly larger button font */
            padding: 0.6rem 1.2rem; /* Adjusted padding */
            font-weight: 600;
            border-radius: 0.375rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: capitalize;
        }
        .btn-light.border { border-color: var(--border) !important; }
        .btn-outline-primary:hover { background: var(--primary); color: white; }

        /* List Group for Documents/Suppliers */
        .list-group-item {
            border-color: var(--border);
            padding: 0.85rem 1.25rem;
        }
        .list-group-item-action:hover {
            background-color: var(--light);
        }

        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 2.5rem; /* Increased padding */
            color: var(--text-muted);
            background-color: var(--light);
            border-radius: var(--radius);
            border: 1px dashed var(--border);
        }

        .empty-state i {
            font-size: 2.5rem; /* Larger icon */
            margin-bottom: 1rem;
            opacity: 0.6;
        }
        .empty-state p {
            font-size: 1rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr; /* Single column on smaller screens */
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .nav-tabs .nav-link {
                padding: 0.75rem 0.5rem; /* Adjust padding for smaller screens */
                font-size: 0.85rem;
            }
            .tab-content {
                padding: 1.25rem;
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
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('initiatetender.index') }}" class="btn btn-light border">
                    <i class="fas fa-chevron-left"></i> Back to List
                </a>
                @if($tender->Status instanceof \App\Enums\TenderStatusEnum && $tender->Status === \App\Enums\TenderStatusEnum::Draft)
                    <a href="{{ route('initiatetender.edit', $tender->Id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i> Edit Tender
                    </a>
                @endif
            </div>
        </div>

        <div class="card-main-details">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-info-circle"></i>
                    {{ $tender->TenderNo ?? 'N/A' }} - {{ Str::limit($tender->Title ?? 'Untitled Tender', 60) }}
                </div>
                @if($tender->Status instanceof \App\Enums\TenderStatusEnum)
                    <span class="badge rounded-pill bg-{{ $tender->Status->badgeClass() }}-subtle text-{{ $tender->Status->badgeClass() }}">
                        {{ $tender->Status->displayName() }}
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
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overviewTabPane" type="button" role="tab" aria-controls="overviewTabPane" aria-selected="true">
                            <i class="fas fa-binoculars"></i> Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="scope-tab" data-bs-toggle="tab" data-bs-target="#scopeTabPane" type="button" role="tab" aria-controls="scopeTabPane" aria-selected="false">
                            <i class="fas fa-bullseye"></i> Scope & Instructions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timelineTabPane" type="button" role="tab" aria-controls="timelineTabPane" aria-selected="false">
                            <i class="fas fa-calendar-alt"></i> Timeline & Stages
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documentsTabPane" type="button" role="tab" aria-controls="documentsTabPane" aria-selected="false">
                            <i class="fas fa-paperclip"></i> Documents & Suppliers
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="tenderTabsContent">
                    <div class="tab-pane fade show active" id="overviewTabPane" role="tabpanel" aria-labelledby="overview-tab">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Tender Number</div>
                                <div class="detail-value">{{ $tender->TenderNo ?? 'N/A' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Title</div>
                                <div class="detail-value">{{ $tender->Title ?? 'N/A' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Type</div>
                                <div class="detail-value">
                                    @if($tender->TenderType instanceof \App\Enums\TenderTypeEnum)
                                        <span class="badge bg-{{ $tender->TenderType->badgeClass() }}-subtle text-{{ $tender->TenderType->badgeClass() }} rounded-pill">
                                    {{ $tender->TenderType->displayName() }}
                                </span>
                                    @else {{ $tender->TenderType ?? 'N/A' }} @endif
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Category</div>
                                <div class="detail-value">
                                    @if($tender->TenderCategory instanceof \App\Enums\TenderCategoryEnum)
                                        {{ $tender->TenderCategory->displayName() }}
                                    @else {{ $tender->TenderCategory ?? 'N/A' }} @endif
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Procurement Method</div>
                                <div class="detail-value">{{ $tender->procurementMode->Name ?? ($tender->procurementMode->ModeName ?? 'N/A') }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Related PR No.</div>
                                <div class="detail-value">{{ $tender->RelatedPRID ? 'PR/' . $tender->RelatedPRID : 'N/A' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Estimated Value</div>
                                <div class="detail-value">
                                    {{ $tender->currency->Code ?? '' }} {{ number_format($tender->EstimatedValue ?? 0, 2) }}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Tender Start Date</div>
                                <div class="detail-value">
                                    {{ $tender->StartDate ? $tender->StartDate->format('M d, Y') : 'N/A' }}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Submission Deadline</div>
                                <div class="detail-value">
                                    {{ $tender->SubmissionDeadline ? $tender->SubmissionDeadline->format('M d, Y, h:i A') : 'N/A' }}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Opening Date</div>
                                <div class="detail-value">
                                    {{ $tender->OpeningDate ? $tender->OpeningDate->format('M d, Y, h:i A') : 'N/A' }}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Created By</div>
                                <div class="detail-value">{{ $tender->creator->name ?? 'N/A' }}</div> {{-- Assuming 'name' on User model --}}
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Created On</div>
                                <div class="detail-value">{{ $tender->CreatedOn ? $tender->CreatedOn->format('M d, Y, h:i A') : 'N/A' }}</div>
                            </div>
                            @if($tender->ModifiedBy && $tender->ModifiedOn && $tender->ModifiedOn->ne($tender->CreatedOn))
                                <div class="detail-item">
                                    <div class="detail-label">Last Modified By</div>
                                    <div class="detail-value">{{ $tender->modifier->name ?? 'N/A' }}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Last Modified On</div>
                                    <div class="detail-value">{{ $tender->ModifiedOn->format('M d, Y, h:i A') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="tab-pane fade" id="scopeTabPane" role="tabpanel" aria-labelledby="scope-tab">
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-2"><i class="fas fa-ruler-combined me-2 text-primary"></i>Scope of Work</h6>
                            <div class="detail-content">
                                @if(!empty($tender->ScopeOfWork))
                                    {!! nl2br(e($tender->ScopeOfWork)) !!}
                                @else
                                    <p class="text-muted fst-italic">No scope of work provided.</p>
                                @endif
                            </div>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-2"><i class="fas fa-clipboard-list me-2 text-primary"></i>Instructions to Bidders</h6>
                            <div class="detail-content">
                                @if(!empty($tender->Instructions))
                                    {!! nl2br(e($tender->Instructions)) !!}
                                @else
                                    <p class="text-muted fst-italic">No instructions provided.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="timelineTabPane" role="tabpanel" aria-labelledby="timeline-tab">
                        <h6 class="fw-semibold mb-4"><i class="fas fa-tasks me-2 text-primary"></i>Procurement Stages & Timeline</h6>
                        @if($tender->stages && $tender->stages->count() > 0)
                            <ul class="timeline">
                                @foreach($tender->stages->sortBy('StartDate') as $stage)
                                    @php
                                        $stageStatusClass = 'default';
                                        $iconClass = 'fa-hourglass-half';

                                        $carbonStartDate = $stage->StartDate ? Carbon\Carbon::parse($stage->StartDate) : null;
                                        $carbonEndDate = $stage->EndDate ? Carbon\Carbon::parse($stage->EndDate) : null;
                                        $carbonActualEndDate = $stage->ActualEndDate ? Carbon\Carbon::parse($stage->ActualEndDate) : null;
                                        $now = Carbon\Carbon::now();

                                        if ($carbonActualEndDate) {
                                            $stageStatusClass = 'completed';
                                            $iconClass = 'fa-check-circle';
                                        } elseif ($carbonEndDate && $now->gt($carbonEndDate)) {
                                            $stageStatusClass = 'overdue';
                                            $iconClass = 'fa-exclamation-triangle';
                                        } elseif ($carbonStartDate && $carbonEndDate && $now->between($carbonStartDate, $carbonEndDate)) {
                                            $stageStatusClass = 'pending';
                                            $iconClass = 'fa-cogs';
                                        } elseif ($carbonStartDate && $now->lt($carbonStartDate)) {
                                            $stageStatusClass = 'default';
                                            $iconClass = 'fa-calendar-alt';
                                        }
                                    @endphp
                                    <li class="timeline-item">
                            <span class="timeline-icon bg-{{ $stageStatusClass }}">
                                <i class="fas {{ $iconClass }}"></i>
                            </span>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <h6 class="fw-semibold mb-1">{{ $stage->Stage ?? 'Unnamed Stage' }}</h6>
                                                <span class="badge rounded-pill bg-{{$stageStatusClass}}-subtle text-{{$stageStatusClass}} ms-2">{{ Str::title(str_replace('_', ' ', $stageStatusClass)) }}</span>
                                            </div>
                                            <div class="small text-muted mb-1">
                                                Planned:
                                                {{ $carbonStartDate ? $carbonStartDate->format('M d, Y') : 'N/A' }} -
                                                {{ $carbonEndDate ? $carbonEndDate->format('M d, Y') : 'N/A' }}
                                                (@if($stage->DurationDays){{ $stage->DurationDays }} day(s)@else N/A @endif
                                            </div>
                                            @if($carbonActualEndDate)
                                                <div class="small text-success fw-semibold">
                                                    <i class="fas fa-flag-checkered me-1"></i>Completed: {{ $carbonActualEndDate->format('M d, Y') }}
                                                </div>
                                            @elseif($stageStatusClass == 'overdue')
                                                <div class="small text-danger fw-semibold">This stage is overdue.</div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-stream text-muted"></i>
                                <p>No procurement stages have been defined for this tender yet.</p>
                            </div>
                        @endif
                    </div>

                    <div class="tab-pane fade" id="documentsTabPane" role="tabpanel" aria-labelledby="documents-tab">
                        <h6 class="fw-semibold mb-3"><i class="fas fa-folder-open me-2 text-primary"></i>Attached Documents</h6>
                        @if($tender->documents && $tender->documents->count() > 0) {{-- Changed from attachments to documents --}}
                        <div class="list-group">
                            @foreach($tender->documents as $document) {{-- Changed from attachments to documents --}}
                            <a href="{{ route('tender.document.download', $document->Id) }}" {{-- Assuming this route exists --}}
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas {{ $document->iconByMimeType() ?? 'fa-file-alt' }} me-2 text-primary"></i> {{-- Assuming iconByMimeType() on TenderDocument model --}}
                                    {{ $document->FileName ?? 'Document' }}
                                </div>
                                <span class="badge bg-light text-dark rounded-pill">{{ $document->FileSize ? Str::bytesToHuman($document->FileSize) : '' }}</span>
                            </a>
                            @endforeach
                        </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-file-excel text-muted"></i>
                                <p>No documents are currently attached to this tender.</p>
                            </div>
                        @endif

                        @if($tender->TenderType instanceof \App\Enums\TenderTypeEnum && $tender->TenderType === \App\Enums\TenderTypeEnum::Restricted)
                            <h6 class="fw-semibold mt-4 mb-3"><i class="fas fa-users me-2 text-primary"></i>Invited Suppliers</h6>
                            @if($tender->invitedSuppliers && $tender->invitedSuppliers->count() > 0)
                                <div class="list-group">
                                    @foreach($tender->invitedSuppliers as $supplier)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-user-tie me-2 text-muted"></i>
                                                <span class="fw-semibold">{{ $supplier->Name ?? 'N/A' }}</span>
                                                @if($supplier->email) <small class="text-muted ms-2">({{ $supplier->email }})</small> @endif
                                            </div>
                                            {{-- TODO: add supplier response status here if available from pivot data --}}
                                            {{-- Example: <span class="badge bg-info-subtle text-info">{{ $supplier->pivot->ResponseStatus ?? 'Pending' }}</span> --}}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-users-slash text-muted"></i>
                                    <p>No suppliers have been specifically invited for this restricted tender.</p>
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
            // Tab persistence using URL hash
            const activateTabFromHash = () => {
                const hash = window.location.hash;
                if (hash) {
                    const tabTrigger = document.querySelector(`.nav-link[data-bs-target="${hash}"]`);
                    if (tabTrigger) {
                        const tabInstance = bootstrap.Tab.getOrCreateInstance(tabTrigger);
                        if (tabInstance) {
                            tabInstance.show();
                        }
                    }
                }
            };

            // Update URL hash when tab changes
            document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(tabEl => {
                tabEl.addEventListener('shown.bs.tab', function(event) {
                    const targetPaneId = event.target.getAttribute('data-bs-target');
                    if (targetPaneId && history.pushState) {
                        history.pushState(null, null, targetPaneId);
                    } else {
                        window.location.hash = targetPaneId;
                    }
                });
            });

            // Activate tab on initial load and on hash change (for back/forward button)
            activateTabFromHash();
            window.addEventListener('hashchange', activateTabFromHash, false);
        });
    </script>
@endpush

