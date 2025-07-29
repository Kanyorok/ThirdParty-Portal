@extends('layouts.app')

@section('title', 'Registered Third Parties')

@section('styles')
<style>
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --secondary: #6b7280;
        --secondary-hover: #4b5563;
        --background: #f8fafc;
        --border: #e2e8f0;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
    }

    body {
        background-color: var(--background);
        color: var(--text-primary);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .card {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        background-color: white;
        margin-bottom: 2rem;
    }

    .card-header {
        background-color: transparent;
        border-bottom: 1px solid var(--border);
        border-radius: 1rem 1rem 0 0;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-body {
        padding: 2rem;
    }

    .form-control,
    .form-select {
        border-radius: 0.5rem;
        border: 1px solid var(--border);
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .input-group {
        max-width: 350px;
    }

    .input-group .form-control {
        border-right: none;
    }

    .input-group-text {
        background-color: white;
        border-left: none;
        border-color: var(--border);
    }

    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
    }

    .btn-outline-secondary {
        border-color: var(--border);
        color: var(--text-primary);
    }

    .btn-outline-secondary:hover {
        background-color: var(--border);
        border-color: var(--border);
    }

    .dataTables_wrapper {
        font-size: 0.875rem;
    }

    .dataTables_length select {
        min-width: 80px;
    }

    .dataTables_paginate .paginate_button {
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        border-radius: 0.5rem;
        border: 1px solid var(--border);
        color: var(--text-primary);
        background-color: white;
    }

    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .dataTables_paginate .paginate_button:hover:not(.current) {
        background-color: var(--border);
        border-color: var(--border);
    }

    table.dataTable {
        border-collapse: separate;
        border-spacing: 0;
        width: 100% !important;
    }

    table.dataTable thead th {
        background-color: #f8fafc;
        border-bottom: 1px solid var(--border);
        padding: 1rem 1.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-primary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    table.dataTable thead th:first-child {
        border-top-left-radius: 0.5rem;
    }

    table.dataTable thead th:last-child {
        border-top-right-radius: 0.5rem;
    }

    table.dataTable tbody td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        font-size: 0.875rem;
    }

    table.dataTable tbody tr:hover {
        background-color: rgba(37, 99, 235, 0.05);
    }

    table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .filter-collapse {
        transition: all 0.3s ease;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }

    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        .input-group {
            max-width: 100%;
        }

        .card-body {
            padding: 1.5rem;
        }

        .dataTables_paginate {
            text-align: center;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0 fw-bold">Third Parties Overview</h4>
                    <a href="{{ route('web.parties.create') }}" class="btn btn-primary d-flex align-items-center">
                        <i class="fas fa-plus me-2"></i> Add New Third Party
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div id="dt-length-container" class="d-flex align-items-center"></div>
                        </div>
                        <div class="col-md-6 d-flex flex-column flex-md-row justify-content-end align-items-center gap-3">
                            <div class="input-group">
                                <input type="search" class="form-control" id="filterFormQ" placeholder="Search third parties..." autocomplete="off" aria-label="Search third parties">
                                <span class="input-group-text" id="clearSearchSpan" style="display: none;">
                                    <button type="button" id="clearSearchBtn" class="btn btn-link p-0 text-muted" aria-label="Clear search">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </span>
                                <button class="btn btn-primary" type="button" id="searchButton" aria-label="Perform search">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <button class="btn btn-outline-secondary d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="false" aria-controls="advancedFilters">
                                <i class="fas fa-filter me-2"></i> Filters
                                <span id="activeFilterCount" class="badge bg-primary rounded-pill ms-2" style="display:none;"></span>
                            </button>
                        </div>
                    </div>

                    <div class="collapse mb-4 filter-collapse" id="advancedFilters">
                        <div class="card card-body border-0 shadow-sm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="filterType" class="form-label fw-semibold">Type</label>
                                    <select id="filterType" class="form-select" name="type" aria-label="Filter by Type">
                                        <option value="">All Types</option>
                                        @foreach (\App\Enums\ThirdPartyTypeEnum::cases() as $type)
                                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="filterStatus" class="form-label fw-semibold">Approval Status</label>
                                    <select id="filterStatus" class="form-select" name="status" aria-label="Filter by Approval Status">
                                        <option value="">All Statuses</option>
                                        @foreach (\App\Enums\ThirdPartyApprovalStatusEnum::cases() as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center gap-3 mt-4">
                                <button type="button" id="applyFiltersBtn" class="btn btn-primary">
                                    <i class="fas fa-check-circle me-2"></i> Apply Filters
                                </button>
                                <button type="button" id="resetFilterBtn" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-2"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="thirdPartiesTable" class="table table-hover w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Company Name</th>
                                    <th>Country</th>
                                    <th>Third Party Type</th>
                                    <th>Approval Status</th>
                                    <th>Business Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const filterQuery = $('#filterFormQ');
    const filterType = $('#filterType');
    const filterStatus = $('#filterStatus');
    const clearSearchBtn = $('#clearSearchBtn');
    const clearSearchSpan = $('#clearSearchSpan');
    const searchButton = $('#searchButton');
    const applyFiltersBtn = $('#applyFiltersBtn');
    const resetFilterBtn = $('#resetFilterBtn');
    const activeFilterCount = $('#activeFilterCount');
    let thirdPartiesTable = null;

    const debounce = (fn, delay) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn.apply(this, args), delay);
        };
    };

    const updateFilterCount = () => {
        let count = 0;
        if (filterQuery.val()) count++;
        if (filterType.val()) count++;
        if (filterStatus.val()) count++;
        activeFilterCount.text(count).toggle(count > 0);
    };

    const initializeDataTable = () => {
        if (thirdPartiesTable) thirdPartiesTable.destroy();

        thirdPartiesTable = $('#thirdPartiesTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            searching: false,
            dom: '<"top"l>rtip',
            ajax: {
                url: "{{ route('web.parties.index') }}",
                data: d => {
                    d.search.value = filterQuery.val();
                    d.type = filterType.val();
                    d.status = filterStatus.val();
                }
            },
            columns: [{
                    data: 'Id',
                    name: 'Id'
                },
                {
                    data: 'ThirdPartyName',
                    name: 'ThirdPartyName'
                },
                {
                    data: 'Country',
                    name: 'Country'
                },
                {
                    data: 'ThirdPartyType',
                    name: 'ThirdPartyType'
                },
                {
                    data: 'ApprovalStatus',
                    name: 'ApprovalStatus'
                },
                {
                    data: 'BusinessType',
                    name: 'BusinessType'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            language: {
                emptyTable: `
                    <div class='text-center py-5'>
                        <img class='img-fluid mb-3' style='max-height: 150px' src='{{ asset('assets/img/errors/404.svg') }}' alt='No data found'>
                        <p class='text-muted'>No third parties found matching your criteria.</p>
                    </div>
                `,
                processing: `
                    <div class='text-center py-3'>
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `,
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            initComplete: function() {
                $('.dataTables_length').appendTo('#dt-length-container');
                $('.dataTables_length select').addClass('form-select form-select-sm');
                updateFilterCount();
            }
        });
    };

    $(document).ready(function() {
        $.fn.dataTable.ext.errMode = 'none';
        initializeDataTable();

        const reloadTable = () => {
            thirdPartiesTable.ajax.reload(updateFilterCount);
        };

        const debouncedReloadTable = debounce(reloadTable, 300);

        filterQuery.on('input', function() {
            clearSearchSpan.toggle(!!$(this).val());
            debouncedReloadTable();
        });

        searchButton.on('click', reloadTable);

        applyFiltersBtn.on('click', function() {
            reloadTable();
            $('#advancedFilters').collapse('hide');
        });

        clearSearchBtn.on('click', function() {
            filterQuery.val('');
            clearSearchSpan.hide();
            reloadTable();
        });

        resetFilterBtn.on('click', function() {
            filterQuery.val('');
            filterType.val('');
            filterStatus.val('');
            clearSearchSpan.hide();
            $('#advancedFilters').collapse('hide');
            reloadTable();
        });

        filterQuery.on('keypress', function(e) {
            if (e.which === 13) {
                reloadTable();
            }
        });
    });
</script>
@endsection