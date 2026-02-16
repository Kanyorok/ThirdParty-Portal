@extends('layouts.app')

@section('title', 'Recurring Journals')

@section('content')
    <div class="container my-3">
        <!-- Card -->
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-sync-alt text-info me-2"></i> Recurring Journals data
                </h6>
                <a href="{{ route('recurrentjournal.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus-circle me-1"></i> New Recurring Journal
                </a>
            </div>

            <!-- Filter Section -->
            <div class="card-body border-bottom pb-0">
                <form action="{{ route('recurrentjournal.index') }}" method="GET" id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="ref_no" class="form-label small text-muted">Reference No</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-hashtag"></i></span>
                            <input type="text" class="form-control form-control-sm" id="ref_no" name="ref_no"
                                value="{{ request('ref_no') }}" placeholder="Search ref...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="reference_name" class="form-label small text-muted">Reference Name</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control form-control-sm" id="reference_name" name="reference_name"
                                value="{{ request('reference_name') }}" placeholder="Search name...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="date_from" class="form-label small text-muted">Start Date From</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control form-control-sm" id="date_from" name="date_from"
                                value="{{ request('date_from') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="date_to" class="form-label small text-muted">Start Date To</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control form-control-sm" id="date_to" name="date_to"
                                value="{{ request('date_to') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="frequency" class="form-label small text-muted">Frequency</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-clock"></i></span>
                            <select class="form-select form-select-sm" id="frequency" name="frequency">
                                <option value="all" {{ request('frequency') == 'all' ? 'selected' : '' }}>All Frequencies</option>
                                @foreach($frequencies as $code => $name)
                                    <option value="{{ $code }}" {{ request('frequency') == $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

{{--                    <div class="col-md-2">--}}
{{--                        <label for="approval_status" class="form-label small text-muted">Status</label>--}}
{{--                        <div class="input-group input-group-sm">--}}
{{--                            <span class="input-group-text bg-light"><i class="fas fa-check-circle"></i></span>--}}
{{--                            <select class="form-select form-select-sm" id="approval_status" name="approval_status">--}}
{{--                                <option value="all" {{ request('approval_status') == 'all' ? 'selected' : '' }}>All Statuses</option>--}}
{{--                                @foreach($approvalStatuses as $status)--}}
{{--                                    <option value="{{ $status }}" {{ request('approval_status') == $status ? 'selected' : '' }}>--}}
{{--                                        {{ ucfirst($status) }}--}}
{{--                                    </option>--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                    </div>--}}

                    <div class="col-md-2">
                        <label for="per_page" class="form-label small text-muted">Per Page</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-list-ol"></i></span>
                            <select class="form-select form-select-sm" id="per_page" name="per_page">
                                @foreach([10, 25, 50, 100] as $perPageOption)
                                    <option value="{{ $perPageOption }}" {{ request('per_page', 10) == $perPageOption ? 'selected' : '' }}>
                                        {{ $perPageOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 d-flex justify-content-end mb-3">
                        <button type="submit" class="btn btn-sm btn-primary me-2">
                            <i class="fas fa-filter me-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('recurrentjournal.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped1 text-center"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>
                                <a href="{{ route('recurrentjournal.index', array_merge(request()->query(), ['sort_by' => 'RefNo', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'RefNo' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Reference No.
                                    @if(request('sort_by') == 'RefNo')
                                        <i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Description</th>
                            <th>Frequency</th>
                            <th>
                                <a href="{{ route('recurrentjournal.index', array_merge(request()->query(), ['sort_by' => 'Date', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'Date' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Start Date
                                    @if(request('sort_by') == 'Date' || !request('sort_by'))
                                        <i class="fas fa-sort-{{ request('sort_direction', 'desc') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Cutoff Date</th>
                            <th>
                                <a href="{{ route('recurrentjournal.index', array_merge(request()->query(), ['sort_by' => 'ApprovalStatus', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'ApprovalStatus' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Approval
                                    @if(request('sort_by') == 'ApprovalStatus')
                                        <i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Next Run</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recurringJournals as $index => $recurring)
                            <tr>
                                <td>{{ $recurringJournals->firstItem() + $index }}</td>

                                <td>
                                    {{ $recurring->RefNo }}
                                    @if(!empty($recurring->IsReversed) && $recurring->IsReversed)
                                        <span class="badge bg-danger ms-2">Reversed</span>
                                    @endif
                                </td>

                                {{-- Reference Name --}}
                                <td>
                                    @if($recurring->recurringJournals && $recurring->recurringJournals->isNotEmpty())
                                        {{ $recurring->recurringJournals->first()->ReferenceName }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Frequency --}}
                                <td>{{ $frequencies[$recurring->recurringJournals->first()->Frequency] ?? 'Unknown' }}</td>

                                {{-- Start Date --}}
                                <td>
                                    @if($recurring->recurringJournals && $recurring->recurringJournals->isNotEmpty())
                                        {{ optional($recurring->recurringJournals->first())->StartDate ? \Carbon\Carbon::parse($recurring->recurringJournals->first()->StartDate)->format('d M Y') : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Cutoff Date --}}
                                <td>
                                    @if($recurring->recurringJournals && $recurring->recurringJournals->isNotEmpty())
                                        {{ optional($recurring->recurringJournals->first())->CuttOffDate ? \Carbon\Carbon::parse($recurring->recurringJournals->first()->CuttOffDate)->format('d M Y') : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Approval Status --}}
                                <td>
                                    @php
                                        $statusClass = match(strtolower($recurring->ApprovalStatus)) {
                                            'posted' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'draft' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ ucfirst($recurring->ApprovalStatus) ?? 'Pending' }}
                                    </span>
                                </td>

                                {{-- Next Run --}}
                                @if($recurring->recurringJournals && $recurring->recurringJournals->isNotEmpty())
                                    <td>
                                        {{ optional($recurring->recurringJournals->first())->NextRunDate ? \Carbon\Carbon::parse($recurring->recurringJournals->first()->NextRunDate)->format('d M Y') : 'Not Set' }}
                                    </td>
                                @else
                                    <td class="text-center">
                                        Not Set
                                    </td>
                                @endif

                                {{-- Action Buttons --}}
                                <td class="text-center">
                                    <a href="{{ route('recurrentjournal.show', $recurring->Id) }}"
                                       class="btn btn-sm btn-outline-info me-1" title="View Journal Lines">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @php
                                        $approvalStatus = strtolower($recurring->ApprovalStatus ?? '');
                                        $isLocked = in_array($approvalStatus, ['posted', 'rejected'], true);
                                    @endphp
                                    <a href="{{ route('recurrentjournal.edit', $recurring->Id) }}"
                                       class="btn btn-sm btn-outline-warning me-1 {{ $isLocked ? 'disabled' : '' }}"
                                       title="Edit" aria-disabled="{{ $isLocked ? 'true' : 'false' }}"
                                       style="{{ $isLocked ? 'pointer-events:none; opacity:.65;' : '' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger custom-delete-btn {{ $isLocked ? 'disabled' : '' }}"
                                            title="Delete"
                                            {{ $isLocked ? 'disabled' : '' }}
                                            @if(!$isLocked)
                                                data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{ $recurring->RefNo ?? ('#'.$recurring->Id) }}"
                                            data-route="{{ route('recurrentjournal.destroy', $recurring->Id) }}"
                                        @endif
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            No recurring journals have been added yet.
                                        </p>
                                        <a href="{{ route('recurrentjournal.create') }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Add Recurring Journal
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Showing {{ $recurringJournals->firstItem() ?? 0 }} to {{ $recurringJournals->lastItem() ?? 0 }} of {{ $recurringJournals->total() }} entries
                    </div>
                    <div>
                        {{ $recurringJournals->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.modals.delete-confirm')
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add loading indicator when form is submitted
        document.getElementById('filter-form').addEventListener('submit', function() {
            const tableContainer = document.querySelector('.table-responsive');
            if (tableContainer) {
                tableContainer.style.opacity = '0.6';
            }
        });

        // Initialize tooltips for action buttons
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                placement: 'top',
                delay: { show: 500, hide: 100 }
            });
        });
    });
</script>
@endsection

@section('styles')
    <style>
        /* Custom hover effect for table rows */
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        /* Compact button styling */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Ensure table cells are compact */
        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }

        /* Card shadow and border */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        /* Filter form styling */
        .form-label {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .input-group-text {
            border: none;
        }

        .form-control, .form-select {
            border: 1px solid #dee2e6;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Pagination styling */
        .pagination {
            margin-bottom: 0;
        }

        .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .page-link {
            color: #0d6efd;
            border: 1px solid #dee2e6;
        }

        .page-link:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
            color: #0a58ca;
        }

        /* Sortable column headers */
        th a {
            display: inline-flex;
            align-items: center;
            color: #212529;
            text-decoration: none;
        }

        th a:hover {
            color: #0d6efd;
        }

        /* Badge styling */
        .badge {
            padding: 0.35em 0.65em;
            font-weight: 500;
        }

        /* Responsive table on small screens */
        @media (max-width: 576px) {
            .table-responsive {
                font-size: 0.875rem;
            }

            .btn-sm {
                padding: 0.2rem 0.4rem;
            }

            .filter-form .col-md-2 {
                margin-bottom: 0.5rem;
            }
        }

        /* Animation for filter changes */
        .table-responsive {
            transition: opacity 0.3s ease;
        }

        /* Custom scrollbar for table */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
@endsection
