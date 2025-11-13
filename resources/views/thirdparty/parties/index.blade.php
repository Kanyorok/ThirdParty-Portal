@extends('layouts.app')

@section('title', 'Registered Third Parties')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
    body {
        background-color: #f8fafc;
        font-family: "Inter", sans-serif;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-weight: 700;
        font-size: 1.75rem;
        color: #1e293b;
    }

    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

    .card-header {
        background: linear-gradient(90deg, #2563eb 0%, #3b82f6 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 1rem 1rem 0 0;
    }

    .card-header h4 {
        font-size: 1.25rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-bar {
        background: #f1f5f9;
        padding: 1.25rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .filter-bar .input-group input {
        border-radius: 0.5rem;
    }

    .filter-bar .btn {
        border-radius: 0.5rem;
    }

    .table thead {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
    }

    .table th {
        font-size: 0.85rem;
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
    }

    .table td {
        vertical-align: middle;
        color: #1e293b;
        font-size: 0.9rem;
    }

    .badge {
        padding: 0.4em 0.65em;
        font-size: 0.75rem;
        border-radius: 0.4rem;
    }

    .badge-success {
        background-color: #dcfce7;
        color: #166534;
    }

    .badge-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .badge-warning {
        background-color: #fef9c3;
        color: #92400e;
    }

    .actions button,
    .actions a {
        margin: 0 0.15rem;
    }

    .actions i {
        font-size: 1rem;
    }

    .bulk-actions {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
        display: none;
    }

    .dataTables_wrapper .dataTables_paginate {
        margin-top: 1rem;
    }
</style>
@endsection

@section('content')
<div class="container py-4">

    <div class="page-header">
        <h1><i class="bi bi-building"></i> Registered Third Parties</h1>
        <a href="{{ route('thirdparty.parties.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Third Party
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h4><i class="bi bi-list-task"></i> Third Party Overview</h4>
        </div>

        <div class="card-body">

            <!-- Search + Filters -->
            <div class="filter-bar">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="search" id="filterFormQ" class="form-control" placeholder="Search by name or email...">
                            <button class="btn btn-outline-secondary" id="clearSearchBtn" type="button" style="display:none;">
                                <i class="bi bi-x-circle"></i>
                            </button>
                            <button class="btn btn-primary" id="searchButton" type="button">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <select id="filterType" class="form-select">
                            <option value="">All Types</option>
                            @foreach (\App\Enums\ThirdPartyTypeEnum::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select id="filterStatus" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach (\App\Enums\ThirdPartyApprovalStatusEnum::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 text-end">
                        <button id="applyFiltersBtn" class="btn btn-success w-100">
                            <i class="bi bi-funnel-fill"></i> Apply
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="bulk-actions d-flex justify-content-between align-items-center">
                <div>
                    <strong id="selectedCount">0</strong> selected
                </div>
                <div>
                    <button class="btn btn-sm btn-success bulk-action-btn" data-action="approve">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>
                    <button class="btn btn-sm btn-warning bulk-action-btn" data-action="activate">
                        <i class="bi bi-play-circle"></i> Activate
                    </button>
                    <button class="btn btn-sm btn-danger bulk-action-btn" data-action="reject">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="clearSelection">
                        <i class="bi bi-dash-circle"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="thirdPartiesTable" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Trading Name</th>
                            <th>Country</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Email</th>
                            <th>Prequalified</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function() {
    const bulkActionUrl = "{{ route('thirdparty.parties.bulk-action') }}"; // replace if route name differs
    const table = $('#thirdPartiesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('thirdparty.parties.index') }}",
            data: function(d) {
                d.search = $('#filterFormQ').val();
                d.type = $('#filterType').val();
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
            {
                data: null,
                render: (data, type, row) => `<input type="checkbox" class="item-checkbox" value="${row.Id}">`,
                orderable: false
            },
            { data: 'Id' },
            { data: 'ThirdPartyName' },
            { data: 'TradingName' },
            { data: 'CountryId', name: 'Country' },
            { data: 'ThirdPartyType' },
            { data: 'ApprovalStatus',
                render: data => {
                    const cls = data === 'Approved' ? 'badge-success' :
                                data === 'Pending' ? 'badge-warning' : 'badge-danger';
                    return `<span class="badge ${cls}">${data}</span>`;
                }
            },
            { data: 'PrimaryEmail' },
            { data: 'IsPrequalified',
                render: d => d === 'Yes'
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-secondary">No</span>'
            },
            {
                data: 'Id',
                render: id => `
                    <div class="actions text-center">
                        <a href="/thirdparty/parties/${id}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <a href="/thirdparty/parties/${id}/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form action="/thirdparty/parties/${id}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this record?')"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                `
            }
        ],
        drawCallback: function() {
            // Rebind checkbox events after each draw
            bindRowSelection();
            updateSelectAllState();
        }
    });

    // UI elements
    const $bulkBar = $('.bulk-actions');
    const $selectedCount = $('#selectedCount');
    const $selectAll = $('#selectAll');

    // store selected ids in a Set to keep selections across paging
    const selectedIds = new Set();

    // bind checkboxes inside table
    function bindRowSelection() {
        // row checkboxes
        $('#thirdPartiesTable .item-checkbox').off('change').on('change', function() {
            const id = $(this).val();
            if ($(this).is(':checked')) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }
            refreshSelectionUI();
        });
    }

    // update selectAll checkbox based on visible rows
    function updateSelectAllState() {
        const $visibleCheckboxes = $('#thirdPartiesTable .item-checkbox');
        if ($visibleCheckboxes.length === 0) {
            $selectAll.prop('checked', false).prop('indeterminate', false);
            return;
        }
        const totalVisible = $visibleCheckboxes.length;
        const checkedVisible = $visibleCheckboxes.filter(':checked').length;

        if (checkedVisible === 0) {
            $selectAll.prop('checked', false).prop('indeterminate', false);
        } else if (checkedVisible === totalVisible) {
            $selectAll.prop('checked', true).prop('indeterminate', false);
        } else {
            $selectAll.prop('checked', false).prop('indeterminate', true);
        }
    }

    // refresh bulk bar UI
    function refreshSelectionUI() {
        // Update row checkbox states to match selectedIds (useful after page switch)
        $('#thirdPartiesTable .item-checkbox').each(function() {
            const id = $(this).val();
            $(this).prop('checked', selectedIds.has(id));
        });

        const count = selectedIds.size;
        $selectedCount.text(count);
        if (count > 0) {
            $bulkBar.show();
        } else {
            $bulkBar.hide();
        }

        updateSelectAllState();
    }

    // select all visible
    $selectAll.on('change', function() {
        const checked = $(this).is(':checked');
        $('#thirdPartiesTable .item-checkbox').each(function() {
            $(this).prop('checked', checked).trigger('change');
        });
    });

    // Clear selection button
    $('#clearSelection').on('click', function(e) {
        e.preventDefault();
        selectedIds.clear();
        refreshSelectionUI();
    });

    // Bulk action handler
    $('.bulk-action-btn').on('click', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        if (!action) return;

        if (selectedIds.size === 0) {
            alert('No items selected.');
            return;
        }

        if (!confirm(`Are you sure you want to ${action} ${selectedIds.size} item(s)?`)) return;

        // Prepare payload
        const payload = {
            action: action,
            selectedItems: Array.from(selectedIds)
        };

        // Send AJAX POST (uses jQuery)
        $.ajax({
            url: bulkActionUrl,
            method: 'POST',
            data: payload,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res && res.success) {
                    // show success toast/alert (adjust to your toastr if available)
                    alert(res.message || 'Bulk action completed successfully.');
                    // clear selection and reload table
                    selectedIds.clear();
                    refreshSelectionUI();
                    table.ajax.reload(null, false); // keep current page
                } else {
                    alert(res.message || 'Bulk action finished with issues.');
                }
            },
            error: function(xhr) {
                let msg = 'Bulk action failed.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    msg = xhr.responseText;
                }
                alert(msg);
                console.error('Bulk action error', xhr);
            }
        });
    });

    // Make search and filters work (already there)
    $('#searchButton').on('click', () => table.ajax.reload());
    $('#applyFiltersBtn').on('click', () => table.ajax.reload());

    // Keep selection checkboxes synced when user pages / sorts (initial bind)
    bindRowSelection();
});
</script>

@endsection
