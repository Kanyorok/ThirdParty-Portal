@extends('layouts.app')
@section('title', 'Chart of Accounts')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info">📚 General Ledgers</h5>
                <div class="d-flex gap-2">
                    @if(!empty($allowThirdPartyPosting))
                        <a href="{{ route('chartofaccounts.glsync') }}" class="btn btn-warning btn-sm p-2">
                            <i class="fas fa-sync-alt me-1"></i> Sync GL
                        </a>
                    @endif
                    <a href="{{ route('chartofaccounts.create') }}" class="btn btn-info btn-sm p-2">
                        <i class="fas fa-plus me-1"></i> Add New Account
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card-body border-bottom pb-0">
                <form action="{{ route('chartofaccounts.index') }}" method="GET" id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="gl_name" class="form-label small text-muted">GL Name</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control form-control-sm" id="gl_name" name="gl_name"
                                value="{{ request('gl_name') }}" placeholder="Search name...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="gl_code" class="form-label small text-muted">Account Code</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-hashtag"></i></span>
                            <input type="text" class="form-control form-control-sm" id="gl_code" name="gl_code"
                                value="{{ request('gl_code') }}" placeholder="Search code...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="gl_type" class="form-label small text-muted">GL Type</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-layer-group"></i></span>
                            <select class="form-select form-select-sm" id="gl_type" name="gl_type">
                                <option value="">All Types</option>
                                @foreach($glTypes as $value => $label)
                                    <option value="{{ $value }}" {{ request('gl_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="gl_type_group" class="form-label small text-muted">Type Group</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-object-group"></i></span>
                            <select class="form-select form-select-sm" id="gl_type_group" name="gl_type_group">
                                <option value="">All Groups</option>
                                @foreach($glTypeGroups as $id => $name)
                                    <option value="{{ $id }}" {{ request('gl_type_group') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="status" class="form-label small text-muted">Status</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-toggle-on"></i></span>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="per_page" class="form-label small text-muted">Per Page</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-list-ol"></i></span>
                            <select class="form-select form-select-sm" id="per_page" name="per_page">
                                @foreach([25, 50, 100] as $perPageOption)
                                    <option value="{{ $perPageOption }}" {{ request('per_page', 25) == $perPageOption ? 'selected' : '' }}>
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
                        <a href="{{ route('chartofaccounts.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-body p-3">
                <p class="text-muted">
                    The Chart of Accounts organizes all GL accounts for assets, liabilities, income, expenses, and
                    equity to support accurate reporting.
                </p>

                @if($charts->count())
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle table-striped table-striped1"
                               style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                            <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">
                                    <a href="{{ route('chartofaccounts.index', array_merge(request()->query(), ['sort_by' => 'GLName', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'GLName' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                        GL Name
                                        @if(request('sort_by') == 'GLName')
                                            <i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{{ route('chartofaccounts.index', array_merge(request()->query(), ['sort_by' => 'GLCode', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'GLCode' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                        Account Code
                                        @if(request('sort_by') == 'GLCode')
                                            <i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                @if(!empty($allowThirdPartyPosting))
                                    <th scope="col">MappedGL</th>
                                @endif
                                <th scope="col">
                                    <a href="{{ route('chartofaccounts.index', array_merge(request()->query(), ['sort_by' => 'GLAccountTypeID', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'GLAccountTypeID' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                        GL Type
                                        @if(request('sort_by') == 'GLAccountTypeID')
                                            <i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col">GL Type Group</th>
                                <th scope="col">GL Sub-Type</th>
                                <th scope="col">Description</th>
                                <th scope="col">
                                    <a href="{{ route('chartofaccounts.index', array_merge(request()->query(), ['sort_by' => 'IsActive', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'IsActive' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                        Status
                                        @if(request('sort_by') == 'IsActive')
                                            <i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($charts as $item)
                                <tr>
                                    <td>{{ $charts->firstItem() + $loop->index }}</td>
                                    <td>{{ $item->GLName ?? '-' }}</td>
                                    <td>{{ $item->GLCode ?? '-' }}</td>
                                    @if(!empty($allowThirdPartyPosting))
                                        <td>{{ $item->MappedGLCode ?? '-' }}</td>
                                    @endif
                                    <td>{{ $item->GLAccountTypeID ?? '-' }}</td>
                                    <td>{{ $item->typeGroup->Description ?? '-' }}</td>
                                    <td>{{ $item->subAccount->Description ?? '-' }}</td>
                                    <td>{{ $item->Description ?? '-' }}</td>
                                    <td>
                                        @php
                                            $isActive = (bool) $item->IsActive;
                                            $badge = $isActive ? 'bg-success' : 'bg-danger';
                                            $label = $isActive ? 'Active' : 'Inactive';
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $label }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('chartofaccounts.edit', $item->Id) }}"
                                           class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

{{--                                        <button type="button"--}}
{{--                                                class="btn btn-sm btn-outline-danger custom-delete-btn"--}}
{{--                                                title="Delete"--}}
{{--                                                data-bs-toggle="modal"--}}
{{--                                                data-bs-target="#customDeleteConfirmModal"--}}
{{--                                                data-name="{{ $item->GLName }}"--}}
{{--                                                data-route="{{ route('chartofaccounts.destroy', $item->Id) }}">--}}
{{--                                            <i class="fas fa-trash-alt"></i>--}}
{{--                                        </button>--}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center p-4 border rounded-3 bg-light">
                        <p class="mb-3 text-muted fs-5">
                            <i class="fas fa-info-circle me-2 text-info"></i>
                            <i>No GL accounts found.</i>
                        </p>
                        <a href="{{ route('chartofaccounts.create') }}" class="btn btn-info px-4 py-2">
                            <i class="fas fa-plus-circle me-2"></i> Add Account
                        </a>
                    </div>
                @endif
            </div>

            @if($charts->hasPages())
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2 px-3">
                    <small class="text-muted mb-0">
                        Showing {{ $charts->firstItem() ?? 0 }} to {{ $charts->lastItem() ?? 0 }}
                        of {{ $charts->total() }} accounts
                    </small>
                    <div class="mb-0">
                        {{ $charts->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
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
