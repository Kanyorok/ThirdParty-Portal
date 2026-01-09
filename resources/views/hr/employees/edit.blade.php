{{-- resources/views/hr/employees/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Employee</h2>
        <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary">
            ← Back to Employees
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex align-items-center">
            <span class="me-2">✏️</span>
            <h5 class="mb-0">Employee Details — {{ $employee->EmployeeNo }}</h5>
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

            <form method="POST" action="{{ route('hr.employees.update', $employee->Id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    {{-- Basic Info --}}
                    <div class="col-md-3">
                        <label class="form-label">Employee No *</label>
                        <input type="text" name="EmployeeNo" class="form-control"
                               value="{{ $employee->EmployeeNo }}" disabled>
                        <div class="form-text">Employee No cannot be changed.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="FirstName" class="form-control"
                               value="{{ old('FirstName', $employee->FirstName) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="LastName" class="form-control"
                               value="{{ old('LastName', $employee->LastName) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Other Names</label>
                        <input type="text" name="OtherNames" class="form-control"
                               value="{{ old('OtherNames', $employee->OtherNames) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="Email" class="form-control"
                               value="{{ old('Email', $employee->Email) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="Phone" class="form-control"
                               value="{{ old('Phone', $employee->Phone) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Gender</label>
                        <select name="Gender" class="form-select">
                            <option value="">Select</option>
                            @foreach(['Male','Female','Other'] as $g)
                                <option value="{{ $g }}" @selected(old('Gender', $employee->Gender) == $g)>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="DateOfBirth" class="form-control"
                               value="{{ old('DateOfBirth', optional($employee->DateOfBirth)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="Address" class="form-control" rows="2" placeholder="Address">{{ old('Address', $employee->Address) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Photo</label>
                        <input type="file" name="Photo" class="form-control">
                        <small class="text-muted">Max 5MB. Current: {{ $employee->PhotoPath ? 'Uploaded' : 'None' }}</small>
                    </div>

                    {{-- Org Placement --}}
                    <div class="col-md-3">
                        <label class="form-label">Branch *</label>
                        <select name="BranchID" class="form-select" required>
                            <option value="">Select Branch</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->Id }}"
                                        @selected(old('BranchID', $employee->BranchID) == $branch->Id)>
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
                                        @selected(old('DepartmentID', $employee->DepartmentID) == $dept->Id)>
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
                                        @selected(old('GradeID', $employee->GradeID) == $grade->Id)>
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
                                        @selected(old('RoleID', $employee->RoleID) == $role->Id)>
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
                                        @selected(old('SupervisorID', $employee->SupervisorID) == $sup->Id)>
                                    {{ $sup->FirstName }} {{ $sup->LastName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Employment --}}
                    <div class="col-md-3">
                        <label class="form-label">Employment Date</label>
                        <input type="date" name="EmploymentDate" class="form-control"
                               value="{{ old('EmploymentDate', optional($employee->EmploymentDate)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Employment Type</label>
                        <input type="text" name="EmploymentType" class="form-control"
                               value="{{ old('EmploymentType', $employee->EmploymentType) }}"
                               placeholder="Permanent, Contract...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contract Type</label>
                        <input type="text" name="ContractType" class="form-control"
                               value="{{ old('ContractType', $employee->ContractType) }}">
                    </div>

                    {{-- Statutory --}}
                    <div class="col-md-3">
                        <label class="form-label">NSSF No</label>
                        <input type="text" name="NSSFNo" class="form-control"
                               value="{{ old('NSSFNo', $employee->NSSFNo) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">NHIF / SHIF No</label>
                        <input type="text" name="NHIFNo" class="form-control"
                               value="{{ old('NHIFNo', $employee->NHIFNo) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">KRA PIN</label>
                        <input type="text" name="KRAPIN" class="form-control"
                               value="{{ old('KRAPIN', $employee->KRAPIN) }}">
                    </div>

                    {{-- Payroll --}}
                    <div class="col-md-3">
                        <label class="form-label">Basic Salary *</label>
                        <input type="number" step="0.01" name="BasicSalary" class="form-control"
                               value="{{ old('BasicSalary', $employee->BasicSalary) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Salary Effective From (for change)</label>
                        <input type="date" name="SalaryEffectiveFrom" class="form-control"
                               value="{{ old('SalaryEffectiveFrom') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Mode</label>
                        <select name="PaymentMode" class="form-select">
                            @foreach(['Bank','Cash','Mobile'] as $mode)
                                <option value="{{ $mode }}"
                                        @selected(old('PaymentMode', $employee->PaymentMode) == $mode)>
                                    {{ $mode }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bank Name</label>
                        @php
                            $selectedBankId = old('BankID', $employee->BankID);
                        @endphp
                        <select name="BankID" id="BankID" class="form-select">
                            <option value="">Select Bank</option>
                            @foreach($banks ?? [] as $bank)
                                <option value="{{ $bank->BankID }}"
                                        @selected($selectedBankId == $bank->BankID)>
                                    {{ $bank->BankName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bank Branch</label>
                        @php
                            $selectedBranchId = old('BankBranchID', $employee->BankBranchID);
                        @endphp
                        <select name="BankBranchID" id="BankBranchID" class="form-select">
                            <option value="">Select Branch</option>
                            @foreach($bankBranches ?? [] as $branch)
                                <option value="{{ $branch->BranchID }}" data-bank-id="{{ $branch->BankID }}"
                                        @selected($selectedBranchId == $branch->BranchID)>
                                    {{ $branch->BranchName }}@if($branch->BranchCode) ({{ $branch->BranchCode }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bank Account</label>
                        <input type="text" name="BankAccount" class="form-control"
                               value="{{ old('BankAccount', $employee->BankAccount) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            @foreach($statusList ?? ['Pending','Active','OnHold','Dormant','Deactivated','Exited'] as $status)
                                <option value="{{ $status }}"
                                        @selected(old('Status', $employee->Status) == $status)>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <h5>Emergency Contacts / Next of Kin</h5>
                <div class="row g-3">
                    @php
                        $contactData = old('contact_name') ? collect(old('contact_name'))->map(function($name,$idx){
                            return [
                                'Name' => $name,
                                'Relation' => old('contact_relation.'.$idx),
                                'Phone' => old('contact_phone.'.$idx),
                                'Email' => old('contact_email.'.$idx),
                                'IsNextOfKin' => old('contact_is_next_of_kin.'.$idx),
                                'IsPrimary' => old('contact_is_primary.'.$idx),
                                'IsEmergency' => old('contact_is_emergency.'.$idx, true),
                            ];
                        }) : ($employee->contacts ?? collect());
                    @endphp
                    @for($i=0; $i<2; $i++)
                        @php $contact = $contactData[$i] ?? null; @endphp
                        <div class="col-md-4">
                            <label class="form-label">Name</label>
                            <input type="text" name="contact_name[]" class="form-control" value="{{ $contact['Name'] ?? $contact->Name ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Relation</label>
                            <input type="text" name="contact_relation[]" class="form-control" value="{{ $contact['Relation'] ?? $contact->Relation ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="contact_phone[]" class="form-control" value="{{ $contact['Phone'] ?? $contact->Phone ?? '' }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-center gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="contact_is_next_of_kin[{{ $i }}]" value="1"
                                       @checked($contact['IsNextOfKin'] ?? $contact->IsNextOfKin ?? false)>
                                <label class="form-check-label">Next of Kin</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="contact_is_primary[{{ $i }}]" value="1"
                                       @checked($contact['IsPrimary'] ?? $contact->IsPrimary ?? false)>
                                <label class="form-check-label">Primary</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="contact_email[]" class="form-control" value="{{ $contact['Email'] ?? $contact->Email ?? '' }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="contact_is_emergency[{{ $i }}]" value="1"
                                       @checked($contact['IsEmergency'] ?? $contact->IsEmergency ?? true)>
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
                        <small class="text-muted">Existing documents stay intact; add new files to append.</small>
                    </div>
                </div>

                <hr class="my-4">
                <h5>Education</h5>
                <div class="row g-3">
                    @php
                        $eduData = old('edu_level') ? collect(old('edu_level'))->map(function($lvl,$idx){
                            return [
                                'Level' => $lvl,
                                'Institution' => old('edu_institution.'.$idx),
                                'Course' => old('edu_course.'.$idx),
                                'YearFrom' => old('edu_year_from.'.$idx),
                                'YearTo' => old('edu_year_to.'.$idx),
                                'Grade' => old('edu_grade.'.$idx),
                            ];
                        }) : ($employee->education ?? collect());
                    @endphp
                    @for($i=0; $i<2; $i++)
                        @php $edu = $eduData[$i] ?? null; @endphp
                        <div class="col-md-3">
                            <label class="form-label">Level</label>
                            <input type="text" name="edu_level[]" class="form-control" value="{{ $edu['Level'] ?? $edu->Level ?? '' }}" placeholder="Bachelor, Diploma">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Institution</label>
                            <input type="text" name="edu_institution[]" class="form-control" value="{{ $edu['Institution'] ?? $edu->Institution ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Course</label>
                            <input type="text" name="edu_course[]" class="form-control" value="{{ $edu['Course'] ?? $edu->Course ?? '' }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">From</label>
                            <input type="text" name="edu_year_from[]" class="form-control" value="{{ $edu['YearFrom'] ?? $edu->YearFrom ?? '' }}" placeholder="YYYY">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">To</label>
                            <input type="text" name="edu_year_to[]" class="form-control" value="{{ $edu['YearTo'] ?? $edu->YearTo ?? '' }}" placeholder="YYYY">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Grade</label>
                            <input type="text" name="edu_grade[]" class="form-control" value="{{ $edu['Grade'] ?? $edu->Grade ?? '' }}">
                        </div>
                    @endfor
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bankSelect = document.getElementById('BankID');
        const branchSelect = document.getElementById('BankBranchID');
        if (!bankSelect || !branchSelect) return;

        const allBranchOptions = Array.from(branchSelect.options);

        const filterBranches = () => {
            const bankId = bankSelect.value;
            branchSelect.innerHTML = '';
            allBranchOptions.forEach((opt) => {
                if (!opt.value) {
                    branchSelect.appendChild(opt);
                    return;
                }
                if (!bankId || opt.getAttribute('data-bank-id') === bankId) {
                    branchSelect.appendChild(opt);
                }
            });
        };

        bankSelect.addEventListener('change', () => {
            filterBranches();
            branchSelect.value = '';
        });

        filterBranches();
    });
</script>
@endpush
