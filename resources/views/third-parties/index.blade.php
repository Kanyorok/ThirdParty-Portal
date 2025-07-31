@extends('layouts.app')

@section('title', 'Registered Third Parties')

@section('styles')
<style>
    /* Modern, Clean Palette */
    :root {
        --primary: #4F46E5;
        /* Indigo 600 */
        --primary-hover: #4338CA;
        /* Indigo 700 */
        --accent: #10B981;
        /* Emerald 500 */
        --text-dark: #1F2937;
        /* Gray 900 */
        --text-medium: #4B5563;
        /* Gray 700 */
        --text-light: #6B7280;
        /* Gray 500 */
        --bg-light: #F9FAFB;
        /* Gray 50 */
        --bg-card: #FFFFFF;
        --border-light: #E5E7EB;
        /* Gray 200 */
        --border-medium: #D1D5DB;
        /* Gray 300 */
        --success-bg: #D1FAE5;
        /* Green 100 */
        --success-text: #065F46;
        /* Green 800 */
        --warning-bg: #FEF3C7;
        /* Amber 100 */
        --warning-text: #92400E;
        /* Amber 800 */
        --danger-bg: #FEE2E2;
        /* Red 100 */
        --danger-text: #991B1B;
        /* Red 800 */
        --info-bg: #DBEAFE;
        /* Blue 100 */
        --info-text: #1E40AF;
        /* Blue 800 */
    }

    body {
        background-color: var(--bg-light);
        color: var(--text-dark);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .container-fluid-custom {
        width: 100%;
        padding-right: 2rem;
        padding-left: 2rem;
        margin-right: auto;
        margin-left: auto;
    }

    @media (min-width: 1600px) {
        .container-fluid-custom {
            max-width: 1500px;
        }
    }

    .card {
        border-radius: 1rem;
        /* Slightly less rounded for a crisp look */
        border: 1px solid var(--border-light);
        background-color: var(--bg-card);
        overflow: hidden;
        /* Ensures child elements respect border-radius */
    }

    .card-header {
        background-color: var(--bg-card);
        /* Keep header background consistent with card */
        border-bottom: 1px solid var(--border-light);
        padding: 1.5rem 2rem;
        /* Adjusted padding */
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-body {
        padding: 2rem;
        /* Adjusted padding */
    }

    .form-control,
    .form-select {
        border-radius: 0.5rem;
        border: 1px solid var(--border-medium);
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        color: var(--text-dark);
        background-color: white;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        /* Indigo shadow */
        outline: none;
    }

    .input-group {
        max-width: 380px;
        /* Slightly wider search input */
    }

    .input-group .form-control {
        border-right: none;
    }

    .input-group-text {
        background-color: var(--bg-card);
        border: 1px solid var(--border-medium);
        border-left: none;
        border-radius: 0 0.5rem 0.5rem 0;
        padding: 0.75rem 1rem;
    }

    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.25rem;
        /* Adjusted button padding */
        font-weight: 600;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        text-decoration: none;
        /* For anchor buttons */
    }

    .btn-primary {
        background-color: var(--primary);
        border: 1px solid var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
    }

    .btn-outline-secondary {
        border: 1px solid var(--border-medium);
        color: var(--text-medium);
        background-color: var(--bg-card);
    }

    .btn-outline-secondary:hover {
        background-color: var(--bg-light);
        color: var(--text-dark);
        border-color: var(--text-light);
    }

    .dataTables_wrapper {
        font-size: 0.9rem;
        color: var(--text-dark);
    }

    .dataTables_length select {
        min-width: 80px;
        font-size: 0.9rem;
        margin-left: 0.5rem;
        /* Space from "Show" text */
        margin-right: 0.5rem;
        /* Space from "entries" text */
    }

    .dataTables_paginate {
        padding-top: 1rem;
    }

    .dataTables_paginate .paginate_button {
        padding: 0.6rem 1rem;
        margin: 0 0.2rem;
        border-radius: 0.375rem;
        /* Slightly smaller radius for pagination buttons */
        border: 1px solid var(--border-light);
        color: var(--text-medium);
        background-color: var(--bg-card);
        transition: all 0.2s ease;
    }

    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .dataTables_paginate .paginate_button:hover:not(.current) {
        background-color: var(--bg-light);
        border-color: var(--border-medium);
        color: var(--text-dark);
    }

    table.dataTable {
        border-collapse: collapse;
        width: 100% !important;
        border-radius: 0.75rem;
        overflow: hidden;
    }

    table.dataTable thead th {
        background-color: var(--bg-light);
        border-bottom: 1px solid var(--border-light);
        padding: 1rem 1.5rem;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-medium);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: left;
    }

    table.dataTable tbody td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-light);
        vertical-align: middle;
        font-size: 0.9rem;
    }

    table.dataTable tbody tr:last-child td {
        border-bottom: none;
    }

    table.dataTable tbody tr:hover {
        background-color: rgba(79, 70, 229, 0.02);
        /* Very subtle hover for rows */
    }

    .filter-collapse {
        transition: all 0.3s ease-in-out;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.75em;
        /* Slightly wider padding */
        border-radius: 0.375rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .badge-status-pending {
        background-color: var(--warning-bg);
        color: var(--warning-text);
    }

    .badge-status-approved {
        background-color: var(--success-bg);
        color: var(--success-text);
    }

    .badge-status-rejected {
        background-color: var(--danger-bg);
        color: var(--danger-text);
    }

    .badge-status-active {
        background-color: var(--success-bg);
        color: var(--success-text);
    }

    .badge-status-inactive {
        background-color: var(--text-light);
        color: white;
    }

    /* Using text-light for inactive */
    .badge-prequalified-yes {
        background-color: var(--accent);
        color: white;
    }

    /* Using accent color */
    .badge-prequalified-no {
        background-color: var(--danger-bg);
        color: var(--danger-text);
    }

    .dt-buttons {
        margin-bottom: 1rem;
    }

    .dt-buttons .btn {
        margin-right: 0.5rem;
    }

    /* Ensure table-responsive takes full width and manages overflow */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 1rem;
        /* Add some padding for scrollbar */
    }

    table.dataTable th,
    table.dataTable td {
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
            padding: 1.25rem;
        }

        .input-group {
            max-width: 100%;
        }

        .card-body {
            padding: 1.25rem;
        }

        .dataTables_paginate {
            text-align: center;
        }

        .container-fluid-custom {
            padding-right: 1rem;
            padding-left: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid-custom py-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0 fw-bold" style="color: var(--text-dark);">Third Parties Overview</h4>
                    <a href="{{ route('web.parties.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Third Party
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div id="dt-length-container" class="d-flex align-items-center gap-2"></div>
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
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="false" aria-controls="advancedFilters">
                                <i class="fas fa-filter"></i> Filters
                                <span id="activeFilterCount" class="badge bg-primary rounded-pill ms-2" style="display:none;"></span>
                            </button>
                        </div>
                    </div>

                    <div class="collapse mb-4 filter-collapse" id="advancedFilters">
                        <div class="card card-body border-0">
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
                                    <i class="fas fa-check-circle"></i> Apply Filters
                                </button>
                                <button type="button" id="resetFilterBtn" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo"></i> Reset Filters
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
                                    <th>Prequalified</th>
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
                    name: 'ThirdPartyType',
                    render: function(data, type, row) {
                        return `<span class="badge bg-info">${data}</span>`;
                    }
                },
                {
                    data: 'ApprovalStatus',
                    name: 'ApprovalStatus',
                    render: function(data, type, row) {
                        let badgeClass = '';
                        switch (data.toLowerCase()) {
                            case 'pending':
                                badgeClass = 'badge-status-pending';
                                break;
                            case 'approved':
                                badgeClass = 'badge-status-approved';
                                break;
                            case 'rejected':
                                badgeClass = 'badge-status-rejected';
                                break;
                            default:
                                badgeClass = 'bg-secondary';
                        }
                        return `<span class="badge ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'BusinessType',
                    name: 'BusinessType'
                },
                {
                    data: 'IsPrequalified',
                    name: 'IsPrequalified',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            const badgeClass = data ? 'badge-prequalified-yes' : 'badge-prequalified-no';
                            const text = data ? 'Yes' : 'No';
                            return `<span class="badge ${badgeClass}">${text}</span>`;
                        }
                        return data;
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        const viewUrl = `{{ route('web.parties.show', ['party' => ':id']) }}`.replace(':id', row.Id);
                        return `<a href="${viewUrl}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View</a>`;
                    }
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