@extends('layouts.app')

@section('title', 'Initiated Tenders')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .table th,
    .table td {
        vertical-align: middle;
    }

    .badge {
        font-size: 0.85em;
        padding: 0.4em 0.7em;
    }

    .action-buttons .btn {
        margin-right: 0.3rem;
    }

    .action-buttons form {
        margin-bottom: 0;
    }

    /* CRITICAL: Force DataTables controls to always be visible */
    div.dataTables_wrapper {
        width: 100% !important;
        overflow: visible !important;
    }

    div.dataTables_wrapper div.dataTables_length,
    div.dataTables_wrapper div.dataTables_filter {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative !important;
        width: auto !important;
        height: auto !important;
        margin: 0 0 1rem 0 !important;
    }

    div.dataTables_wrapper div.dataTables_filter {
        text-align: right !important;
        float: none !important;
    }

    div.dataTables_wrapper div.dataTables_filter label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }

    div.dataTables_wrapper div.dataTables_filter input {
        display: inline-block !important;
        width: auto !important;
        min-width: 200px !important;
        margin-left: 0.5rem !important;
    }

    div.dataTables_wrapper div.dataTables_length label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }

    div.dataTables_wrapper div.dataTables_length select {
        display: inline-block !important;
        width: auto !important;
        margin: 0 0.5rem !important;
    }

    /* Fix for Bootstrap grid system conflicts */
    .dataTables_wrapper .row {
        display: flex !important;
        flex-wrap: wrap !important;
        margin-right: -0.75rem !important;
        margin-left: -0.75rem !important;
    }

    .dataTables_wrapper .col-sm-12,
    .dataTables_wrapper .col-md-6,
    .dataTables_wrapper .col-md-5,
    .dataTables_wrapper .col-md-7 {
        padding-right: 0.75rem !important;
        padding-left: 0.75rem !important;
        position: relative !important;
        width: 100% !important;
    }

    @media (min-width: 768px) {
        .dataTables_wrapper .col-md-6 {
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }

        .dataTables_wrapper .col-md-5 {
            flex: 0 0 41.666667% !important;
            max-width: 41.666667% !important;
        }

        .dataTables_wrapper .col-md-7 {
            flex: 0 0 58.333333% !important;
            max-width: 58.333333% !important;
        }
    }

    /* Responsive adjustments */
    @media (max-width: 767px) {
        div.dataTables_wrapper div.dataTables_filter {
            text-align: left !important;
            margin-top: 0.5rem !important;
        }

        div.dataTables_wrapper div.dataTables_filter input {
            min-width: 150px !important;
            width: 100% !important;
            max-width: 100% !important;
        }
    }

    /* Ensure table stays within card */
    .card-body {
        overflow-x: auto !important;
        overflow-y: visible !important;
    }

    /* Prevent sidebar transitions from affecting DataTables */
    .main-sidebar {
        transition: margin-left 0.3s ease-in-out, left 0.3s ease-in-out !important;
    }

    body:not(.sidebar-collapse) .content-wrapper,
    body:not(.sidebar-collapse) .main-header,
    body:not(.sidebar-collapse) .main-footer {
        transition: margin-left 0.3s ease-in-out !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
    @php
    $isPaginator = isset($tenders) && method_exists($tenders, 'links');
    if ($isPaginator) {
    $tendersSorted = $tenders;
    } else {
    $tendersCollection = isset($tenders) ? collect($tenders) : collect();
    if ($tendersCollection->isNotEmpty()) {
    $tendersSorted = $tendersCollection->sortByDesc(fn($t) => $t->Id ?? null)->values();
    } else {
    $tendersSorted = $tendersCollection;
    }
    }
    @endphp

    @php
    $tenderStatusMap = [];
    try {
    $rows = \Illuminate\Support\Facades\DB::table('t_CodeDetails')->where('CodeID', 'TenderStatus')->get();
    foreach ($rows as $r) {
    $tenderStatusMap[$r->Value] = $r->Description;
    }
    } catch (\Throwable $e) {
    // ignore and fallback to enum displayName
    }
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-primary"><i class="fas fa-list-alt me-2"></i>Initiated Tenders</h3>
        @canWrite('tender')
        <a href="{{ route('initiatetender.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> New Tender
        </a>
        @endcanWrite
    </div>

    <div class="alert alert-info" role="alert" style="background:#eef6ff;border:1px solid #cfe2ff;color:#084298;">
        <i class="fa fa-info-circle me-2"></i>
        <span title="Open: all suppliers can bid. Restricted: only invited based on selected item category. Use 'Add to Grid' to add items.'">
            <strong>Guidance:</strong> Tender Initiation supports two types: Open (all suppliers can bid) and Restricted (only invited suppliers based on the selected item category). Add items to the tender by clicking Add to Grid.
        </span>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="tendersTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tender No.</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Currency</th>
                            <th>Deadline</th>
                            <th>Opening Date</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th style="min-width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tendersSorted as $tender)
                        <tr>
                            <td>{{ $isPaginator ? ($tenders->firstItem() + $loop->index) : $loop->iteration }}</td>
                            <td>{{ $tender->TenderNo }}</td>
                            <td>
                                <a href="{{ route('initiatetender.show', $tender->Id) }}" title="View {{ $tender->Title }}">
                                    {{ Str::limit($tender->Title, 40) }}
                                </a>
                            </td>
                            <td>
                                @if($tender->TenderType)
                                <span class="badge
                                        @if($tender->TenderType === \App\Enums\TenderTypeEnum::Restricted) bg-warning text-dark
                                        @elseif($tender->TenderType === \App\Enums\TenderTypeEnum::Open) bg-success
                                        @else bg-info text-dark
                                        @endif">
                                    {{ $tender->TenderType?->name ?? 'Unknown' }}
                                </span>
                                @else
                                N/A
                                @endif
                            </td>
                            <td>
                                @if($tender->TenderCategory)
                                {{ $tender->tenderCategory?->TenderCategory }}
                                @else
                                N/A
                                @endif
                            </td>
                            <td>
                                {{ $tender->currency ? $tender->currency->Code : 'N/A' }}
                            </td>
                            <td>{{ $tender->SubmissionDeadline ? $tender->SubmissionDeadline->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $tender->OpeningDate ? $tender->OpeningDate->format('d/m/Y') : 'N/A' }}</td>
                            <td>
                                @if($tender->Status)
                                <span class="badge rounded-pill
                                    @switch($tender->Status)
                                        @case(\App\Enums\TenderStatusEnum::Draft) bg-secondary @break
                                        @case(\App\Enums\TenderStatusEnum::Published) bg-success @break
                                        @case(\App\Enums\TenderStatusEnum::Closed) bg-dark @break
                                        @default bg-light text-dark @break
                                    @endswitch">
                                    {{ $tender->Status->displayName() }}
                                </span>
                                @else
                                N/A
                                @endif
                            </td>
                            <td>
                                @if ($tender->ApprovalStatus == \App\Enums\TenderApprovalStatusEnum::APPROVED)
                                <span class="badge rounded-pill bg-success text-white">
                                    Approved
                                </span>
                                @elseif ($tender->ApprovalStatus == \App\Enums\TenderApprovalStatusEnum::REJECTED)
                                <span class="badge rounded-pill bg-danger text-white">
                                    Rejected
                                </span>
                                @elseif ($tender->ApprovalStatus == \App\Enums\TenderApprovalStatusEnum::PENDING)
                                <span class="badge rounded-pill bg-warning text-dark">
                                    Pending
                                </span>
                                @else
                                <span class="badge rounded-pill bg-secondary">
                                    N/A
                                </span>
                                @endif
                            </td>
                            <td class="action-buttons">
                                @php $hasActions = false; @endphp
                                @canRead('tender')
                                @php $hasActions = true; @endphp
                                <a href="{{ route('initiatetender.show', $tender->Id) }}"
                                    class="btn btn-sm btn-outline-info"
                                    title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcanRead

                                @if ($tender->ApprovalStatus == \App\Enums\TenderApprovalStatusEnum::APPROVED)
                                    {{-- APPROVED: View button only --}}

                                @elseif ($tender->ApprovalStatus == \App\Enums\TenderApprovalStatusEnum::PENDING)
                                    {{-- PENDING APPROVAL: View button only --}}

                                @elseif ($tender->ApprovalStatus == \App\Enums\TenderApprovalStatusEnum::REJECTED)
                                @if($tender->Status === \App\Enums\TenderStatusEnum::Draft)
                                @canUpdate('tender')
                                @php $hasActions = true; @endphp
                                <a href="{{ route('initiatetender.edit', $tender->Id) }}"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcanUpdate

                                @canUpdate('tender')
                                @php $hasActions = true; @endphp
                                <form action="{{ route('initiatetender.submit', $tender->Id) }}"
                                    method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('POST')
                                    <button type="submit"
                                        class="btn btn-sm btn-outline-success"
                                        title="Resubmit for Approval"
                                        onclick="return confirm('Are you sure you want to resubmit this tender for approval?')">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </form>
                                @endcanUpdate
                                @endif

                                @canDelete('tender')
                                @php $hasActions = true; @endphp
                                <form action="{{ route('initiatetender.destroy', $tender->Id) }}"
                                    method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Delete"
                                        onclick="return confirm('Are you sure you want to delete this tender? This action cannot be undone.')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcanDelete

                                @else
                                @if($tender->Status === \App\Enums\TenderStatusEnum::Draft)
                                @canUpdate('tender')
                                @php $hasActions = true; @endphp
                                <a href="{{ route('initiatetender.edit', $tender->Id) }}"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcanUpdate

                                @canUpdate('tender')
                                @php $hasActions = true; @endphp
                                <form action="{{ route('initiatetender.submit', $tender->Id) }}"
                                    method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('POST')
                                    <button type="submit"
                                        class="btn btn-sm btn-outline-success"
                                        title="Submit for Approval"
                                        onclick="return confirm('Are you sure you want to submit this tender for approval?')">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </form>
                                @endcanUpdate
                                @endif

                                @canDelete('tender')
                                @php $hasActions = true; @endphp
                                <form action="{{ route('initiatetender.destroy', $tender->Id) }}"
                                    method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Delete"
                                        onclick="return confirm('Are you sure you want to delete this tender? This action cannot be undone.')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcanDelete
                                @endif

                                @if (!$hasActions)
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="fas fa-folder-open fa-2x text-muted mb-2"></i><br>
                                No initiated tenders found.
                                @canWrite('tender')
                                <a href="{{ route('initiatetender.create') }}">Create a new one?</a>
                                @endcanWrite
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    // Wrap in IIFE to avoid conflicts with partial navigation
    (function() {
        'use strict';

        var tableInstance = null;
        var isInitialized = false;

        function initializeDataTable() {
            try {
                console.log('Initializing DataTable...');

                // Check if table exists
                if (!$('#tendersTable').length) {
                    console.warn('Table not found');
                    return;
                }

                // Check if DataTables library is loaded
                if (typeof $.fn.DataTable === 'undefined') {
                    console.error('DataTables library not loaded');
                    return;
                }

                // DEBUG: Count and validate columns
                var headerCells = $('#tendersTable thead tr th').length;
                console.log('Header columns: ' + headerCells);
                
                var hasErrors = false;
                var hasDataRows = false;
                var dataRowCount = 0;
                
                $('#tendersTable tbody tr').each(function(index) {
                    var cellCount = $(this).find('td').length;
                    var colspan = $(this).find('td[colspan]').attr('colspan');
                    
                    // Skip rows with colspan (like empty state)
                    if (colspan) {
                        console.log('Row ' + index + ' has colspan=' + colspan + ' (empty state row)');
                        return;
                    }
                    
                    // This is a data row
                    hasDataRows = true;
                    dataRowCount++;
                    
                    if (cellCount !== headerCells) {
                        console.error('Row ' + index + ' has ' + cellCount + ' cells (Expected ' + headerCells + ')');
                        console.log('Row HTML:', $(this).html());
                        hasErrors = true;
                    }
                });

                // Don't initialize DataTable if there are no data rows (only empty state)
                if (!hasDataRows) {
                    console.warn('No data rows found (only empty state). Skipping DataTable initialization.');
                    return;
                }

                console.log('Found ' + dataRowCount + ' data rows');

                if (hasErrors) {
                    console.error('Column count mismatch detected. Cannot initialize DataTable.');
                    return;
                }

                // Destroy existing instance if present
                if ($.fn.DataTable.isDataTable('#tendersTable')) {
                    try {
                        $('#tendersTable').DataTable().destroy();
                        console.log('Destroyed existing DataTable instance');
                    } catch (err) {
                        console.warn('Error destroying DataTable:', err);
                    }
                }

                // Initialize fresh DataTable
                tableInstance = $('#tendersTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                "responsive": false,
                "autoWidth": false,
                "processing": false,
                "stateSave": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "paging": true,
                "lengthChange": true,
                "dom": '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "language": {
                    "search": "Search:",
                    "searchPlaceholder": "Search tenders...",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "Showing 0 to 0 of 0 entries",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "zeroRecords": "No matching tenders found",
                    "emptyTable": "No tenders available",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                },
                "columns": [{
                        "orderable": true
                    }, // #
                    {
                        "orderable": true
                    }, // Tender No.
                    {
                        "orderable": true
                    }, // Title
                    {
                        "orderable": true
                    }, // Type
                    {
                        "orderable": true
                    }, // Category
                    {
                        "orderable": true
                    }, // Currency
                    {
                        "orderable": true
                    }, // Deadline
                    {
                        "orderable": true
                    }, // Opening Date
                    {
                        "orderable": true
                    }, // Status
                    {
                        "orderable": true
                    }, // Approval
                    {
                        "orderable": false,
                        "searchable": false
                    } // Actions
                ],
                "initComplete": function() {
                    console.log('DataTable initialized successfully');
                    enforceControlVisibility();
                    isInitialized = true;
                },
                "drawCallback": function() {
                    enforceControlVisibility();
                }
            });

            console.log('DataTable instance created');
            
            } catch (error) {
                console.error('Error initializing DataTable:', error);
                console.error('Error stack:', error.stack);
            }
        }

        function enforceControlVisibility() {
            var $wrapper = $('.dataTables_wrapper');
            if (!$wrapper.length) return;

            var $length = $wrapper.find('.dataTables_length');
            var $filter = $wrapper.find('.dataTables_filter');
            var $info = $wrapper.find('.dataTables_info');
            var $paginate = $wrapper.find('.dataTables_paginate');

            // Force visibility
            $length.css({
                'display': 'block',
                'visibility': 'visible',
                'opacity': '1'
            }).show();

            $filter.css({
                'display': 'block',
                'visibility': 'visible',
                'opacity': '1',
                'text-align': 'right'
            }).show();

            $info.css('display', 'block').show();
            $paginate.css('display', 'block').show();

            // Ensure inner controls are visible
            $filter.find('input').css({
                'display': 'inline-block',
                'visibility': 'visible'
            }).show();

            $length.find('select').css({
                'display': 'inline-block',
                'visibility': 'visible'
            }).show();
        }

        // Handle sidebar toggle
        function handleSidebarToggle() {
            if (!tableInstance) return;

            setTimeout(function() {
                tableInstance.columns.adjust();
                enforceControlVisibility();
            }, 350);
        }

        // Attach sidebar toggle handlers
        $(document).on('click', '#sidebar-hide, #mobile-collapse, .pc-sidebar-collapse, .pc-sidebar-popup', handleSidebarToggle);

        // Handle window resize with debounce
        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (tableInstance) {
                    tableInstance.columns.adjust();
                    enforceControlVisibility();
                }
            }, 250);
        });

        // Protection against other scripts hiding controls
        var protectionInterval = setInterval(function() {
            var $filter = $('.dataTables_filter');
            var $length = $('.dataTables_length');

            if ($filter.length && $filter.is(':hidden')) {
                console.warn('Filter hidden, restoring...');
                enforceControlVisibility();
            }

            if ($length.length && $length.is(':hidden')) {
                console.warn('Length hidden, restoring...');
                enforceControlVisibility();
            }
        }, 1000);

        // Initialize on document ready - DELEGATED to partial:loaded event to avoid double-init
        // partial-init.js guarantees a 'partial:loaded' event on page load.
        /*
        $(document).ready(function() {
            // initializeDataTable(); 
            // Initial enforcement
            setTimeout(enforceControlVisibility, 200);
        });
        */

        // Only enforce visibility on ready, let partial:loaded handle the table init
        $(document).ready(function() {
            setTimeout(enforceControlVisibility, 200);
        });

        // Re-initialize after partial content loads (AJAX navigation)
        document.addEventListener('partial:loaded', function(e) {
            console.log('Partial content loaded, reinitializing DataTable...');

            // Wait for DOM to settle
            setTimeout(function() {
                if ($('#tendersTable').length) {
                    initializeDataTable();
                }
            }, 100);
        });

        // Cleanup on page unload
        $(window).on('beforeunload', function() {
            clearInterval(protectionInterval);
            if (tableInstance) {
                tableInstance.destroy();
                tableInstance = null;
            }
        });

        // Override jQuery.hide() for DataTables controls
        var originalHide = $.fn.hide;
        $.fn.hide = function() {
            if (this.hasClass('dataTables_filter') ||
                this.hasClass('dataTables_length') ||
                this.hasClass('dataTables_info') ||
                this.hasClass('dataTables_paginate')) {
                console.warn('Blocked hide() on DataTables control');
                return this;
            }
            return originalHide.apply(this, arguments);
        };

    })();
</script>
@endpush