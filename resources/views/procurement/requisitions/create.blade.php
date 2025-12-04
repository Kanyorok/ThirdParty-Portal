@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Requisitions')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .select2-container {
        width: 100% !important;
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
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-12 text-end">
        @can('create', \App\Models\Procurement\Requisitions::class)
        <button class="btn btn-primary modal-create-item" type="button">
            <i class="fas fa-plus-circle"></i> New Requisition
        </button>
        @endcan
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="requisitionTable" class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Requisition No</th>
                                <th>Procurement Plan</th>
                                <th>Requisition Date</th>
                                <th>Branch</th>
                                <th>Department</th>
                                <th>Remarks</th>
                                <th>Total Items</th>
                                <th>Total Cost</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($details as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->RequisitionNo }}</td>
                                <td>{{ $item->PlanTitle ?? 'N/A' }}</td>
                                <td data-order="{{ $item->CreatedOn ? Carbon::parse($item->CreatedOn)->format('Y-m-d H:i:s') : '' }}">
                                    {{ $item->CreatedOn ? Carbon::parse($item->CreatedOn)->format('d M Y') : '' }}
                                </td>
                                <td>{{ $item->BranchID }}</td>
                                <td>{{ $item->DepartmentID }}</td>
                                <td>{{ $item->Remarks }}</td>
                                <td>{{ $item->itemcount }}</td>
                                <td data-order="{{ (float)($item->ExpectedPrice ?? 0) }}">{{ number_format((float)($item->ExpectedPrice ?? 0), 2) }}</td>
                                <td>{{ $item->Status }}</td>
                                <td>
                                    <a href="{{ route('requisition.show', [$item->Id]) }}"
                                        class="btn btn-info btn-sm">View</a>
                                    <a href="{{ route('requisition.approval', [$item->Id]) }}"
                                        class="btn btn-success btn-sm">Approve</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No requisition items found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="RequisitionItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="createRequisition">
                    @can('create', \App\Models\Procurement\Requisitions::class)
                    <form action="{{ route('requisition.store') }}" method="post" id="createRequisitionForm">
                        @csrf

                        <!-- Procurement Plan -->
                        <div class="mb-3">
                            <label class="form-label" for="ProcurementPlan">Procurement Plan</label>
                            <select class="form-control" name="ProcurementPlan" id="ProcurementPlan">
                                <option selected value="">Select Procurement Plan</option>
                                @foreach ($procurementPlans as $procurementPlan)
                                <option value="{{ $procurementPlan->PlanID }}">
                                    {{ $procurementPlan->ReferenceNumber }}
                                    -{{ $procurementPlan->Title }}
                                </option>
                                @endforeach
                            </select>
                            <p id="ProcurementPlan_error" class="invalid-feedback d-none error col-12"
                                role="alert"></p>
                        </div>

                        <!-- Branch -->
                        <div class="mb-3">
                            <label class="form-label" for="Branch">Branch <span class="text-danger">*</span></label>
                            <select class="form-control" name="Branch" id="Branch" required>
                                @if (isset($branchId))
                                <option value="{{ $branchId }}"
                                    selected>{{ session('LoginBranchName') }}</option>
                                @else
                                <option selected disabled>Select Branch</option>
                                @endif
                            </select>
                            <p id="Branch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>

                        <!-- Department -->
                        <div class="mb-3">
                            <label class="form-label" for="Department">Department <span class="text-danger">*</span></label>
                            <select class="form-control" name="Department" id="Department" required>

                                @if (isset($departmentId))
                                <option value="{{ $departmentId }}" selected>Department
                                    #{{ $departmentName ?? 'Department #' . $departmentId }}</option>
                                @else
                                <option selected disabled>Select Department</option>
                                @endif
                            </select>
                            <p id="Department_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>

                        <!-- Remarks -->
                        <div class="mb-3">
                            <label class="form-label" for="Remarks">Remarks <span
                                    class="text-danger">*</span></label>
                            <textarea name="Remarks" id="Remarks" rows="3" class="form-control" maxlength="1000"
                                required></textarea>
                            <p id="Remarks_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>

                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button class="btn btn-primary float-end" id="createRequisitionBtn" type="submit">
                                <i class="fas fa-save"></i> Add Requisition
                            </button>
                        </div>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/datatables.js') }}"></script>

<script>
        // Wrap in IIFE to avoid conflicts with partial navigation
        (function() {
            'use strict';
            
        const $Modal = $('#RequisitionItemModal');
            var tableInstance = null;
            var isInitialized = false;
            
            function initializeDataTable() {
                console.log('[Requisitions] Initializing DataTable...');
                
                // Check if table exists
                if (!$('#requisitionTable').length) {
                    console.warn('[Requisitions] Table not found');
                    return;
                }
                
                // Destroy existing instance if present
                if ($.fn.DataTable.isDataTable('#requisitionTable')) {
                    $('#requisitionTable').DataTable().destroy();
                    console.log('[Requisitions] Destroyed existing DataTable instance');
                }
                
                @if(count($details) > 0)
                // Initialize fresh DataTable
                tableInstance = $('#requisitionTable').DataTable({
                    "pageLength": 10,
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "responsive": false,
                    "autoWidth": false,
                    "processing": false,
                    "stateSave": false,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "paging": true,
                    "lengthChange": true,
                    "order": [
                [3, 'desc']
            ], // Requisition Date column
                    "dom": '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                           '<"row"<"col-sm-12"tr>>' +
                           '<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    "language": {
                        "search": "Search:",
                        "searchPlaceholder": "Search requisitions...",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "infoEmpty": "Showing 0 to 0 of 0 entries",
                        "infoFiltered": "(filtered from _MAX_ total entries)",
                        "zeroRecords": "No matching requisitions found",
                        "emptyTable": "No requisitions available",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        }
                    },
                    "initComplete": function() {
                        console.log('[Requisitions] DataTable initialized successfully');
                        enforceControlVisibility();
                        isInitialized = true;
                    },
                    "drawCallback": function() {
                        enforceControlVisibility();
                    }
                });
                
                console.log('[Requisitions] DataTable instance created');
                @endif
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
                    console.warn('[Requisitions] Filter hidden, restoring...');
                    enforceControlVisibility();
                }
                
                if ($length.length && $length.is(':hidden')) {
                    console.warn('[Requisitions] Length hidden, restoring...');
                    enforceControlVisibility();
                }
            }, 1000);

            // Initialize on document ready
            $(document).ready(function() {
                initializeDataTable();
                
                // Initial enforcement
                setTimeout(enforceControlVisibility, 200);
                
                // Modal handlers
                $(document).on('click', '.modal-create-item', function() {
                    $(".modal-title").html('Add Requisition');
                    $(".modal-item").addClass('d-none');
                    $('#createRequisition').removeClass('d-none');
                    $Modal.modal('show');
                });

                $('form#createRequisitionForm').submit(async function(e) {
                    e.preventDefault();
                    if (await saveForm($(this), $('#createRequisitionBtn'), true, true, true)) {
                        $Modal.modal('hide');
                    }
                });

                // Fetch Branch and Department based on Procurement Plan
                document.getElementById('ProcurementPlan').addEventListener('change', function() {
                    let planId = this.value;
                    if (!planId) return;

                    fetch("{{ route('procurement.plan.details', '__ID__') }}".replace('__ID__', planId))
                        .then(response => response.json())
                        .then(data => {
                            const branchSelect = document.getElementById('Branch');
                            const departmentSelect = document.getElementById('Department');

                            branchSelect.innerHTML = '<option selected disabled>Select Branch</option>';
                            departmentSelect.innerHTML = '<option selected disabled>Select Department</option>';

                            data.branches.forEach(branch => {
                                const opt = document.createElement('option');
                                opt.value = branch.Id;
                                opt.textContent = branch.Name;
                                branchSelect.appendChild(opt);
                            });

                            data.departments.forEach(dept => {
                                const opt = document.createElement('option');
                                opt.value = dept.Id;
                                opt.textContent = dept.Name;
                                departmentSelect.appendChild(opt);
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching plan details:', error);
                        });
                });
            });

            // Re-initialize after partial content loads (AJAX navigation)
            document.addEventListener('partial:loaded', function(e) {
                console.log('[Requisitions] Partial content loaded, reinitializing DataTable...');
                
                // Wait for DOM to settle
                setTimeout(function() {
                    if ($('#requisitionTable').length) {
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
                    console.warn('[Requisitions] Blocked hide() on DataTables control');
                    return this;
                }
                return originalHide.apply(this, arguments);
            };

            console.log('[Requisitions] Page script initialized');
        })();
</script>
@endsection