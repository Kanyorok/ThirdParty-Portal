@php use App\Enums\Employee\GenderEnum; @endphp
@extends('layouts.app')

@section('title', 'Add Employee')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <form class="card" action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data"
                  id="newEmployeeForm">
                <div class="card-header">@csrf
                    <div class="card-actions float-end">
                        <a class="btn btn-primary ms-2 " href="{{ route('employees.index') }}">
                            <i class="fas fa-backward"></i> back to employees
                        </a>
                    </div>
                    <h5 class="card-title mb-0">@yield('title')</h5>
                </div>
                <div class="card-body row">
                    <div class="col-12 progress mb-3 avatar-change d-none">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
                             aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                                Complete</small></div>
                    </div>
                    <div class="col-sm-4 col-md-3">
                        <div class="mb-3 text-center">
                            <input type="file" name="image" class="d-none" accept="image/*"
                                   style="display: none;" id="Upload_image">
                            <label for="Upload_image">
                                <img src="https://placehold.co/200x200?font=roboto&text=Pick%20a%20Photo"
                                     id="image_upload_preview" alt=".." class="img-fluid img-thumbnail mb-2"
                                     width="200" height="200"/></label>
                            <p id="image_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                    </div>
                    <div class="col-sm-8 col-md-9">
                        <div class="row">
                            <div class="mb-3 col-md-4 col-12">
                                <label class="form-label" for="FirstName">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="FirstName" name="FirstName" required
                                       placeholder="FirstName">
                                <p id="FirstName_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3 col-md-4 col-12">
                                <label class="form-label" for="MiddleName">Middle Name</label>
                                <input type="text" class="form-control" id="MiddleName" name="MiddleName"
                                       placeholder="MiddleName">
                                <p id="MiddleName_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3 col-md-4 col-12">
                                <label class="form-label" for="LastName">Last Name / Surname <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="LastName" name="LastName" required
                                       placeholder="LastName">
                                <p id="LastName_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3 col-md-6 col-12">
                                <label class="form-label" for="Phone">Phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Phone" name="Phone" required
                                       placeholder="Phone">
                                <p id="Phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3 col-md-6 col-12">
                                <label class="form-label" for="Email">Email <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Email" name="Email" required
                                       placeholder="Email">
                                <p id="Email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                        </div>

                    </div>
                    <div class="mb-3 col-md-4 col-12">
                        <label class="form-label" for="Department">Department <span class="text-danger">*</span></label>
                        <select class="form-control select2-fields" id="Department" name="Department" required>
                            @foreach($departments as $department)
                                <option value="{{ $department->DepartmentID }}">{{ $department->Name }}</option>
                            @endforeach
                        </select>
                        <p id="Department_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>
                    <div class="mb-3 col-md-4 col-12">
                        <label class="form-label" for="Branch">Branch <span class="text-danger">*</span></label>
                        <select class=" select2-fields" id="Branch" name="Branch" required>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->BranchID }}">{{ $branch->Name }}</option>
                            @endforeach
                        </select>
                        <p id="Branch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>
                    <div class="mb-3 col-md-4 col-12">
                        <label class="form-label" for="Gender">Gender <span class="text-danger">*</span></label>
                        <select class="form-control select2-fields" id="Gender" name="Gender" required>
                            @foreach(GenderEnum::cases() as $gender)
                                <option value="{{ $gender->value }}">{{ $gender->name }}</option>
                            @endforeach
                        </select>
                        <p id="Gender_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>
                    <div class="mb-3 col-md-4 col-12">
                        <label class="form-label" for="JobTitle">Job Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="JobTitle" name="JobTitle" required/>
                        <p id="JobTitle_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>
                    <div class="mb-3 col-md-4 col-12">
                        <label class="form-label" for="JoinDate">Join Date <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="JoinDate" name="JoinDate" required/>
                        <p id="JoinDate_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>
                    <div class="mb-3 col-md-4 col-12">
                        <label class="form-label" for="DateOfBirth">Date Of Birth <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="DateOfBirth" name="DateOfBirth" required/>
                        <p id="DateOfBirth_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label" for="Address">Address</label>
                            <textarea class="form-control" id="Address" name="Address" rows="3"
                                      placeholder="Address"></textarea>
                            <p id="Address_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="CreateUser"
                                                           name="CreateUser">
                                <label class="form-check-label" for="CreateUser">Create a user account for this
                                    employee</label></div>
                            <p id="Address_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                    </div>
                </div>
                <div class="card-footer row">
                    <div class="col-6"><a href="{{ route('employees.index') }}"
                                          class="btn btn-secondary ml-2">Cancel</a></div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-success mx-2 float-end" id="newEmployeeBtn"><i
                                class="fas fa-save"></i> add Employee
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script src='{{ asset('assets/libs/flatpickr/flatpickr.min.js.js') }}'></script>
    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function () {
            $('.select2-fields').select2({
                width: '100%',
                placeholder: 'Select an option',
                style: 'padding: 0.5rem 0.75rem'
            });


            $("#Upload_image").change(function () {
                readURL(this);
            });

            flatpickr("#JoinDate", {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                allowInput: true,
                defaultDate: "today",
                maxDate: "today"
            });

            flatpickr("#DateOfBirth", {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                allowInput: true,
                defaultDate: moment().subtract(18, 'year').format('YYYY-MM-DD'),
                maxDate: moment().subtract(18, 'year').format('YYYY-MM-DD'),
            });

            $('form#newEmployeeForm').submit(function (e) {
                e.preventDefault();
                const saveBtn = $("#newEmployeeBtn"), btnContent = saveBtn.html();
                saveBtn.prop('disable', true).addClass('disabled').prop('type', 'button').html('<i class="fas fa-spinner fa-spin"></i> please wait');
                $("#progress-bar").parent().removeClass('d-none');
                $(this).ajaxSubmit({
                    dataType: 'json', beforeSubmit: function () {
                        $("#progress-bar").width('0%');
                    },
                    uploadProgress: function (event, position, total, percentComplete) {
                        $("#progress-bar").width(percentComplete + '%').html('<small id="progress-status">' + percentComplete + ' % Complete</small>');
                    },
                    success: function (data) {
                        nSuccess(data.message);
                        $("#progress-bar").parent().addClass('d-none');
                        saveBtn.html("<i class='fas fa-check-double'></i> Employee added");
                        window.setTimeout(function () {
                            window.location.replace(data.route);
                        }, 3000)
                    },
                    error: function (request) {
                        $("#progress-bar").parent().addClass('d-none');
                        formRequest(request, true);
                        saveBtn.prop('disable', false).removeClass('disabled').prop('type', 'submit').html(btnContent);
                    }, resetForm: true
                });
                return false;
            });
        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#image_upload_preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
