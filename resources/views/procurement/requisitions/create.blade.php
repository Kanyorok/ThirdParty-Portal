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
                                            -{{ $procurementPlan->Title }}</option>
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
        const $Modal = $('#RequisitionItemModal');

        $(function() {
            // Initialize DataTable
            @if (!$details->isEmpty())
            $('#requisitionTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                order: [[3, 'desc']], 
                language: {
                    emptyTable: "No data available"
                }
            });
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
                
                // Disable button
                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Creating...'
                );
                
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
                            throw new Error(response.message || 'Failed to create requisition');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        let errorMessage = 'Failed to create requisition. Please try again.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                errorMessage = response.message || errorMessage;
                            } catch (e) {
                                console.error('Could not parse error response');
                            }
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
                        
                        // Re-enable button
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save"></i> Add Requisition'
                        );
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
                        console.error('Error fetching plan details:', error);
                        alert('Failed to fetch plan details');
                    });
            });
        });
    </script>
@endsection
