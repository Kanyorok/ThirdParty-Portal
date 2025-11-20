@extends('layouts.app')
@section('title', 'GL Mappings')

@section('content')
<div class="card shadow p-2 rounded-4">

    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-info"><i class="fas fa-map-pin"></i> Mapping List</h5>
        <button type="button" class="btn btn-info btn-sm p-2" data-bs-toggle="modal" data-bs-target="#createMappingModal">
            <i class="fas fa-plus me-1"></i> New Mapping
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-hover table-sm align-middle table-striped1 text-center"
        style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        <thead>
            <tr>
                <th>#</th>
                <th>Module</th>
                <th>Transaction Type</th>
                <th>Debit GL</th>
                <th>Credit GL</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mappings as $map)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $map->modules->Name }}</td>
                <td>{{ $map->transactions->Name }}</td>
                <td>{{$map->debitAccount?->GLCode}} <small>({{ $map->debitAccount->GLName }})</small></td>
                <td>{{$map->creditAccount?->GLCode}} <small>({{ $map->creditAccount->GLName }})</small></td>
                <td class="text-center">
                    <button type="button"
                        class="btn btn-sm btn-outline-primary me-1 edit-mapping-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#editMappingModal"
                        data-mapping-id="{{ $map->Id }}"
                        title="Edit GL Mapping">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger custom-delete-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#customDeleteConfirmModal"
                        data-name="{{ $map->modules->Name . ' - ' . $map->transactions->Name }}"
                        data-route="{{ route('glpostingmap.destroy', $map->Id) }}"
                        title="Delete GL Mapping">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-0">
                    <div class="text-center p-4 border rounded-3 bg-light">
                        <p class="mb-3 text-muted fs-5">
                            <i class="fas fa-info-circle me-2 text-info"></i>
                            <i>No mappings found.</i>
                        </p>
                        <button type="button" class="btn btn-info px-4 py-2" data-bs-toggle="modal" data-bs-target="#createMappingModal">
                            <i class="fas fa-plus-circle me-2"></i> Add Mapping
                        </button>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Create Mapping Modal -->
<div class="modal fade" id="createMappingModal" tabindex="-1" aria-labelledby="createMappingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createMappingModalLabel">Create GL Mapping</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createMappingForm" method="POST" action="{{ route('glpostingmap.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Module</label>
                                <select name="ModuleID" id="createModuleID" class="form-select" required>
                                    <option value="">Select Module</option>
                                    @foreach ($modules ?? [] as $module)
                                    <option value="{{ $module->ModuleID }}">{{ $module->Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Transaction Type</label>
                                <select name="TransactionType" id="createTransactionType" class="form-select" required>
                                    <option selected disabled value="">-- Select Transaction Type --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Debit GL Account</label>
                                <select name="DebitGLAccountID" class="form-select" required>
                                    <option value="">Select Debit Account</option>
                                    @foreach ($glaccounts ?? [] as $account)
                                    <option value="{{ $account->Id }}">{{ $account->GLName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Credit GL Account</label>
                                <select name="CreditGLAccountID" class="form-select" required>
                                    <option value="">Select Credit Account</option>
                                    @foreach ($glaccounts ?? [] as $account)
                                    <option value="{{ $account->Id }}">{{ $account->GLName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="IsActive" class="form-check-input" id="createIsActive" checked>
                                    <label class="form-check-label" for="createIsActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Create Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Mapping Modal -->
<div class="modal fade" id="editMappingModal" tabindex="-1" aria-labelledby="editMappingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMappingModalLabel">Edit GL Mapping</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMappingForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Module</label>
                                <select name="ModuleID" id="editModuleID" class="form-select" required>
                                    <option value="">Select Module</option>
                                    @foreach ($modules ?? [] as $module)
                                    <option value="{{ $module->ModuleID }}">{{ $module->Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Transaction Type</label>
                                <select name="TransactionType" id="editTransactionType" class="form-select" required>
                                    <option selected disabled value="">-- Select Transaction Type --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Debit GL Account</label>
                                <select name="DebitGLAccountID" id="editDebitGLAccountID" class="form-select" required>
                                    <option value="">Select Debit Account</option>
                                    @foreach ($glaccounts ?? [] as $account)
                                    <option value="{{ $account->Id }}">{{ $account->GLName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Credit GL Account</label>
                                <select name="CreditGLAccountID" id="editCreditGLAccountID" class="form-select" required>
                                    <option value="">Select Credit Account</option>
                                    @foreach ($glaccounts ?? [] as $account)
                                    <option value="{{ $account->Id }}">{{ $account->GLName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="IsActive" class="form-check-input" id="editIsActive">
                                    <label class="form-check-label" for="editIsActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('components.modals.delete-confirm')

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        console.log('GL Mapping script loaded');

        // Test if jQuery is working
        if (typeof $ !== 'undefined') {
            console.log('jQuery is loaded');
        } else {
            console.error('jQuery is not loaded');
        }

        // Handle module change for transaction types in create modal
        $(document).on('change', '#createModuleID', function() {
            console.log('Create module changed');
            const selectedModule = $(this).val();
            const transactionSelect = $('#createTransactionType');

            if (selectedModule) {
                transactionSelect.html('<option selected disabled value="">Loading...</option>');

                $.ajax({
                    url: `/finance/finance/transactions/${selectedModule}`,
                    method: 'GET',
                    success: function(data) {
                        console.log('Transaction types loaded:', data);
                        let options = '<option selected disabled value="">-- Select Transaction Type --</option>';
                        if (data && data.length > 0) {
                            data.forEach(function(item) {
                                options += `<option value="${item.Id}">${item.transactions.Name}</option>`;
                            });
                        }
                        transactionSelect.html(options);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading transaction types:', error);
                        transactionSelect.html('<option selected disabled value="">-- Select Transaction Type --</option>');
                    }
                });
            } else {
                transactionSelect.html('<option selected disabled value="">-- Select Transaction Type --</option>');
            }
        });

        // Handle module change for transaction types in edit modal
        $(document).on('change', '#editModuleID', function() {
            console.log('Edit module changed');
            const selectedModule = $(this).val();
            const transactionSelect = $('#editTransactionType');

            if (selectedModule) {
                transactionSelect.html('<option selected disabled value="">Loading...</option>');

                $.ajax({
                    url: `/finance/finance/transactions/${selectedModule}`,
                    method: 'GET',
                    success: function(data) {
                        console.log('Edit transaction types loaded:', data);
                        let options = '<option selected disabled value="">-- Select Transaction Type --</option>';
                        if (data && data.length > 0) {
                            data.forEach(function(item) {
                                options += `<option value="${item.Id}">${item.transactions.Name}</option>`;
                            });
                        }
                        transactionSelect.html(options);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading edit transaction types:', error);
                        transactionSelect.html('<option selected disabled value="">-- Select Transaction Type --</option>');
                    }
                });
            } else {
                transactionSelect.html('<option selected disabled value="">-- Select Transaction Type --</option>');
            }
        });

        // Handle edit button clicks
        $(document).on('click', '.edit-mapping-btn', function() {
            console.log('Edit button clicked');
            const mappingId = $(this).data('mapping-id');
            console.log('Mapping ID:', mappingId);
            loadEditData(mappingId);
        });

        // Function to load edit data
        function loadEditData(mappingId) {
            console.log('Loading edit data for mapping:', mappingId);
            $.ajax({
                url: `/finance/glpostingmap/${mappingId}/edit`,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    console.log('Edit data loaded:', data);
                    // Populate form fields
                    $('#editModuleID').val(data.ModuleID);
                    $('#editDebitGLAccountID').val(data.DebitGLAccountID);
                    $('#editCreditGLAccountID').val(data.CreditGLAccountID);
                    $('#editIsActive').prop('checked', data.IsActive == 1);

                    // Load transaction types for the selected module
                    $('#editModuleID').trigger('change');

                    // Set the transaction type after loading
                    setTimeout(function() {
                        $('#editTransactionType').val(data.TransactionTypeID);
                    }, 1000);

                    // Set form action
                    $('#editMappingForm').attr('action', `/finance/glpostingmap/${mappingId}`);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading edit data:', error);
                    showToast('error', 'Failed to load mapping data');
                }
            });
        }

        // Handle form submissions via AJAX for create modal
        $(document).on('submit', '#createMappingForm', function(e) {
            e.preventDefault();
            console.log('Create form submitted');

            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();

            // Validate form before submission
            const moduleID = $('#createModuleID').val();
            const transactionType = $('#createTransactionType').val();
            const debitAccount = $('select[name="DebitGLAccountID"]').val();
            const creditAccount = $('select[name="CreditGLAccountID"]').val();

            console.log('Form validation:', {
                moduleID: moduleID,
                transactionType: transactionType,
                debitAccount: debitAccount,
                creditAccount: creditAccount
            });

            if (!moduleID || !transactionType || !debitAccount || !creditAccount) {
                showToast('error', 'Please fill in all required fields');
                return;
            }

            // Debug: Log form data
            const formData = form.serialize();
            console.log('Form data being sent:', formData);
            console.log('Form action URL:', form.attr('action'));

            // Disable button and show loading
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    console.log('Create response:', data);
                    if (data.success) {
                        showToast('success', data.message || 'Mapping created successfully');
                        $('#createMappingModal').modal('hide');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast('error', data.message || 'Failed to create mapping');
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Create error:', error);
                    console.error('XHR response:', xhr.responseText);
                    console.error('Status:', status);

                    let errorMessage = 'Failed to create mapping. Please try again.';

                    if (xhr.status === 422) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            console.error('Validation errors:', response.errors);

                            if (response.errors) {
                                const errorMessages = [];
                                for (const field in response.errors) {
                                    errorMessages.push(response.errors[field][0]);
                                }
                                errorMessage = 'Validation errors: ' + errorMessages.join(', ');
                            }
                        } catch (e) {
                            console.error('Error parsing validation response:', e);
                        }
                    }

                    showToast('error', errorMessage);
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }
            });
        });

        // Handle form submissions via AJAX for edit modal
        $(document).on('submit', '#editMappingForm', function(e) {
            e.preventDefault();
            console.log('Edit form submitted');

            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();

            // Validate form before submission
            const moduleID = $('#editModuleID').val();
            const transactionType = $('#editTransactionType').val();
            const debitAccount = $('#editDebitGLAccountID').val();
            const creditAccount = $('#editCreditGLAccountID').val();

            console.log('Edit form validation:', {
                moduleID: moduleID,
                transactionType: transactionType,
                debitAccount: debitAccount,
                creditAccount: creditAccount
            });

            if (!moduleID || !transactionType || !debitAccount || !creditAccount) {
                showToast('error', 'Please fill in all required fields');
                return;
            }

            // Disable button and show loading
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Updating...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    console.log('Edit response:', data);
                    if (data.success) {
                        showToast('success', data.message || 'Mapping updated successfully');
                        $('#editMappingModal').modal('hide');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast('error', data.message || 'Failed to update mapping');
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Edit error:', error);
                    showToast('error', 'Failed to update mapping. Please try again.');
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }
            });
        });
    });

    function showToast(type, message) {
        console.log('Showing toast:', type, message);
        const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

        $('body').append(toastHtml);
        const toast = new bootstrap.Toast($('.toast').last()[0]);
        toast.show();

        $('.toast').last().on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
</script>
@endsection