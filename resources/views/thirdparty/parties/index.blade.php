@extends('layouts.app')

@section('title', 'Registered Third Parties')

@section('styles')
    <style>
        :root {
            --primary-50: #eff6ff;
            --primary-100: #dbeafe;
            --primary-500: #3b82f6;
            --primary-600: #2563eb;
            --primary-700: #1d4ed8;
            --accent-50: #ecfdf5;
            --accent-500: #10b981;
            --accent-600: #059669;
            --red-50: #fef2f2;
            --red-100: #fee2e2;
            --red-500: #ef4444;
            --red-600: #dc2626;
            --red-700: #b91c1c;
            --amber-50: #fffbeb;
            --amber-100: #fef3c7;
            --amber-600: #d97706;
            --amber-700: #b45309;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --ring-primary: 0 0 0 3px rgb(59 130 246 / 0.1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: var(--gray-900);
            background-color: var(--gray-50);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container-custom {
            width: 100%;
            padding: 1rem;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
            letter-spacing: -0.025em;
        }

        .card {
            background-color: white;
            border: 1.5px solid var(--gray-200);
            border-radius: 0.75rem;
            overflow: hidden;
            transition: border-color 0.2s ease-in-out;
        }

        .card:hover {
            border-color: var(--gray-300);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
            border-bottom: 1.5px solid var(--gray-200);
            padding: 2rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-header h4 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header h4 i {
            color: var(--primary-600);
            font-size: 1.375rem;
        }

        .card-body {
            padding: 2.5rem;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 0.875rem 1.125rem;
            font-size: 0.9375rem;
            line-height: 1.375rem;
            color: var(--gray-900);
            background-color: white;
            border: 1.5px solid var(--gray-300);
            border-radius: 0.5rem;
            transition: all 0.15s ease-in-out;
            outline: none;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-500);
            box-shadow: var(--ring-primary);
            background-color: var(--primary-50);
        }

        .form-control:hover:not(:focus),
        .form-select:hover:not(:focus) {
            border-color: var(--gray-400);
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            display: block;
        }

        .input-group {
            display: flex;
            position: relative;
        }

        .input-group .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-right: none;
            flex: 1;
        }

        .input-group .form-control:focus {
            border-right: none;
        }

        .input-group-text {
            background-color: white;
            border: 1.5px solid var(--gray-300);
            border-left: none;
            border-radius: 0;
            padding: 0.875rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-500);
        }

        .input-group .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-left: none;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.25rem;
            border-radius: 0.5rem;
            border: 1.5px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            white-space: nowrap;
            outline: none;
        }

        .btn:focus {
            outline: 2px solid transparent;
            outline-offset: 2px;
        }

        .btn-primary {
            color: white;
            background-color: var(--primary-600);
            border-color: var(--primary-600);
        }

        .btn-primary:hover {
            background-color: var(--primary-700);
            border-color: var(--primary-700);
            transform: translateY(-1px);
        }

        .btn-primary:focus {
            box-shadow: var(--ring-primary);
        }

        .btn-outline-secondary {
            color: var(--gray-700);
            background-color: white;
            border-color: var(--gray-300);
        }

        .btn-outline-secondary:hover {
            background-color: var(--gray-50);
            border-color: var(--gray-400);
            color: var(--gray-800);
            transform: translateY(-1px);
        }

        .btn-outline-secondary:focus {
            box-shadow: var(--ring-primary);
        }

        .btn-outline-primary {
            color: var(--primary-600);
            background-color: white;
            border-color: var(--primary-300);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-50);
            border-color: var(--primary-500);
            color: var(--primary-700);
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
        }

        .btn-link {
            background: none;
            border: none;
            color: var(--gray-500);
            padding: 0;
            text-decoration: none;
        }

        .btn-link:hover {
            color: var(--gray-700);
        }

        .filter-section {
            background-color: var(--gray-50);
            border: 1.5px solid var(--gray-200);
            border-radius: 0.75rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .filter-collapse {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .controls-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .search-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-section .input-group {
            min-width: 320px;
        }

        .badge {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .badge-status-pending {
            background-color: var(--amber-100);
            color: var(--amber-700);
            border: 1px solid var(--amber-200);
        }

        .badge-status-approved {
            background-color: var(--accent-50);
            color: var(--accent-600);
            border: 1px solid var(--accent-200);
        }

        .badge-status-rejected {
            background-color: var(--red-100);
            color: var(--red-700);
            border: 1px solid var(--red-200);
        }

        .badge-status-active {
            background-color: var(--accent-50);
            color: var(--accent-600);
            border: 1px solid var(--accent-200);
        }

        .badge-status-inactive {
            background-color: var(--gray-100);
            color: var(--gray-600);
            border: 1px solid var(--gray-200);
        }

        .badge-prequalified-yes {
            background-color: var(--accent-500);
            color: white;
            border: 1px solid var(--accent-600);
        }

        .badge-prequalified-no {
            background-color: var(--red-100);
            color: var(--red-700);
            border: 1px solid var(--red-200);
        }

        .badge-info {
            background-color: var(--primary-100);
            color: var(--primary-700);
            border: 1px solid var(--primary-200);
        }

        .badge-primary {
            background-color: var(--primary-600);
            color: white;
            border: 1px solid var(--primary-700);
        }

        .table-container {
            background-color: white;
            border: 1.5px solid var(--gray-200);
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table.dataTable {
            border-collapse: collapse;
            width: 100% !important;
            margin: 0 !important;
        }

        table.dataTable thead th {
            background-color: var(--gray-50);
            border-bottom: 1.5px solid var(--gray-200);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            font-size: 0.8125rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            white-space: nowrap;
        }

        table.dataTable tbody td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            vertical-align: middle;
            font-size: 0.875rem;
            color: var(--gray-800);
            white-space: nowrap;
        }

        table.dataTable tbody tr:last-child td {
            border-bottom: none;
        }

        table.dataTable tbody tr:hover {
            background-color: var(--gray-50);
        }

        .dataTables_wrapper {
            font-size: 0.875rem;
            color: var(--gray-900);
        }

        .dataTables_wrapper .dataTables_length {
            margin: 0;
        }

        .dataTables_wrapper .dataTables_length select {
            min-width: 80px;
            font-size: 0.875rem;
            margin: 0 0.5rem;
        }

        .dataTables_wrapper .dataTables_info {
            color: var(--gray-600);
            font-size: 0.875rem;
            padding-top: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 0.25rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5rem 0.875rem;
            border-radius: 0.375rem;
            border: 1.5px solid var(--gray-200);
            color: var(--gray-600);
            background-color: white;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background-color: var(--primary-600);
            color: white;
            border-color: var(--primary-600);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
            background-color: var(--gray-50);
            border-color: var(--gray-300);
            color: var(--gray-800);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .dt-length-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .empty-state img {
            max-height: 120px;
            margin-bottom: 1rem;
            opacity: 0.7;
        }

        .loading-state {
            text-align: center;
            padding: 2rem;
        }

        .spinner-border {
            width: 2rem;
            height: 2rem;
            border: 0.25em solid var(--primary-200);
            border-right-color: var(--primary-600);
            border-radius: 50%;
            animation: spinner-border 0.75s linear infinite;
        }

        @keyframes spinner-border {
            to {
                transform: rotate(360deg);
            }
        }

        .visually-hidden {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .filter-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .container-custom {
                padding: 0.75rem;
            }

            .card-header {
                padding: 1.5rem;
                flex-direction: column;
                align-items: flex-start;
            }

            .card-body {
                padding: 1.5rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .controls-section {
                flex-direction: column;
                align-items: stretch;
            }

            .search-section {
                justify-content: stretch;
            }

            .search-section .input-group {
                min-width: auto;
                width: 100%;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                flex-direction: column;
            }

            .dataTables_wrapper .dataTables_paginate {
                justify-content: center;
                flex-wrap: wrap;
            }

            table.dataTable thead th,
            table.dataTable tbody td {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.25rem;
            }

            .card-header h4 {
                font-size: 1.25rem;
            }

            .btn {
                padding: 0.75rem 1rem;
                font-size: 0.8125rem;
            }

            table.dataTable thead th,
            table.dataTable tbody td {
                padding: 0.75rem;
                font-size: 0.8125rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-custom">
        <div class="page-header">
            <h1 class="page-title">Third Parties Management</h1>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>
                    <i class="fas fa-building"></i>
                    Third Parties Overview
                </h4>
                <a href="{{ route('thirdparty.parties.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Third Party
                </a>
            </div>

            <div class="card-body">
                <div class="controls-section">
                    <div id="dt-length-container" class="dt-length-container"></div>
                    <div class="search-section">
                        <div class="input-group">
                            <input type="search"
                                   class="form-control"
                                   id="filterFormQ"
                                   placeholder="Search third parties..."
                                   autocomplete="off"
                                   aria-label="Search third parties">
                            <span class="input-group-text" id="clearSearchSpan" style="display: none;">
                            <button type="button"
                                    id="clearSearchBtn"
                                    class="btn btn-link"
                                    aria-label="Clear search">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </span>
                            <button class="btn btn-primary"
                                    type="button"
                                    id="searchButton"
                                    aria-label="Perform search">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <button class="btn btn-outline-secondary"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#advancedFilters"
                                aria-expanded="false"
                                aria-controls="advancedFilters">
                            <i class="fas fa-filter"></i> Filters
                            <span id="activeFilterCount"
                                  class="badge badge-primary rounded-pill ms-2"
                                  style="display:none;"></span>
                        </button>
                    </div>
                </div>

                <div class="collapse filter-collapse" id="advancedFilters">
                    <div class="filter-section">
                        <div class="filter-grid">
                            <div>
                                <label for="filterType" class="form-label">Type</label>
                                <select id="filterType"
                                        class="form-select"
                                        name="type"
                                        aria-label="Filter by Type">
                                    <option value="">All Types</option>
                                    @foreach (\App\Enums\ThirdPartyTypeEnum::cases() as $type)
                                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="filterStatus" class="form-label">Approval Status</label>
                                <select id="filterStatus"
                                        class="form-select"
                                        name="status"
                                        aria-label="Filter by Approval Status">
                                    <option value="">All Statuses</option>
                                    @foreach (\App\Enums\ThirdPartyApprovalStatusEnum::cases() as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="filter-actions">
                            <button type="button" id="applyFiltersBtn" class="btn btn-primary">
                                <i class="fas fa-check-circle"></i> Apply Filters
                            </button>
                            <button type="button" id="resetFilterBtn" class="btn btn-outline-secondary">
                                <i class="fas fa-undo"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table id="thirdPartiesTable" class="table w-100">
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
                    url: "{{ route('thirdparty.parties.index') }}",
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
                        render: function (data, type, row) {
                            return `<span class="badge badge-info">${data}</span>`;
                        }
                    },
                    {
                        data: 'ApprovalStatus',
                        name: 'ApprovalStatus',
                        render: function (data, type, row) {
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
                                    badgeClass = 'badge-info';
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
                        render: function (data, type, row) {
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
                        render: function (data, type, row) {
                            const viewUrl = `{{ route('thirdparty.parties.show', ['party' => ':id']) }}`.replace(':id', row.Id);
                            return `<a href="${viewUrl}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View</a>`;
                        }
                    }
                ],
                language: {
                    emptyTable: `
                    <div class='empty-state'>
                        <img class='img-fluid' src='{{ asset('assets/img/errors/404.svg') }}' alt='No data found'>
                        <p>No third parties found matching your criteria.</p>
                    </div>
                `,
                    processing: `
                    <div class='loading-state'>
                        <div class="spinner-border" role="status">
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
                initComplete: function () {
                    $('.dataTables_length').appendTo('#dt-length-container');
                    $('.dataTables_length select').addClass('form-select');
                    updateFilterCount();
                }
            });
        };

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            initializeDataTable();

            const reloadTable = () => {
                thirdPartiesTable.ajax.reload(updateFilterCount);
            };

            const debouncedReloadTable = debounce(reloadTable, 300);

            filterQuery.on('input', function () {
                clearSearchSpan.toggle(!!$(this).val());
                debouncedReloadTable();
            });

            searchButton.on('click', reloadTable);

            applyFiltersBtn.on('click', function () {
                reloadTable();
                $('#advancedFilters').collapse('hide');
            });

            clearSearchBtn.on('click', function () {
                filterQuery.val('');
                clearSearchSpan.hide();
                reloadTable();
            });

            resetFilterBtn.on('click', function () {
                filterQuery.val('');
                filterType.val('');
                filterStatus.val('');
                clearSearchSpan.hide();
                $('#advancedFilters').collapse('hide');
                reloadTable();
            });

            filterQuery.on('keypress', function (e) {
                if (e.which === 13) {
                    reloadTable();
                }
            });
        });
    </script>
@endsection
