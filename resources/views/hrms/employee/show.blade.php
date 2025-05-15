@php use App\Enums\Employee\GenderEnum; @endphp
@extends('layouts.app')

@section('title')
    {{ \Illuminate\Support\Str::upper($employee->EmployeeID) }}
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
@endsection
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
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body py-0">
                    <ul class="nav nav-tabs profile-tabs" id="employeeTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#tab-0" role="tab"
                               aria-selected="false" tabindex="-1">
                                <i class="ti ti-user me-2"></i>Personal Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link " id="profile-tab-2" href="#tab-1" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <i class="ti ti-report-money me-2"></i> Payroll Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="profile-tab-3" href="#tab-2" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <i data-feather="umbrella"></i> Leaves</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="profile-tab-4" href="#tab-3" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <i class="ti ti-unlink me-2"></i> Next of Kin</a></li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="profile-tab-5" href="#tab-4" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <i class="ti ti-files me-2"></i> Documents Related</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="profile-tab-6" href="#tab-5" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <i class="ti ti-logout me-2"></i> Attrition</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content">
                <div class="tab-pane active show" id="tab-0" role="tabpanel" aria-labelledby="profile-tab-1">
                    <div class="row">
                        <div class="col-lg-4 col-xxl-3">
                            <div class="card">
                                <div class="card-body position-relative">
                                    <div class="text-center mt-3">
                                        <div class="chat-avtar d-inline-flex mx-auto">
                                            {!! $employee->getImage('class="rounded-circle img-fluid wid-70"  alt="User image""') !!}
                                        </div>
                                        <h5 class="mb-0">{{ $employee->full_name }}</h5>
                                        <p class="text-muted text-sm">{{ $employee->JobTitle }}</p>
                                        <hr class="my-3 border border-secondary-subtle">
                                        <div class="d-inline-flex align-items-center justify-content-start w-100 mb-3">
                                            <i class="ti ti-mail me-2"></i>
                                            <p class="mb-0"><a href="javascript:void(0)"
                                                               data-info="{{ route('employees.store', [$employee->EmployeeID]) }}~{{ $employee->full_name }}~{{ $employee->Email }}"
                                                               class="send-mail-to-action">{{ $employee->Email }}</a>
                                            </p>
                                        </div>
                                        <div class="d-inline-flex align-items-center justify-content-start w-100 mb-3">
                                            <i class="ti ti-phone me-2"></i>
                                            <p class="mb-0"><a href="javascript:void(0)"
                                                               data-info="{{ route('employees.store', [$employee->EmployeeID]) }}~{{ $employee->full_name }}~{{ $employee->Phone }}"
                                                               class=" send-message-to-action">{{ $employee->Phone }}</a>
                                            </p>
                                        </div>
                                        <div class="d-inline-flex align-items-center justify-content-start w-100 mb-3">
                                            <i class="ti ti-map-pin me-2"></i>
                                            <p class="mb-0">{{ $employee->Address }}</p></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header"><h5>Skills</h5></div>
                                <div class="card-body">
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    @include('snippets.behind_scenes',['model' => $employee])
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 col-xxl-9">
                            <form class="card" id="employeePersonalForm" method="post"
                                  action="{{ route('employees.update', [$employee->EmployeeID]) }}">
                                <div class="card-header"><h5>Personal Details</h5></div>
                                <div class="card-body row">@csrf
                                    <div class="mb-3 col-md-4 col-12">@method('PUT')
                                        <label class="form-label" for="FirstName">First Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control personal-form" disabled id="FirstName"
                                               name="FirstName" required
                                               placeholder="FirstName" value="{{ $employee->FirstName }}">
                                        <p id="FirstName_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3 col-md-4 col-12">
                                        <label class="form-label" for="MiddleName">Middle Name</label>
                                        <input type="text" class="form-control personal-form" disabled id="MiddleName"
                                               name="MiddleName"
                                               placeholder="MiddleName" value="{{ $employee->MiddleName }}">
                                        <p id="MiddleName_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3 col-md-4 col-12">
                                        <label class="form-label" for="LastName">Last Name / Surname <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control personal-form" disabled id="LastName"
                                               name="LastName" required
                                               placeholder="LastName" value="{{ $employee->LastName }}">
                                        <p id="LastName_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3 col-md-6 col-12">
                                        <label class="form-label" for="Phone">Phone <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control personal-form" disabled id="Phone"
                                               name="Phone" required
                                               placeholder="Phone" value="{{ $employee->Phone }}">
                                        <p id="Phone_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3 col-md-6 col-12">
                                        <label class="form-label" for="Email">Email <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control personal-form" disabled id="Email"
                                               name="Email" required
                                               placeholder="Email" value="{{ $employee->Email }}">
                                        <p id="Email_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3 col-md-4 col-12">
                                        <label class="form-label" for="Gender">Gender <span class="text-danger">*</span></label>
                                        <select class="form-control personal-form select2-fields" disabled id="Gender"
                                                name="Gender" required>
                                            @foreach( GenderEnum::cases() as $gender)
                                                <option value="{{ $gender->value }}"
                                                    {{ ($employee->Gender->value ===  $gender->value)?'selected':'' }}>{{ $gender->name }}</option>
                                            @endforeach
                                        </select>
                                        <p id="Gender_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3 col-md-4 col-12">
                                        <label class="form-label" for="DateOfBirth">Date Of Birth <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control personal-form" disabled id="DateOfBirth"
                                               name="DateOfBirth"
                                               required value="{{ $employee->DateOfBirth?->format('Y-m-d') }}">
                                        <p id="DateOfBirth_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="Address">Address</label>
                                            <textarea class="form-control personal-form" disabled id="Address"
                                                      name="Address" rows="3"
                                                      placeholder="Address"> {{ $employee->Address }}</textarea>
                                            <p id="Address_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer border-1 border-top">
                                    <div class="row">
                                        <div class="col-6">
                                            <button type="button" class="btn btn-secondary d-none float-start"
                                                    id="employeePersonalCancelBtn">
                                                cancel
                                            </button>
                                            <button type="button" class="btn btn-primary float-start"
                                                    id="employeePersonalEditBtn">
                                                edit profile
                                            </button>
                                        </div>
                                        <div class="col-6">

                                            <button type="submit" class="btn btn-success d-none float-end"
                                                    id="employeePersonalBtn">
                                                save
                                                changes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="card">
                                <div class="card-header"><h5>Education</h5></div>
                                <div class="card-body">
                                    {{-- <ul class="list-group list-group-flush">
                                         <li class="list-group-item px-0 pt-0">
                                             <div class="row">
                                                 <div class="col-md-6"><p class="mb-1 text-muted">Master Degree
                                                         (Year)</p>
                                                     <p class="mb-0">2014-2017</p></div>
                                                 <div class="col-md-6"><p class="mb-1 text-muted">Institute</p>
                                                     <p class="mb-0">-</p></div>
                                             </div>
                                         </li>
                                         <li class="list-group-item px-0">
                                             <div class="row">
                                                 <div class="col-md-6"><p class="mb-1 text-muted">Bachelor (Year)</p>
                                                     <p class="mb-0">2011-2013</p></div>
                                                 <div class="col-md-6"><p class="mb-1 text-muted">Institute</p>
                                                     <p class="mb-0">Imperial College London</p></div>
                                             </div>
                                         </li>
                                         <li class="list-group-item px-0 pb-0">
                                             <div class="row">
                                                 <div class="col-md-6"><p class="mb-1 text-muted">School (Year)</p>
                                                     <p class="mb-0">2009-2011</p></div>
                                                 <div class="col-md-6"><p class="mb-1 text-muted">Institute</p>
                                                     <p class="mb-0">School of London, England</p></div>
                                             </div>
                                         </li>
                                     </ul>--}}
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header"><h5>Employment</h5></div>
                                <div class="card-body">
                                    {{--  <ul class="list-group list-group-flush">
                                          <li class="list-group-item px-0 pt-0">
                                              <div class="row">
                                                  <div class="col-md-6"><p class="mb-1 text-muted">Senior</p>
                                                      <p class="mb-0">Senior UI/UX designer (Year)</p></div>
                                                  <div class="col-md-6"><p class="mb-1 text-muted">Job Responsibility</p>
                                                      <p class="mb-0">Perform task related to project manager with the
                                                          100+ team under my observation. Team management is key role in
                                                          this company.</p></div>
                                              </div>
                                          </li>
                                          <li class="list-group-item px-0">
                                              <div class="row">
                                                  <div class="col-md-6"><p class="mb-1 text-muted">Trainee cum Project
                                                          Manager (Year)</p>
                                                      <p class="mb-0">2017-2019</p></div>
                                                  <div class="col-md-6"><p class="mb-1 text-muted">Job Responsibility</p>
                                                      <p class="mb-0">Team management is key role in this company.</p>
                                                  </div>
                                              </div>
                                          </li>
                                          <li class="list-group-item px-0 pb-0">
                                              <div class="row">
                                                  <div class="col-md-6"><p class="mb-1 text-muted">School (Year)</p>
                                                      <p class="mb-0">2009-2011</p></div>
                                                  <div class="col-md-6"><p class="mb-1 text-muted">Institute</p>
                                                      <p class="mb-0">School of London, England</p></div>
                                              </div>
                                          </li>
                                      </ul>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-1" role="tabpanel" aria-labelledby="profile-tab-2">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Payroll Details</h5></div>
                                <div class="card-body">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-2" role="tabpanel" aria-labelledby="profile-tab-3">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Leaves</h5></div>
                                <div class="card-body">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-3" role="tabpanel" aria-labelledby="profile-tab-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Next of Kin</h5></div>
                                <div class="card-body">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-4" role="tabpanel" aria-labelledby="profile-tab-5">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Documents Related</h5></div>
                                <div class="card-body">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-5" role="tabpanel" aria-labelledby="profile-tab-6">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Attrition</h5></div>
                                <div class="card-body">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    {{-- <div class="row">
         <div class="col-md-4 col-xxl-3">
             <div class="card">
                 <div class="card-body mx-1 mb-0 mt-1">
                     @include('snippets.employee_summary', ['employee'=>$employee])
                     <hr>
                     <h5 class="h6 card-title">Contacts</h5>
                     <div class="text center">
                         <div class="btn-group">
                             <button type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                     class="btn btn-link dropdown-toggle">
                                 {{ $employee->Phone }}
                             </button>
                             <div class="dropdown-menu" style="">
                                 <a class="dropdown-item disabled text-decoration-line-through"
                                    href="javascript:void(0)"><i class="fas fa-phone-alt"></i> Call</a>
                                 <div class="dropdown-divider"></div>
                                 <a class="dropdown-item send-message-to-action" href="javascript:void(0)"
                                    data-info="{{ route('employees.store', [$employee->EmployeeID]) }}~{{ $employee->full_name }}~{{ $employee->Phone }}">
                                     <i class="fas fa-message"></i> Message</a>
                             </div>
                         </div>
                         @if(!empty($employee->Email))
                             <a href="javascript:void(0)"
                                data-info="{{ route('employees.store', [$employee->EmployeeID]) }}~{{ $employee->full_name }}~{{ $employee->Email }}"
                                class="btn btn-lg btn-link me-1 my-1 send-mail-to-action">{{ $employee->Email }}</a>
                         @endif
                     </div>
                 </div>
                 <hr class="my-0">
                 <ul class="list-group list-group-flush">
                     <li class="list-group-item"><b>Join Date</b><span class="float-end">{{ $employee->JoinDate?->format('m d, Y') }} </span></li>
                     <li class="list-group-item"><b>Gender</b><span class="float-end">{{ $employee->Gender->name }} </span></li>
                     <li class="list-group-item"><b>Date of Birth</b><span class="float-end">{{ $employee->DateOfBirth?->format('m d, Y') }} </span></li>
                     <li class="list-group-item"><b>Department</b><span
                             class="float-end">{{ $employee->department?->Name }} </span></li>
                    <li class="list-group-item"><b>Branch</b><span
                             class="float-end">{{ $employee->branch?->Name }} </span></li>

                 </ul>

             </div>
             <div class="card">
                 <div class="card-body">
                     @include('snippets.behind_scenes',['model' => $employee])
                 </div>
             </div>
         </div>
         <div class="col-md-8 col-xxl-9">
             <div class="tab">
                 <ul class="nav nav-tabs" role="tablist">
                     <li class="nav-item"><a class="nav-link active" href="#tab-0" data-bs-toggle="tab" role="tab"
                                             aria-selected="false">Basic Details</a></li>
                     <li class="nav-item"><a class="nav-link " href="#tab-1" data-bs-toggle="tab" role="tab"
                                             aria-selected="false" >Payroll Details</a></li>
                     <li class="nav-item"><a class="nav-link" href="#tab-2" data-bs-toggle="tab" role="tab"
                                             aria-selected="false" >Leaves</a></li>
                     <li class="nav-item"><a class="nav-link " href="#tab-3" data-bs-toggle="tab" role="tab"
                                             aria-selected="false">Next of Kin</a></li>
                     <li class="nav-item"><a class="nav-link " href="#tab-4" data-bs-toggle="tab" role="tab"
                                             aria-selected="false">Documents Related</a></li>
                     <li class="nav-item"><a class="nav-link " href="#tab-5" data-bs-toggle="tab" role="tab"
                                             aria-selected="false">Attrition</a>
                     </li>
                 </ul>
                 <div class="tab-content">
                     <div class="tab-pane active m-2" id="tab-0" role="tabpanel"></div>
                     <div class="tab-pane active m-2" id="tab-1" role="tabpanel">
                         <h3>Payroll Details</h3>

                     </div>
                     <div class="tab-pane active m-2" id="tab-2" role="tabpanel"></div>
                     <div class="tab-pane active m-2" id="tab-3" role="tabpanel"></div>
                     <div class="tab-pane active m-2" id="tab-4" role="tabpanel"></div>
                     <div class="tab-pane active m-2" id="tab-5" role="tabpanel"></div>
                 </div>
             </div>
         </div>
     </div>--}}
@endsection
@section('scripts')
    @include('snippets.actions.mailto')
    @include('snippets.actions.sms')
    <script src='{{ asset('assets/libs/flatpickr/flatpickr.min.js.js') }}'></script>
    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script> const EditBtn = $("#employeePersonalEditBtn"), CancelBtn = $("#employeePersonalCancelBtn"),
            UpdateProfileBtn = $("#employeePersonalBtn");
        $(function () {
            EditBtn.on('click', function () {
                CancelBtn.removeClass('d-none');
                UpdateProfileBtn.removeClass('d-none');
                EditBtn.addClass('d-none');
                $('.personal-form').prop('disabled', false);
                flatpickr("#DateOfBirth", {
                    altInput: true,
                    altFormat: "F j, Y",
                    dateFormat: "Y-m-d",
                    allowInput: true,
                    defaultDate: moment().subtract(18, 'year').format('YYYY-MM-DD'),
                    maxDate: moment().subtract(18, 'year').format('YYYY-MM-DD'),
                });
            });

            flatpickr("#DateOfBirth", {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                allowInput: true,
                defaultDate: moment().subtract(18, 'year').format('YYYY-MM-DD'),
                maxDate: moment().subtract(18, 'year').format('YYYY-MM-DD'),
            });
            CancelBtn.on('click', function () {
                CancelBtn.addClass('d-none');
                UpdateProfileBtn.addClass('d-none');
                EditBtn.removeClass('d-none');
                $('.personal-form').prop('disabled', true);
            });

            $('form#employeePersonalForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), UpdateProfileBtn, true, false, true);
            });
        });
    </script>
@endsection

