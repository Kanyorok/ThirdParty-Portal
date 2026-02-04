@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Requisitions')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
    .select2-container {
        width: 100% !important;
    }
    
    /* CRITICAL: Force DataTables controls to always be visible (copied from Tender Initiation) */
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
    
    /* Responsive adjustments */
    @media (max-width: 767px) {
        div.dataTables_wrapper div.dataTables_filter {
            text-align: left !important;
            margin-top: 0.5rem !important;
        }
    }

   /* Fix for Bootstrap grid system conflicts - CRITICAL for header layout */
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
                                    <a href="{{ route('requisition.show', [$item->Id]) }}" class="btn btn-info btn-sm">View</a>
                                    <a href="{{ route('requisition.approval', [$item->Id]) }}" class="btn btn-success btn-sm">Approve</a>
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
                        <div class="mb-3">
                            <label class="form-label" for="ProcurementPlan">Procurement Plan</label>
                            <select class="form-control" name="ProcurementPlan" id="ProcurementPlan">
                                <option selected value="">Select Procurement Plan</option>
                                @foreach ($procurementPlans as $procurementPlan)
                                <option value="{{ $procurementPlan->PlanID }}">
                                    {{ $procurementPlan->ReferenceNumber }} - {{ $procurementPlan->Title }}
                                </option>
                                @endforeach
                            </select>
                            <p id="ProcurementPlan_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="Branch">Branch <span class="text-danger">*</span></label>
                            <select class="form-control" name="Branch" id="Branch" required>
                                @if (isset($branchId))
                                <option value="{{ $branchId }}" selected>{{ session('LoginBranchName') }}</option>
                                @else
                                <option selected disabled>Select Branch</option>
                                @endif
                            </select>
                            <p id="Branch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="Department">Department <span class="text-danger">*</span></label>
                            <select class="form-control" name="Department" id="Department" required>
                                @if (isset($departmentId))
                                <option value="{{ $departmentId }}" selected>Department #{{ $departmentName ?? $departmentId }}</option>
                                @else
                                <option selected disabled>Select Department</option>
                                @endif
                            </select>
                            <p id="Department_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="Remarks">Remarks <span class="text-danger">*</span></label>
                            <textarea name="Remarks" id="Remarks" rows="3" class="form-control" maxlength="1000" required></textarea>
                            <p id="Remarks_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>

                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start" data-bs-dismiss="modal">Cancel</button>
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

<script>
    $(function() {
        const $Modal = $('#RequisitionItemModal');

        $(function() {
            // Initialize DataTable
            @if (count($details) > 0)
            var table = $('#requisitionTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                order: [[3, 'desc']], 
                responsive: false, // CRITICAL: Disable responsive to prevent auto-hiding
                language: {
                    emptyTable: "No data available"
                },
                initComplete: function() {
                    enforceControlVisibility();
                },
                drawCallback: function() {
                    enforceControlVisibility();
                }
            });

            // Prevent controls from disappearing
            function enforceControlVisibility() {
                var $wrapper = $('.dataTables_wrapper');
                if (!$wrapper.length) return;

                $wrapper.find('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate').css({
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': '1'
                }).show();
                
                $wrapper.find('.dataTables_filter').css('text-align', 'right');
            }

            // Handle sidebar and resize events
            $(window).on('resize', function() {
                setTimeout(handleResize, 200);
            });
            
            // Aggressive layout fixer for sidebar toggle
            $(document).on('click', '#sidebar-hide, #mobile-collapse, .pc-sidebar-collapse, .pc-sidebar-popup', function() {
                var checkCount = 0;
                var layoutInterval = setInterval(function() {
                    handleResize();
                    checkCount++;
                    if (checkCount > 20) { // Run for ~1 second (50ms * 20)
                        clearInterval(layoutInterval);
                    }
                }, 50);
            });
            
            function handleResize() {
                if (table) {
                    try {
                        table.columns.adjust();
                    } catch(e) {}
                }
                enforceControlVisibility();
            }

            // Periodic check
            setInterval(enforceControlVisibility, 2000);
            @endif

            // Initialize Select2
            $('#ProcurementPlan, #Branch, #Department').select2({
                dropdownParent: $Modal,
                width: '100%'
            });

            // Show modal
            $(document).on('click', '.modal-create-item', function () {
                $(".modal-title").html('Add Requisition');
                $(".modal-item").addClass('d-none');
                $('#createRequisition').removeClass('d-none');
                $Modal.modal('show');
            });

            // Handle form submission - FIXED VERSION
           $('form#createRequisitionForm').on('submit', function (e) {
                e.preventDefault();
                
                const form = $(this);
                const submitBtn = $('#createRequisitionBtn');
                
                // Create FormData
                const formData = new FormData(this);
                
                // Make AJAX request
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    // 1. CLEAR ERRORS BEFORE SENDING
                    beforeSend: function() {
                        // Disable button
                        submitBtn.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>Creating...'
                        );
                        // Clear previous errors
                        $('.invalid-feedback').addClass('d-none').text('');
                        $('.form-control').removeClass('is-invalid');
                    },
                    // 2. HANDLE SUCCESS
                    success: function(response) {
                        if (response.success && response.requisition_id) {
                            $Modal.modal('hide');
                            
                            // Show success message
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message || 'Requisition created successfully',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = response.route;
                                });
                            } else {
                                alert(response.message || 'Requisition created successfully');
                                window.location.href = response.route;
                            }
                        } else {
                            // Manually trigger error if success is false
                            // This goes to the error block below or handles it here
                            submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Add Requisition');
                            alert(response.message || 'Failed to create requisition');
                        }
                    },
                    // 3. HANDLE ERRORS
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        
                        // Re-enable button
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Add Requisition');

                        // HANDLE VALIDATION ERRORS (Status 422)
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                // key = Field name (e.g., ProcurementPlan), value = Array of errors
                                let errorId = '#' + key + '_error';
                                let inputId = '#' + key;
                                
                                $(inputId).addClass('is-invalid'); // Highlight input red
                                $(errorId).removeClass('d-none').text(value[0]); // Show error message
                            });
                            
                            // Stop here so we don't show the generic popup
                            return; 
                        }

                        // HANDLE GENERAL SERVER ERRORS
                        let errorMessage = 'Failed to create requisition. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage
                            });
                        } else {
                            alert(errorMessage);
                        }
                    }
                });

                
            });

            // Fetch Branch and Department based on Procurement Plan
            $('#ProcurementPlan').on('change', function () {
                let planId = $(this).val();
                if (!planId) return;

                fetch("{{ route('procurement.plan.details', '__ID__') }}".replace('__ID__', planId))
                    .then(response => response.json())
                    .then(data => {
                        const branchSelect = $('#Branch');
                        const departmentSelect = $('#Department');

                        branchSelect.empty().append('<option selected disabled>Select Branch</option>');
                        departmentSelect.empty().append('<option selected disabled>Select Department</option>');

                        data.branches.forEach(branch => {
                            branchSelect.append($('<option>', {
                                value: branch.Id,
                                text: branch.Name
                            }));
                        });

                        data.departments.forEach(dept => {
                            departmentSelect.append($('<option>', {
                                value: dept.Id,
                                text: dept.Name
                            }));
                        });
                    })
                    .catch(error => {
                        // Log error silently without showing to user
                        console.error('Error fetching plan details:', error);
                    });
            });
        });

        $('#ProcurementPlan').on('change', function() {
            let planId = $(this).val();
            if (!planId) return;

            let url = "{{ route('procurement.plan.details', ':id') }}".replace(':id', planId);
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const branchSelect = $('#Branch');
                    const departmentSelect = $('#Department');

                    branchSelect.empty().append('<option selected disabled>Select Branch</option>');
                    departmentSelect.empty().append('<option selected disabled>Select Department</option>');

                    data.branches.forEach(branch => {
                        branchSelect.append(new Option(branch.Name, branch.Id));
                    });

                    data.departments.forEach(dept => {
                        departmentSelect.append(new Option(dept.Name, dept.Id));
                    });
                });
        });
    });
</script>
@endsection