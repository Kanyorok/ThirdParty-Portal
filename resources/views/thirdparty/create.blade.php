@extends('layouts.app')

@section('title', 'New Third Party')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('thirdparty.parties.index') }}">Third Parties</a></li>
@endsection
@section('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
@endsection
@section('content')
<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createFromExistingModal">
        <i class="bi bi-person-plus"></i> Create from Existing
    </button>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="progress mb-3 d-none">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
                        aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                            Complete</small></div>
                </div>
                <div class="mb-4">
                    <ul class="nav nav-pills nav-justified" id="wizardSteps">
                        <li class="nav-item">
                            <a class="nav-link active" data-step="1">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="badge bg-primary rounded-circle me-2">1</span>
                                    <span class="d-none d-md-inline">Basic Info</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-step="2">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="badge bg-secondary rounded-circle me-2">2</span>
                                    <span class="d-none d-md-inline">Types</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-step="3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="badge bg-secondary rounded-circle me-2">3</span>
                                    <span class="d-none d-md-inline">Logo</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-step="4">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="badge bg-secondary rounded-circle me-2">4</span>
                                    <span class="d-none d-md-inline">User</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Wizard Form -->
                <form id="thirdPartyWizardForm" enctype="multipart/form-data"
                    action="{{ route("thirdparty.parties.store") }}" method="POST">
                    @csrf

                    <!-- Step 1: Basic Information -->
                    <div class="wizard-step" data-step="1">
                        <h4 class="mb-3">Basic Information</h4>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="Name" class="form-label"> Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Name" name="Name" required>
                                <p id="Name_error" class="invalid-feedback d-none error" role="alert"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="TradingName" class="form-label">Trading/Other Names</label>
                                <input type="text" class="form-control" id="TradingName" name="TradingName">
                                <p id="TradingName_error" class="invalid-feedback d-none error" role="alert"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="BusinessType" class="form-label">Business Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" id="BusinessType" name="BusinessType" required>
                                    <option selected disabled>Select a Business Type</option>
                                    @foreach($businessTypes as $type)
                                    <option value="{{ $type->Value }}">{{ $type->Description }}</option>
                                    @endforeach
                                </select>
                                <p id="BusinessType_error" class="invalid-feedback d-none error" role="alert"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="RegistrationNumber" class="form-label">Registration Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="RegistrationNumber"
                                    name="RegistrationNumber" required>
                                <p id="RegistrationNumber_error" class="invalid-feedback d-none error"
                                    role="alert"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="Website" class="form-label">Website</label>
                                <input type="text" class="form-control" id="Website" name="Website">
                                <p id="Website_error" class="invalid-feedback d-none error" role="alert"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="Country" class="form-label">Country <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" id="Country" name="Country">
                                    <option disabled selected>Select Country</option>
                                    @foreach($countries as $Country)
                                    "
                                    <option value="{{ $Country->CountryCode }}"
                                        data-phone="{{$Country->PhoneCode}}"
                                        data-location="{{ route('locality.select2',['country'=>$Country->CountryCode]) }}">{{ $Country->Flag}} {{ $Country->Name}}</option>
                                    @endforeach
                                </select>
                                <p id="Country_error" class="invalid-feedback d-none error" role="alert"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="Location" class="form-label">Location <span class="text-danger">*</span></label>
                                <select class="form-control locations" name="Location" id="Location"
                                    required disabled></select>
                                <p id="Location_error" class="invalid-feedback d-none error col-12"
                                    role="alert"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="TaxPIN" class="form-label">Tax PIN</label>
                                <input type="text" class="form-control" id="TaxPIN" name="TaxPIN">
                                <p id="TaxPIN_error" class="invalid-feedback d-none error" role="alert"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="VATNumber" class="form-label">VAT Number</label>
                                <input type="text" class="form-control" id="VATNumber" name="VATNumber">
                                <p id="VATNumber_error" class="invalid-feedback d-none error" role="alert"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="Email" class="form-label">Email <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="Email" name="Email" required>
                                <p id="Email_error" class="invalid-feedback d-none error" role="alert"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="Phone" class="form-label">Phone <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Phone" name="Phone" required>
                                <p id="Phone_error" class="invalid-feedback d-none error" role="alert"></p>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="PhysicalAddress" class="form-label">Physical Address</label>
                                <textarea class="form-control" id="PhysicalAddress" name="PhysicalAddress"
                                    rows="3"></textarea>
                                <p id="PhysicalAddress_error" class="invalid-feedback d-none error"
                                    role="alert"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Third Party Types -->
                    <div class="wizard-step d-none" data-step="2">
                        <h4 class="mb-3">Select Third Party Types</h4>
                        <p class="text-muted">Select all types that apply to this third party</p>
                        <div class="row" id="typesContainer">
                            @foreach($types as $type)
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="types[]"
                                        value="{{ $type->Code }}" id="type_{{ $type->Code }}">
                                    <label class="form-check-label" for="type_{{ $type->Code }}">
                                        {{ $type->Description }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                            <p id="types_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div id="TenantFields" class="d-none border-top border-1 my-2">
                            <p class="mb-2 h5">Tenant Details</p>
                            <div class="row">
                                <div class="col-12">
                                    <label for="tenant_Remarks" class="form-label">Remarks <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control user-form" id="tenant_Remarks"
                                        name="tenant_Remarks" rows="2"></textarea>
                                    <p id="tenant_Remarks_error" class="invalid-feedback d-none error"
                                        role="alert"></p>
                                </div>
                            </div>
                        </div>
                        <div id="CustomerFields" class="d-none border-top border-1 my-2">
                            <p class="mb-2  h5">Customer Details</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="customer_Occupation" class="form-label">Occupation <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" id="customer_Occupation"
                                        name="customer_Occupation">
                                        <option selected disabled>Select Occupation</option>
                                        @foreach($occupations as $type)
                                        <option value="{{ $type->Value }}">{{ $type->Description }}</option>
                                        @endforeach
                                    </select>
                                    <p id="customer_Occupation_error" class="invalid-feedback d-none error"
                                        role="alert"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="customer_DateOfBirth" class="form-label">Date Of Birth <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="customer_DateOfBirth"
                                        name="customer_DateOfBirth">
                                    <p id="customer_DateOfBirth_error" class="invalid-feedback d-none error"
                                        role="alert"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="customer_MaritalStatus" class="form-label">Marital Status <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" id="customer_MaritalStatus"
                                        name="customer_MaritalStatus">
                                        <option selected disabled>Select Marital Status</option>
                                        @foreach($maritalstatus as $type)
                                        <option value="{{ $type->Value }}">{{ $type->Description }}</option>
                                        @endforeach
                                    </select>
                                    <p id="customer_MaritalStatus_error" class="invalid-feedback d-none error"
                                        role="alert"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="customer_Gender" class="form-label">Gender <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" id="customer_Gender" name="customer_Gender">
                                        <option selected disabled>Select Gender</option>
                                        @foreach($genders as $type)
                                        <option value="{{ $type->Value }}">{{ $type->Description }}</option>
                                        @endforeach
                                    </select>
                                    <p id="customer_Gender_error" class="invalid-feedback d-none error"
                                        role="alert"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Logo Upload -->
                    <div class="wizard-step d-none" data-step="3">
                        <h4 class="mb-3">Logo / Image (Optional)</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="logo" class="form-label">Upload Logo</label>
                                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                    <small class="text-muted">Accepted formats: JPG, PNG, GIF (Max: 2MB)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="logoPreview" class="d-none">
                                    <p class="form-label">Preview:</p>
                                    <img id="logoPreviewImg" src="" alt="Logo Preview" class="img-thumbnail"
                                        style="max-width: 200px; max-height: 200px;">
                                </div>
                            </div>
                            <p id="logo_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                    </div>

                    <!-- Step 4: Third Party User -->
                    <div class="wizard-step d-none" data-step="4">
                        <h4 class="mb-3">Third Party User (Optional)</h4>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="createUser" name="createUser">
                            <label class="form-check-label" for="createUser">
                                Create a user for this third party
                            </label>
                        </div>
                        <div id="userFields" class="d-none">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="user_FirstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control user-form" id="user_FirstName"
                                        name="user_FirstName">
                                    <p id="user_FirstName_error" class="invalid-feedback d-none error"
                                        role="alert"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="user_LastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control user-form" id="user_LastName"
                                        name="user_LastName">
                                    <p id="user_LastName_error" class="invalid-feedback d-none error"
                                        role="alert"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="user_Email" class="form-label">Email</label>
                                    <input type="email" class="form-control user-form" id="user_Email"
                                        name="user_Email">
                                    <p id="user_Email_error" class="invalid-feedback d-none error" role="alert"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="user_Phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control user-form" id="user_Phone"
                                        name="user_Phone">
                                    <p id="user_Phone_error" class="invalid-feedback d-none error" role="alert"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="user_Gender" class="form-label">Gender</label>
                                    <select class="form-control user-form" id="user_Gender" name="user_Gender">
                                        <option selected disabled>Select Gender</option>
                                        @foreach($genders as $type)
                                        <option value="{{ $type->Value }}">{{ $type->Description }}</option>
                                        @endforeach
                                    </select>
                                    <p id="user_Gender_error" class="invalid-feedback d-none error"
                                        role="alert"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button type="button" class="btn btn-primary" id="nextBtn">
                            Next<i class="fas fa-arrow-right ms-2"></i>
                        </button>
                        <button type="submit" class="btn btn-success" id="thirdPartyWizardBtn"
                            style="display: none;">
                            <i class="fas fa-check me-2"></i>Create Third Party
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Create From Existing Modal -->
<div class="modal fade" id="createFromExistingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create from Existing Third Party</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createFromExistingForm">
                    @csrf
                    <div class="mb-3">
                        <label for="roleType" class="form-label">Select Role to Add</label>
                        <select class="form-select" id="roleType" name="type" required>
                            <option value="">Select Role...</option>
                            <option value="SU">Supplier</option>
                            <option value="TN">Tenant</option>
                            <option value="CU">Customer</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="existingPartySelect" class="form-label">Search Existing Party</label>
                        <select class="form-select" id="existingPartySelect" name="third_party_id" required style="width: 100%;">
                            <option value="">Type to search...</option>
                        </select>
                        <div class="form-text">Search by Name, Trading Name, or Email. Only parties without the selected role will appear.</div>
                    </div>

                    <!-- Tenant Specific Fields -->
                    <div id="tenantFields" class="role-fields d-none">
                        <div class="mb-3">
                            <label class="form-label">Remarks *</label>
                            <textarea name="tenant_Remarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Customer Specific Fields -->
                    <div id="customerFields" class="role-fields d-none">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth *</label>
                                <input type="date" name="customer_DateOfBirth" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender *</label>
                                <select name="customer_Gender" class="form-select">
                                    <option value="">Select Gender...</option>
                                    @foreach($genders as $gender)
                                    <option value="{{ $gender->Value }}">{{ $gender->Description }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Marital Status *</label>
                                <select name="customer_MaritalStatus" class="form-select">
                                    <option value="">Select Status...</option>
                                    @foreach($maritalstatus as $status)
                                    <option value="{{ $status->Value }}">{{ $status->Description }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Occupation *</label>
                                <select name="customer_Occupation" class="form-select">
                                    <option value="">Select Occupation...</option>
                                    @foreach($occupations as $occupation)
                                    <option value="{{ $occupation->Value }}">{{ $occupation->Description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="confirmAddRole">Create</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
<script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
<script>
    $(function() {
        let currentStep = 1;
        const totalSteps = 4;

        $('#Country').select2({
            placeholder: "Select a Country",
            width: '100%'
        }).on('change', function() {
            const option = $(this).find('option:selected');
            $('#Phone').val(option.data('phone'));
            $('#Location').prop('disabled', false).select2('destroy').val(null).select2({
                placeholder: "Search for the Location",
                minimumInputLength: 2,
                width: '100%',
                ajax: {
                    url: option.data('location'),
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: $.trim(params.term)
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.Name,
                                    id: item.ID
                                }
                            })
                        };
                    },
                    cache: true
                }
            });
        });
        $('#Location').select2();


        // Logo preview
        $('#logo').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#logoPreviewImg').attr('src', e.target.result);
                    $('#logoPreview').removeClass('d-none');
                }
                reader.readAsDataURL(file);
            } else {
                $('#logoPreview').addClass('d-none');
            }
        });

        // Toggle user fields
        $('#createUser').on('change', function() {
            if ($(this).is(':checked')) {
                $('#userFields').removeClass('d-none');
                $('#userFields input, #userFields select').prop('required', true)
            } else {
                $('#userFields').addClass('d-none');
                $('#userFields input, #userFields select').prop('required', false).val(null).change();
            }
        });

        // Toggle Customers fields
        $('#type_CU').on('change', function() {
            if ($(this).is(':checked')) {
                $('#CustomerFields').removeClass('d-none');
                $('#CustomerFields input, #CustomerFields select').prop('required', true);
            } else {
                $('#CustomerFields').addClass('d-none');
                $('#CustomerFields input, #CustomerFields select').prop('required', false).val(null).change();
            }
        });

        // Toggle Tenants fields
        $('#type_TN').on('change', function() {
            if ($(this).is(':checked')) {
                $('#TenantFields').removeClass('d-none');
                $('#TenantFields input, #TenantFields select').prop('required', true);
            } else {
                $('#TenantFields').addClass('d-none');
                $('#TenantFields input, #TenantFields select').prop('required', false).val(null).change();
            }
        });

        // Next button
        $('#nextBtn').on('click', function() {
            if (validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });

        // Previous button
        $('#prevBtn').on('click', function() {
            currentStep--;
            showStep(currentStep);
        });

        // Form submission
        $('#thirdPartyWizardForm').on('submit', function(e) {
            e.preventDefault();
            if (!validateStep(currentStep)) {
                return false;
            }
            $('#progress-bar').parent().removeClass('d-none');
            const saveBtn = $('#thirdPartyWizardBtn');
            const btnContent = saveBtn.html();
            $(".form-control").removeClass('is-invalid');
            $('.error').addClass('d-none');
            saveBtn.prop('disable', true).addClass('disabled').prop('type', 'button').html('<i class="fas fa-spinner fa-spin"></i> please wait');
            $(this).ajaxSubmit({
                dataType: 'json',
                beforeSubmit: function() {
                    $("#progress-bar").width('0%');
                },
                uploadProgress: function(event, position, total, percentComplete) {
                    $("#progress-bar").width(percentComplete + '%').html('<small id="progress-status">' + percentComplete + ' % Complete</small>');
                },
                success: function(data) {
                    nSuccess(data.message);
                    $("#progress-bar").width('0%').html('0').parent().removeClass('d-none');
                    saveBtn.html('<i class="fas fa-check"></i> Created Successfully');
                    setTimeout(() => {
                        window.location.replace(data.route);
                    }, 2000);
                },
                error: function(request) {
                    formRequest(request, true);
                    if (request.status === 422 && request.responseJSON.errors) {
                        showStep(1);
                        currentStep = 1;
                    }
                    $("#progress-bar").width('0%').html('0').parent().addClass('d-none');
                    saveBtn.prop('disable', false).removeClass('disabled').prop('type', 'submit').html(btnContent);

                },
                resetForm: true
            });

        });

        function showStep(step) {
            // Hide all steps
            $('.wizard-step').addClass('d-none');

            // Show current step
            $(`.wizard-step[data-step="${step}"]`).removeClass('d-none');

            // Update progress indicators
            $('#wizardSteps li').each(function() {
                const stepNum = $(this).find('a').data('step');
                const badge = $(this).find('.badge');
                const link = $(this).find('a');

                if (stepNum < step) {
                    link.removeClass('active').addClass('text-success');
                    badge.removeClass('bg-primary bg-secondary').addClass('bg-success');
                } else if (stepNum === step) {
                    link.addClass('active').removeClass('text-success');
                    badge.removeClass('bg-secondary bg-success').addClass('bg-primary');
                } else {
                    link.removeClass('active text-success');
                    badge.removeClass('bg-primary bg-success').addClass('bg-secondary');
                }
            });

            // Update navigation buttons
            if (step === 1) {
                $('#prevBtn').hide();
            } else {
                $('#prevBtn').show();
            }

            if (step === totalSteps) {
                $('#nextBtn').hide();
                $('#thirdPartyWizardBtn').show();
            } else {
                $('#nextBtn').show();
                $('#thirdPartyWizardBtn').hide();
            }

            // Scroll to top
            $('html, body').animate({
                scrollTop: 0
            }, 300);
        }

        function validateStep(step) {
            let isValid = true;
            const currentStepEl = $(`.wizard-step[data-step="${step}"]`);

            // Clear previous validation
            currentStepEl.find('.is-invalid').removeClass('is-invalid');

            // Validate required fields in current step
            currentStepEl.find('input[required], select[required], textarea[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                }
            });

            // Step 2: At least one type must be selected
            if (step === 2) {
                const checkedTypes = $('input[name="types[]"]:checked').length;
                if (checkedTypes === 0) {
                    nWarning('Please select at least one third party type.');
                    isValid = false;
                }
            }

            // Step 4: Validate user fields if creating user
            if (step === 4 && $('#createUser').is(':checked')) {
                const requiredUserFields = ['user_FirstName', 'user_LastName', 'user_Email'];
                requiredUserFields.forEach(function(fieldId) {
                    const field = $('#' + fieldId);
                    if (!field.val()) {
                        field.addClass('is-invalid');
                        console.log(fieldId)
                        isValid = false;
                    }
                });

                if (!isValid) {
                    nWarning('Please fill in all required user fields.');
                }
            }

            if (!isValid && step === 1) {
                nWarning('Please fill in all required fields.');
            }

            return isValid;
        }


        // Initialize first step
        showStep(1);

        // Create from Existing Logic
        const $modal = $('#createFromExistingModal');
        const $roleSelect = $('#roleType');
        const $partySelect = $('#existingPartySelect');
        const $confirmBtn = $('#confirmAddRole');

        // Reset form when modal opens
        $modal.on('shown.bs.modal', function() {
            $partySelect.val(null).trigger('change');
            $roleSelect.val('');
            $('#createFromExistingForm')[0].reset();
            $('.role-fields').addClass('d-none');
        });

        // Initialize Select2 for searching existing parties
        $partySelect.select2({
            dropdownParent: $modal,
            ajax: {
                url: "{{ route('thirdparty.parties.search-existing') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        type: $roleSelect.val() // filter out parties that already have this type
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            placeholder: 'Type to search...',
            minimumInputLength: 1
        });

        // Show/Hide fields based on role selection
        $roleSelect.on('change', function() {
            const role = $(this).val();
            // clear previous search selection as exclusion criteria changed
            $partySelect.val(null).trigger('change');

            $('.role-fields').addClass('d-none');
            if (role === 'TN') {
                $('#tenantFields').removeClass('d-none');
            } else if (role === 'CU') {
                $('#customerFields').removeClass('d-none');
            }
        });

        // Handle Form Submission
        $confirmBtn.on('click', function() {
            const $btn = $(this);
            const data = $('#createFromExistingForm').serialize();

            // basic validation (HTML5 validation doesn't trigger on ajax button click automatically)
            if (!$roleSelect.val() || !$partySelect.val()) {
                alert('Please select a role and a party.');
                return;
            }

            $btn.prop('disabled', true).text('Creating...');

            $.ajax({
                url: "{{ route('thirdparty.parties.add-role') }}",
                method: 'POST',
                data: data,
                success: function(res) {
                    if (res.success) {
                        window.location.href = res.redirect;
                    } else {
                        alert(res.message || 'Error adding role.');
                        $btn.prop('disabled', false).text('Create');
                    }
                },
                error: function(xhr) {
                    let msg = 'An error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                    $btn.prop('disabled', false).text('Create');
                }
            });
        });
    });
</script>