@extends('layouts.app')

@section('title', 'New Employee')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Employee</h2>
        <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary">
            ← Back to Employees
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex align-items-center">
            <span class="me-2">➕</span>
            <h5 class="mb-0">Employee Details</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>There were some problems with your input.</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('hr.employees.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    {{-- Basic Info --}}
                    <div class="col-md-3">
                        <label class="form-label">Employee No *</label>
                        <input type="text" name="EmployeeNo" class="form-control"
                               value="{{ old('EmployeeNo') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="FirstName" class="form-control"
                               value="{{ old('FirstName') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="LastName" class="form-control"
                               value="{{ old('LastName') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Other Names</label>
                        <input type="text" name="OtherNames" class="form-control"
                               value="{{ old('OtherNames') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="Email" class="form-control"
                               value="{{ old('Email') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="Phone" class="form-control"
                               value="{{ old('Phone') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Gender</label>
                        <select name="Gender" class="form-select">
                            <option value="">Select</option>
                            @foreach(['Male','Female','Other'] as $g)
                                <option value="{{ $g }}" @selected(old('Gender') == $g)>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="DateOfBirth" class="form-control"
                               value="{{ old('DateOfBirth') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="Address" class="form-control" rows="2" placeholder="Address">{{ old('Address') }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Photo</label>
                        <input type="file" name="Photo" class="form-control">
                        <small class="text-muted">Max 5MB</small>
                    </div>

                    {{-- Org Placement --}}
                    <div class="col-md-3">
                        <label class="form-label">Branch *</label>
                        <select name="BranchID" class="form-select" required>
                            <option value="">Select Branch</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->Id }}"
                                    @selected(old('BranchID') == $branch->Id)>
                                    {{ $branch->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department *</label>
                        <select name="DepartmentID" class="form-select" required>
                            <option value="">Select Department</option>
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->Id }}"
                                    @selected(old('DepartmentID') == $dept->Id)>
                                    {{ $dept->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Grade</label>
                        <select name="GradeID" class="form-select">
                            <option value="">Select Grade</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->Id }}"
                                    @selected(old('GradeID') == $grade->Id)>
                                    {{ $grade->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Role</label>
                        <select name="RoleID" class="form-select">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}"
                                    @selected(old('RoleID') == $role->Id)>
                                    {{ $role->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Supervisor</label>
                        <select name="SupervisorID" class="form-select">
                            <option value="">Select Supervisor</option>
                            @foreach($supervisors ?? [] as $sup)
                                <option value="{{ $sup->Id }}"
                                    @selected(old('SupervisorID') == $sup->Id)>
                                    {{ $sup->FirstName }} {{ $sup->LastName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Employment --}}
                    <div class="col-md-3">
                        <label class="form-label">Employment Date</label>
                        <input type="date" name="EmploymentDate" class="form-control"
                               value="{{ old('EmploymentDate') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Employment Type</label>
                        <input type="text" name="EmploymentType" class="form-control"
                               value="{{ old('EmploymentType') }}" placeholder="Permanent, Contract...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contract Type</label>
                        <input type="text" name="ContractType" class="form-control"
                               value="{{ old('ContractType') }}">
                    </div>

                    {{-- Statutory --}}
                    <div class="col-md-3">
                        <label class="form-label">NSSF No</label>
                        <input type="text" name="NSSFNo" class="form-control"
                               value="{{ old('NSSFNo') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">NHIF / SHIF No</label>
                        <input type="text" name="NHIFNo" class="form-control"
                               value="{{ old('NHIFNo') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">KRA PIN</label>
                        <input type="text" name="KRAPIN" class="form-control"
                               value="{{ old('KRAPIN') }}">
                    </div>

                    {{-- Payroll --}}
                    <div class="col-md-3">
                        <label class="form-label">Basic Salary *</label>
                        <input type="number" step="0.01" name="BasicSalary" class="form-control"
                               value="{{ old('BasicSalary', 0) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Salary Effective From</label>
                        <input type="date" name="SalaryEffectiveFrom" class="form-control"
                               value="{{ old('SalaryEffectiveFrom') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Mode</label>
                        <select name="PaymentMode" class="form-select">
                            @foreach(['Bank','Cash','Mobile'] as $mode)
                                <option value="{{ $mode }}" @selected(old('PaymentMode','Bank') == $mode)>
                                    {{ $mode }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bank Name</label>
                        @php
                            $selectedBank = old('BankName');
                            $bankNames = collect($banks ?? [])->pluck('BankName')->all();
                        @endphp
                        <select name="BankName" class="form-select">
                            <option value="">Select Bank</option>
                            @if($selectedBank && !in_array($selectedBank, $bankNames))
                                <option value="{{ $selectedBank }}" selected>{{ $selectedBank }} (current)</option>
                            @endif
                            @foreach($banks ?? [] as $bank)
                                <option value="{{ $bank->BankName }}"
                                    @selected($selectedBank === $bank->BankName)>
                                    {{ $bank->BankName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bank Branch</label>
                        <input type="text" name="BankBranch" class="form-control"
                               value="{{ old('BankBranch') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bank Account</label>
                        <input type="text" name="BankAccount" class="form-control"
                               value="{{ old('BankAccount') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            @foreach($statusList ?? ['Pending','Active','OnHold','Dormant','Deactivated','Exited'] as $status)
                                <option value="{{ $status }}" @selected(old('Status','Pending') == $status)>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <h5>Emergency Contacts / Next of Kin</h5>
                <div class="row g-3">
                    @for($i=0; $i<2; $i++)
                        <div class="col-md-4">
                            <label class="form-label">Name</label>
                            <input type="text" name="contact_name[]" class="form-control" value="{{ old('contact_name.'.$i) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Relation</label>
                            <input type="text" name="contact_relation[]" class="form-control" value="{{ old('contact_relation.'.$i) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="contact_phone[]" class="form-control" value="{{ old('contact_phone.'.$i) }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-center gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="contact_is_next_of_kin[{{ $i }}]" value="1" @checked(old('contact_is_next_of_kin.'.$i))>
                                <label class="form-check-label">Next of Kin</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="contact_is_primary[{{ $i }}]" value="1" @checked(old('contact_is_primary.'.$i))>
                                <label class="form-check-label">Primary</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="contact_email[]" class="form-control" value="{{ old('contact_email.'.$i) }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="contact_is_emergency[{{ $i }}]" value="1" @checked(old('contact_is_emergency.'.$i, true))>
                                <label class="form-check-label">Emergency Contact</label>
                            </div>
                        </div>
                    @endfor
                </div>

                <hr class="my-4">
                <h5>Attach Documents</h5>
                <div class="row g-3">
                    @for($i=0; $i<3; $i++)
                        <div class="col-md-4">
                            <label class="form-label">File</label>
                            <input type="file" name="documents[]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="documents_category[]" class="form-control" placeholder="ID, Contract, Certificate" value="{{ old('documents_category.'.$i) }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Description</label>
                            <input type="text" name="documents_description[]" class="form-control" value="{{ old('documents_description.'.$i) }}">
                        </div>
                    @endfor
                    <div class="col-12">
                        <small class="text-muted">Max 5MB per file. Stored under employee-docs.</small>
                    </div>
                </div>

                <hr class="my-4">
                <h5>Education</h5>
                <div class="row g-3">
                    @for($i=0; $i<2; $i++)
                        <div class="col-md-3">
                            <label class="form-label">Level</label>
                            <input type="text" name="edu_level[]" class="form-control" value="{{ old('edu_level.'.$i) }}" placeholder="Bachelor, Diploma">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Institution</label>
                            <input type="text" name="edu_institution[]" class="form-control" value="{{ old('edu_institution.'.$i) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Course</label>
                            <input type="text" name="edu_course[]" class="form-control" value="{{ old('edu_course.'.$i) }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">From</label>
                            <input type="text" name="edu_year_from[]" class="form-control" value="{{ old('edu_year_from.'.$i) }}" placeholder="YYYY">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">To</label>
                            <input type="text" name="edu_year_to[]" class="form-control" value="{{ old('edu_year_to.'.$i) }}" placeholder="YYYY">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Grade</label>
                            <input type="text" name="edu_grade[]" class="form-control" value="{{ old('edu_grade.'.$i) }}">
                        </div>
                    @endfor
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Save Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
