@extends('layouts.app')
@section('title', 'Budget Creation')
@section('content')
    <div class="container-lg mt-4">
        {{--        <h4 class="fw-bold mb-4 text-primary">Create New Budget Period</h4>--}}
        <div class="fs-6 text-secondary mb-4">
            This form allows you to create a new budget by specifying budget details and selecting GL accounts.
        </div>
    @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
    @endif
    @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
    @endif

    <form method="post" action="{{ route('budgetperiod.store') }}">
        @csrf
        @method('POST')
        <div class="card p-4 shadow-sm border-0 rounded-3">
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="Name" class="form-label fw-medium">Budget Name</label>
                    <input type="text" class="form-control rounded-3" id="Name" name="Name"
                           placeholder="Enter the Budget Name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="FiscalYear" class="form-label fw-medium">Fiscal Year</label>
                    <input type="number" class="form-control rounded-3" id="FiscalYear" min="2020" name="FiscalYear"
                           placeholder="e.g., 2025" required>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="From" class="form-label fw-medium">From (Date)</label>
                    <input type="date" class="form-control rounded-3" id="From" name="From" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="To" class="form-label fw-medium">To (Date)</label>
                    <input type="date" class="form-control rounded-3" id="To" name="To" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label fw-medium">Notes</label>
                <textarea class="form-control rounded-3" id="notes" name="Notes" rows="3"
                          placeholder="Add any additional notes"></textarea>
            </div>

            <!-- GL Selection Section -->
            <h5 class="mt-4 mb-3 fw-bold text-primary">Select General Ledger Accounts</h5>
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="glAccountType" class="form-label fw-medium">GL Account Type</label>
                    <select class="form-select rounded-3" id="glAccountType" name="glAccountType">
                        <option disabled selected>Select GL Account Type</option>
                        <option value="A" data-description="Asset">Asset (A)</option>
                        <option value="E" data-description="Expense">Expense (E)</option>
                        <option value="I" data-description="Income">Income (I)</option>
                        <option value="L" data-description="Liability">Liability (L)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="glSubAccountType" class="form-label fw-medium">GL Subtype</label>
                    <select class="form-select rounded-3" id="glSubAccountType" name="glSubAccountType" disabled>
                        <option disabled selected>Select Subtype</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="glAccounts" class="form-label fw-medium">GL Accounts</label>
                    <select class="form-select rounded-3" id="glAccounts" multiple size="8" disabled>
                        <option disabled>Select GL Accounts</option>
                    </select>
                    <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple GLs.</div>
                    <button type="button" class="btn btn-outline-primary mt-2 w-100 rounded-3" id="addGlButton"
                            disabled>
                        <i class="bi bi-plus-circle me-1"></i>Add Selected GLs
                    </button>
                </div>
            </div>

            <!-- Selected GLs Table -->
            <h6 class="mt-3 mb-2 fw-bold">Selected GL Accounts</h6>
            <div class="table-responsive">
                <table class="table table-hover table-bordered rounded-3" id="selectedGlTable">
                    <thead class="table-light">
                    <tr>
                        <th scope="col">Account ID</th>
                        <th scope="col">Description</th>
                        <th scope="col">Account Type</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody id="selectedGlBody">
                    <!-- Selected GLs will be appended here -->
                    </tbody>
                </table>
            </div>
            <!-- Hidden input to store selected GLs -->
            <input type="hidden" name="selected_gls" id="selected_gls" value="">
            <div class="modal-footer border-0 pt-4">
                {{--                <button type="button" class="btn btn-outline-secondary rounded-3 me-2">Cancel</button>--}}
                <button type="submit" class="btn btn-primary rounded-3"
                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
                    <i class="bi bi-save me-1"></i>Create Budget
                </button>
            </div>
        </div>
    </form>

        <!-- Custom CSS for Enhanced UI -->
        <style>
            .card {
                background-color: #f8f9fa;
                transition: all 0.3s ease;
            }

            .card:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .form-select, .form-control {
                border-color: #ced4da;
                transition: border-color 0.2s ease;
            }

            .form-select:focus, .form-control:focus {
                border-color: #0d6efd;
                box-shadow: 0 0 5px rgba(13, 110, 253, 0.3);
            }

            .btn-outline-primary {
                transition: all 0.2s ease;
            }

            .btn-outline-primary:hover {
                background-color: #0d6efd;
                color: white;
            }

            .btn-danger {
                transition: all 0.2s ease;
            }

            .btn-danger:hover {
                background-color: #dc3545;
                border-color: #dc3545;
            }

            .table-hover tbody tr:hover {
                background-color: #e9ecef;
            }

            .alert {
                border-radius: 0.5rem;
            }
        </style>

        <!-- JavaScript for Client-Side Filtering -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            $(document).ready(function () {
                // Preload GL Subtypes and GL Accounts data
                const glSubtypes = @json($glSubtypes);
                const glAccounts = @json($glAccounts);

                // Filter GL Subtypes when GL Account Type is selected
                $('#glAccountType').change(function () {
                    let glAccountTypeId = $(this).val();
                    $('#glSubAccountType').prop('disabled', true).html('<option disabled selected>Select Subtype</option>');
                    $('#glAccounts').prop('disabled', true).html('<option disabled>Select GL Accounts</option>');
                    $('#addGlButton').prop('disabled', true);

                    if (glAccountTypeId) {
                        let filteredSubtypes = glSubtypes.filter(subtype => subtype.GLAccountTypeID == glAccountTypeId);
                        let options = '<option disabled selected>Select Subtype</option>';
                        filteredSubtypes.forEach(subtype => {
                            options += `<option value="${subtype.GLSubAccountTypeID}">${subtype.Description}</option>`;
                        });
                        $('#glSubAccountType').html(options).prop('disabled', false);
                    }
                });

                // Filter GL Accounts when GL Subtype is selected
                $('#glSubAccountType').change(function () {
                    let glSubAccountTypeId = $(this).val();
                    $('#glAccounts').prop('disabled', true).html('<option disabled>Select GL Accounts</option>');
                    $('#addGlButton').prop('disabled', true);

                    if (glSubAccountTypeId) {
                        let filteredGls = glAccounts.filter(gl => gl.GLSubAccountTypeID == glSubAccountTypeId);
                        let options = '<option disabled>Select GL Accounts</option>';
                        filteredGls.forEach(gl => {
                            options += `<option value="${gl.AccountID}" data-description="${gl.Description}" data-gl-account-type-id="${gl.GLAccountTypeID}">${gl.Description} (${gl.AccountID})</option>`;
                        });
                        $('#glAccounts').html(options).prop('disabled', false);
                        $('#addGlButton').prop('disabled', false);
                    }
                });

                // Add selected GLs to the table
                $('#addGlButton').click(function () {
                    let selectedOptions = $('#glAccounts option:selected');
                    let selectedGls = JSON.parse($('#selected_gls').val() || '[]');

                    selectedOptions.each(function () {
                        let glAccountTypeId = $(this).data('gl-account-type-id');
                        let glAccountTypeDescription = $('#glAccountType option[value="' + glAccountTypeId + '"]').data('description') || 'Unknown Type';
                        let gl = {
                            AccountID: $(this).val(),
                            Description: $(this).data('description'),
                            GLAccountTypeID: glAccountTypeId,
                            GLAccountTypeDescription: glAccountTypeDescription
                        };

                        // Avoid duplicates
                        if (!selectedGls.some(item => item.AccountID == gl.AccountID)) {
                            selectedGls.push(gl);
                            $('#selectedGlBody').append(`
                            <tr data-account-id="${gl.AccountID}">
                                <td>${gl.AccountID}</td>
                                <td>${gl.Description}</td>
                                <td>${gl.GLAccountTypeDescription}</td>
                                <td><button type="button" class="btn btn-sm btn-danger remove-gl">Remove</button></td>
                            </tr>
                        `);
                        }
                    });

                    // Update hidden input with selected GLs
                    $('#selected_gls').val(JSON.stringify(selectedGls));
                });

                // Remove GL from the table
                $(document).on('click', '.remove-gl', function () {
                    let row = $(this).closest('tr');
                    let accountId = row.data('account-id');
                    let selectedGls = JSON.parse($('#selected_gls').val() || '[]');
                    selectedGls = selectedGls.filter(gl => gl.AccountID != accountId);
                    $('#selected_gls').val(JSON.stringify(selectedGls));
                    row.remove();
                });
            });
        </script>
@endsection
