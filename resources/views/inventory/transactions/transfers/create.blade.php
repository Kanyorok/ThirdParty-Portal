@php use App\Models\Core\Branch; @endphp
@extends('layouts.app')

@section('title', 'Create Transfer')

@section('content')
    <div class="container bg-white shadow rounded p-4">
        <h4 class="mb-4">Create Transaction Transfer</h4>

        {{-- Step 1: Select requisition type & number --}}
        <form class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="requisition_type" class="form-label">Requisition Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="requisition_type" required>
                        <option value="">Select Requisition Type</option>
                        <option value="interbranch">InterBranch Requisition</option>
                        <option value="procurement">Procurement Plan Requisition</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="requisition_id" class="form-label">Requisition Number <span class="text-danger">*</span></label>
                    <select class="form-select" id="requisition_id" required>
                        <option value="">Select Requisition</option>
                    </select>
                </div>
            </div>
        </form>

        {{-- Step 2: Transfer form --}}
        <form method="POST" action="{{ route('transactionstransfers.store') }}" id="transferForm">
            @csrf
            <div id="transferDetails" style="display: none">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="transferDate" class="form-label">Transfer Date <span
                                class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control"
                               id="transferDate"
                               name="TransferDate"
                               required
                               value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                               min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="fromBranch" class="form-label">From Branch</label>
                        <input type="text" class="form-control" id="fromBranch" readonly>
                        <input type="hidden" name="FromBranch" id="FromBranch">
                    </div>

                    <div class="col-md-3">
                        <label for="toBranch" class="form-label">To Branch <span class="text-danger">*</span></label>

                        {{-- For Interbranch (hidden field) --}}
                        <input type="hidden" id="toBranchHidden" name="ToBranch">
                        <input type="text" class="form-control" id="toBranchText" readonly style="display: none;">

                        {{-- For Procurement (dropdown) --}}
                        <select class="form-select" id="toBranchSelect" style="display: none;">
                            <option value="">-- Select Branch --</option>
                            @foreach (Branch::all() as $branch)
                                <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="TransferredBy" class="form-label">Transferred By <span class="text-danger">*</span></label>
                        <select name="TransferredBy" class="form-select" id="TransferredBy" required>
                            <option value="">-- Select User --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->Id }}">{{ $user->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="RequisitionId" id="RequisitionId">
                    <input type="hidden" name="RequisitionType" id="RequisitionType">
                </div>

                {{-- Items Section --}}
                <div id="itemsSection">
                    <div class="mb-3">
                        <h5>Requisition Items</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Item Name</th>
                                    <th>Item Code</th>
                                    <th>Unit Cost</th>
                                    <th>UOM</th>
                                    <th>Approved Qty</th>
                                    <th>Dispatched Qty <span class="text-danger">*</span></th>
                                    <th>Remarks</th>
                                </tr>
                                </thead>
                                <tbody id="itemsBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Error Alert --}}
                <div id="ajax-error" class="alert alert-danger d-none"></div>

                {{-- Action Buttons --}}
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success" id="submitBtn"
                            onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin me-1\'></i> Submitting...'; this.disabled=true; document.getElementById('transferForm').requestSubmit();">
                        <i class="fas fa-save me-1"></i> Submit Transfer
                    </button>
                    <a href="{{ route('transactionstransfers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const requisitionTypeSelect = document.getElementById('requisition_type');
        const requisitionIdSelect = document.getElementById('requisition_id');
        const transferDetails = document.getElementById('transferDetails');
        const itemsBody = document.getElementById('itemsBody');
            const transferForm = document.getElementById('transferForm');

        const fromBranchText = document.getElementById('fromBranch');
        const fromBranchHidden = document.getElementById('FromBranch');

            const toBranchText = document.getElementById('toBranchText');
            const toBranchSelect = document.getElementById('toBranchSelect');
            const toBranchHidden = document.getElementById('toBranchHidden');

        const requisitionTypeHidden = document.getElementById('RequisitionType');
        const requisitionIdHidden = document.getElementById('RequisitionId');

        let selectedType = '';

        const requisitionsBaseUrl = "{{ url(route('requisitions.by-type', ['type' => 'PLACEHOLDER'])) }}";
        const requisitionDetailsBaseUrl = "{{ url(route('requisitions.details', ['id' => 'PLACEHOLDER'])) }}";

            // Load requisitions when type changes
        requisitionTypeSelect.addEventListener('change', function () {
            selectedType = this.value;
            requisitionTypeHidden.value = selectedType;
            requisitionIdSelect.innerHTML = '<option value="">Loading...</option>';

            // Reset form
            requisitionIdHidden.value = '';
            itemsBody.innerHTML = '';
            fromBranchText.value = '';
            fromBranchHidden.value = '';
            toBranchText.value = '';
            toBranchSelect.value = '';
            toBranchHidden.value = '';
            transferDetails.style.display = 'none';

            // Hide all ToBranch inputs initially
            toBranchText.style.display = 'none';
            toBranchSelect.style.display = 'none';

            if (!selectedType) {
                requisitionIdSelect.innerHTML = '<option value="">Select Requisition</option>';
                return;
            }

            const url = requisitionsBaseUrl.replace('PLACEHOLDER', selectedType);

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    requisitionIdSelect.innerHTML = '<option value="">Select Requisition</option>';
                    if (data.length === 0) {
                        requisitionIdSelect.innerHTML = '<option value="">No requisitions found</option>';
                        return;
                    }

                    data.forEach(req => {
                        const text = selectedType === 'interbranch' ? req.ReqNo : req.GRNID;
                        const value = selectedType === 'interbranch' ? req.Id : req.id;

                        requisitionIdSelect.innerHTML += `<option value="${value}">${text}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error fetching requisitions:', error);
                    requisitionIdSelect.innerHTML = '<option value="">Failed to load requisitions</option>';
                });
        });

            // Load requisition details when number changes
        requisitionIdSelect.addEventListener('change', function () {
            const id = this.value;

            // Reset details
            itemsBody.innerHTML = '';
            fromBranchText.value = '';
            fromBranchHidden.value = '';
            toBranchText.value = '';
            toBranchSelect.value = '';
            toBranchHidden.value = '';
            transferDetails.style.display = 'none';

            // Hide all ToBranch inputs initially
            toBranchText.style.display = 'none';
            toBranchSelect.style.display = 'none';

            if (!id || !selectedType) {
                requisitionIdHidden.value = '';
                return;
            }

            requisitionIdHidden.value = id;

            const detailsUrl = requisitionDetailsBaseUrl.replace('PLACEHOLDER', id) + `?type=${selectedType}`;

            fetch(detailsUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to load requisition details');
                    }
                    return response.json();
                })
                .then(data => {
                    if (selectedType === 'procurement') {
                        // Procurement: From = HQ, To = dropdown
                        fromBranchText.value = 'Headquarters';
                        fromBranchHidden.value = '{{ Branch::where("IsHQ", 1)->value("Id") ?? "" }}';

                        // Show dropdown for ToBranch and hide others
                        toBranchSelect.style.display = 'block';
                        toBranchText.style.display = 'none';

                        // Pre-select if data available
                        if (data.to_branch && data.to_branch.Id) {
                            toBranchSelect.value = data.to_branch.Id;
                        }

                    } else {
                        // Interbranch: From + To from requisition
                        fromBranchText.value = data.from_branch?.Name || 'N/A';
                        fromBranchHidden.value = data.from_branch?.Id || '';

                        toBranchText.value = data.to_branch?.Name || 'N/A';

                        // For interbranch, use hidden field for ToBranch value
                        toBranchHidden.value = data.to_branch?.Id || '';

                        // Show text input for display and hide dropdown
                        toBranchText.style.display = 'block';
                        toBranchSelect.style.display = 'none';
                    }

                    // Populate items table
                    itemsBody.innerHTML = '';
                    if (data.items && data.items.length > 0) {
                        data.items.forEach((item, index) => {
                            const dispatchedQty = item.DispatchedQty ?? item.ApprovedQty;
                            itemsBody.innerHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>
                                    ${item.ItemName || 'N/A'}
                                    <input type="hidden" name="items[${index}][item]" value="${item.Item}">
                                </td>
                                <td>${item.ItemCode || 'N/A'}</td>
                                <td>
                                    <input type="number" class="form-control" name="items[${index}][unit_cost]" value="${item.UnitCost || 0}" step="0.01" min="0" readonly>
                                    <input type="hidden" name="items[${index}][unit_id]" value="${item.PriceID || ''}">
                                </td>
                                <td>
                                    <input type="text" class="form-control" value="${item.UOMCode || 'N/A'}" readonly>
                                    <input type="hidden" name="items[${index}][uom]" value="${item.UOM || ''}">
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="items[${index}][approved_qty]" value="${item.ApprovedQty || 0}" min="0" readonly>
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="items[${index}][dispatched_qty]"
                                        value="${dispatchedQty || 0}"
                                        min="0"
                                        max="${item.ApprovedQty || 0}"
                                        required
                                        oninput="validateQuantity(this, ${item.ApprovedQty || 0})">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="items[${index}][remarks]" maxlength="255" placeholder="Optional remarks">
                                </td>
                            </tr>
                            `;
                        });
                    } else {
                        itemsBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No items found for this requisition</td></tr>';
                    }

                    transferDetails.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading requisition details:', error);
                    showError('Failed to load requisition details: ' + error.message);
                    requisitionIdHidden.value = '';
                });
        });

            // Form submission handler
            transferForm.addEventListener('submit', function (e) {
            e.preventDefault();

                // Prepare form data based on requisition type
                const formData = new FormData(this);

                // For procurement, we need to manually add the ToBranch value from dropdown
                if (selectedType === 'procurement') {
                    if (!toBranchSelect.value) {
                        showError('Please select a To Branch for procurement transfer');
                        toBranchSelect.focus();
                        return;
                    }
                    // Remove any existing ToBranch value and add the selected one
                    formData.delete('ToBranch');
                    formData.append('ToBranch', toBranchSelect.value);
                } else {
                    // For interbranch, validate hidden field
                    if (!toBranchHidden.value) {
                        showError('Invalid To Branch configuration for interbranch transfer');
                        return;
                    }
                }

                // Validate dispatched quantities
                const dispatchedInputs = document.querySelectorAll('input[name*="dispatched_qty"]');
                let hasInvalidQuantity = false;

                dispatchedInputs.forEach(input => {
                    const max = parseFloat(input.getAttribute('max'));
                    const value = parseFloat(input.value);

                    if (value > max) {
                        showError(`Dispatched quantity cannot exceed approved quantity (${max})`);
                        input.focus();
                        hasInvalidQuantity = true;
                        return;
                    }

                    if (value <= 0) {
                        showError('Dispatched quantity must be greater than 0');
                        input.focus();
                        hasInvalidQuantity = true;
                        return;
                    }
                });

                if (hasInvalidQuantity) return;

                // Debug: Log form data before submission
                console.log('Submitting form with data:');
                console.log('RequisitionType:', selectedType);
                console.log('RequisitionId:', requisitionIdHidden.value);
                console.log('FromBranch:', fromBranchHidden.value);

                if (selectedType === 'procurement') {
                    console.log('ToBranch (procurement):', toBranchSelect.value);
                } else {
                    console.log('ToBranch (interbranch):', toBranchHidden.value);
                }

                // Submit using fetch to handle the FormData properly
                fetch(this.action, {
                method: 'POST',
                    body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
                    .then(response => {
                        if (response.ok) {
                            window.location.href = "{{ route('transactionstransfers.index') }}";
                        } else {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Submission failed');
                            });
                        }
                    })
                    .catch(error => {
                        showError('Submission failed: ' + error.message);
                    });
        });

            // Helper function to show errors
            function showError(message) {
                const errorDiv = document.getElementById('ajax-error');
                errorDiv.textContent = message;
                errorDiv.classList.remove('d-none');

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    errorDiv.classList.add('d-none');
                }, 5000);
            }

            // Helper function to validate quantity in real-time
            window.validateQuantity = function (input, maxQty) {
                const value = parseFloat(input.value);
                if (value > maxQty) {
                    input.setCustomValidity(`Quantity cannot exceed ${maxQty}`);
                } else if (value <= 0) {
                    input.setCustomValidity('Quantity must be greater than 0');
                } else {
                    input.setCustomValidity('');
                }
            };
        });
    </script>
@endpush

<style>
    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }

    .form-label {
        font-weight: 500;
    }

    #itemsTable input[readonly] {
        background-color: #f8f9fa;
    }

    .text-danger {
        font-weight: bold;
    }
</style>
