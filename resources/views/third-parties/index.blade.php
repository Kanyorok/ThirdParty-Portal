@extends('layouts.app')

@section('title', 'Third Parties')

@section('styles')
<style>
    .card {
        border-radius: 0.75rem;
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    }

    .card-header {
        background-color: white;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        padding: 1.5rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    .form-control,
    .form-select {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
    }

    .input-group-lg>.form-control,
    .input-group-lg>.btn {
        height: calc(3.5rem + 2px);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5em 0.8em;
        margin-left: 0.2em;
        border-radius: 0.3rem !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background-color: #007bff !important;
        color: white !important;
        border: 1px solid #007bff !important;
    }

    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    table.dataTable thead th {
        border-bottom: 2px solid #e9ecef;
        font-weight: 600;
        padding: 1rem 1rem;
    }

    table.dataTable tbody td {
        padding: 1rem 1rem;
    }

    table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.25rem;
    }

    .filter-label {
        font-weight: 500;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Third Parties Overview</h4>
                <a href="{{ route('web.parties.create') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="fas fa-plus me-2"></i> Add New Third Party
                </a>
            </div>
            <div class="card-body">
                <!-- Search Bar -->
                <div class="d-flex justify-content-center mb-4">
                    <div class="input-group input-group-lg shadow-sm w-100" style="max-width: 800px;">
                        <input type="search" class="form-control border-end-0 pe-0" id="filterFormQ" placeholder="Search by name, email, phone, Reg. No., Tax PIN" maxlength="50" autocomplete="off" aria-label="Search third parties">
                        <span class="input-group-text bg-white border-start-0 ps-0 pe-2" id="clearSearchSpan" style="display: none;">
                            <button type="button" id="clearSearchBtn" class="btn btn-link p-0 text-muted" aria-label="Clear search">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </span>
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="false" aria-controls="advancedFilters">
                            <i class="fas fa-filter me-2"></i> Filters
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="collapse mb-4" id="advancedFilters">
                    <div class="card card-body shadow-sm border">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="filterType" class="form-label filter-label">Filter by Type</label>
                                <select id="filterType" class="form-select" name="type">
                                    <option value="">All Types</option>
                                    @foreach (\App\Enums\ThirdPartyTypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="filterStatus" class="form-label filter-label">Filter by Approval Status</label>
                                <select id="filterStatus" class="form-select" name="status">
                                    <option value="">All Approval Statuses</option>
                                    @foreach (\App\Enums\ThirdPartyApprovalStatusEnum::cases() as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="button" id="resetFilterBtn" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i> Reset All Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table id="thirdPartiesTable" class="table table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Company Name</th>
                                <th>Country</th>
                                <th>Type</th>
                                <th>Approval</th>
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
@endsection

@section('scripts')
<script>
    const filterQuery = $('#filterFormQ');
    const filterType = $('#filterType');
    const filterStatus = $('#filterStatus');
    const clearSearchBtn = $('#clearSearchBtn');
    const clearSearchSpan = $('#clearSearchSpan');
    const resetFilterBtn = $('#resetFilterBtn');
    let thirdPartiesTable = null;

    const debounce = (fn, delay) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn.apply(this, args), delay);
        };
    };

    const initializeDataTable = () => {
        if (thirdPartiesTable) thirdPartiesTable.destroy();

        thirdPartiesTable = $('#thirdPartiesTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
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
                        <img class='img-fluid' style='height:20vh' src='{{ asset('assets/img/errors/404.svg') }}' alt='No data found'>
                        <p class='mt-3 text-muted'>No third parties found matching your criteria.</p>
                    </div>
                `,
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                search: "",
                searchPlaceholder: "Search...",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            initComplete: () => {
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_length select').addClass('form-select form-select-sm');
            }
        });
    };

    $(function() {
        $.fn.dataTable.ext.errMode = 'none';
        initializeDataTable();

        const reloadTable = debounce(() => {
            thirdPartiesTable.ajax.reload();
        }, 300);

        filterQuery.on('input', function() {
            clearSearchSpan.toggle(!!$(this).val());
            reloadTable();
        });

        filterType.on('change', reloadTable);
        filterStatus.on('change', reloadTable);

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
    });
</script>
@endsection