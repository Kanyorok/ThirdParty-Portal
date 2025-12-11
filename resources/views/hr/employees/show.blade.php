{{-- resources/views/hr/employees/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Employee Profile')
@php use Illuminate\Support\Facades\Storage; @endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Employee Profile</h2>
        <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary">
            ← Back to Employees
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">
                    {{ $employee->FirstName }} {{ $employee->LastName }}
                    <span class="text-muted">({{ $employee->EmployeeNo }})</span>
                </h5>
                <small class="text-muted">
                    {{ $employee->role->Name ?? 'No Role' }} —
                    {{ $employee->department->Name ?? 'No Department' }},
                    {{ $employee->branch->Name ?? 'No Branch' }}
                </small>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-{{ $employee->Status === 'Active' ? 'success' : 'secondary' }} align-self-start">
                    {{ $employee->Status }}
                </span>
                @if($employee->StatusReason)
                    <span class="text-muted small">{{ $employee->StatusReason }}</span>
                @endif
                <div class="btn-group">
                    <a href="{{ route('hr.employees.edit', $employee->Id) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                    <a href="{{ route('hr.employees.status.edit', $employee->Id) }}" class="btn btn-outline-info btn-sm">Status</a>
                    @if($employee->IsActive)
                        <form action="{{ route('hr.employees.destroy', $employee->Id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deactivate this employee?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm" type="submit">Deactivate</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- Top-level quick info --}}
            <div class="row g-3 align-items-center">
                <div class="col-md-2 text-center">
                    @if($employee->PhotoPath)
                        <img src="{{ Storage::url($employee->PhotoPath) }}" alt="Photo" class="img-thumbnail mb-2" style="max-height:120px;">
                    @else
                        <div class="border rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:120px;height:120px;background:#f5f5f5;">No Photo</div>
                    @endif
                </div>
                <div class="col-md-10">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Employment Date</label>
                            <div>{{ optional($employee->EmploymentDate)->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Last Status Change</label>
                            <div>{{ optional($employee->StatusChangedOn)->format('d M Y H:i') ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Employment Type</label>
                            <div>{{ $employee->EmploymentType ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Grade</label>
                            <div>{{ $employee->grade->Name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Supervisor</label>
                            <div>
                                @if($employee->supervisor)
                                    {{ $employee->supervisor->FirstName }} {{ $employee->supervisor->LastName }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" id="employeeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="personal-tab" data-bs-toggle="tab"
                            data-bs-target="#personal" type="button" role="tab">
                        Personal & Employment
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="payroll-tab" data-bs-toggle="tab"
                            data-bs-target="#payroll" type="button" role="tab">
                        Payroll
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="attendance-tab" data-bs-toggle="tab"
                            data-bs-target="#attendance" type="button" role="tab">
                        Attendance
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="leave-tab" data-bs-toggle="tab"
                            data-bs-target="#leave" type="button" role="tab">
                        Leave
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="kpi-tab" data-bs-toggle="tab"
                            data-bs-target="#kpi" type="button" role="tab">
                        KPI & Appraisals
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cases-tab" data-bs-toggle="tab"
                            data-bs-target="#cases" type="button" role="tab">
                        Cases / Discipline
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="docs-tab" data-bs-toggle="tab"
                            data-bs-target="#docs" type="button" role="tab">
                        Documents
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="employeeTabsContent">
                {{-- Personal & Employment --}}
                <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Email</label>
                            <div>{{ $employee->Email ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Phone</label>
                            <div>{{ $employee->Phone ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Branch</label>
                            <div>{{ $employee->branch->Name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Department</label>
                            <div>{{ $employee->department->Name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Gender</label>
                            <div>{{ $employee->Gender ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Date of Birth</label>
                            <div>{{ optional($employee->DateOfBirth)->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted d-block mb-1">Address</label>
                            <div>{{ $employee->Address ?? '-' }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Contract Type</label>
                            <div>{{ $employee->ContractType ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">NSSF No</label>
                            <div>{{ $employee->NSSFNo ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">NHIF / SHIF No</label>
                            <div>{{ $employee->NHIFNo ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">KRA PIN</label>
                            <div>{{ $employee->KRAPIN ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-12">
                            <label class="text-muted d-block mb-1">Contacts & Next of Kin</label>
                            @if($employee->contacts && $employee->contacts->count())
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Relation</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Next of Kin</th>
                                            <th>Primary</th>
                                            <th>Emergency</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($employee->contacts as $contact)
                                            <tr>
                                                <td>{{ $contact->Name }}</td>
                                                <td>{{ $contact->Relation ?? '-' }}</td>
                                                <td>{{ $contact->Phone ?? '-' }}</td>
                                                <td>{{ $contact->Email ?? '-' }}</td>
                                                <td>{{ $contact->IsNextOfKin ? 'Yes' : 'No' }}</td>
                                                <td>{{ $contact->IsPrimary ? 'Yes' : 'No' }}</td>
                                                <td>{{ $contact->IsEmergency ? 'Yes' : 'No' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <span class="text-muted">No contacts captured yet.</span>
                            @endif
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-12">
                            <label class="text-muted d-block mb-1">Education</label>
                            @if($employee->education && $employee->education->count())
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Level</th>
                                            <th>Institution</th>
                                            <th>Course</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Grade</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($employee->education as $edu)
                                            <tr>
                                                <td>{{ $edu->Level ?? '-' }}</td>
                                                <td>{{ $edu->Institution ?? '-' }}</td>
                                                <td>{{ $edu->Course ?? '-' }}</td>
                                                <td>{{ $edu->YearFrom ?? '-' }}</td>
                                                <td>{{ $edu->YearTo ?? '-' }}</td>
                                                <td>{{ $edu->Grade ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <span class="text-muted">No education records yet.</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Payroll --}}
                <div class="tab-pane fade" id="payroll" role="tabpanel" aria-labelledby="payroll-tab">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Basic Salary</label>
                            <div>{{ number_format($employee->BasicSalary, 2) }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Payment Mode</label>
                            <div>{{ $employee->PaymentMode ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Bank Name</label>
                            <div>{{ $employee->BankName ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Bank Branch</label>
                            <div>{{ $employee->BankBranch ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted d-block mb-1">Bank Account</label>
                            <div>{{ $employee->BankAccount ?? '-' }}</div>
                        </div>

                        {{-- Placeholder: later you can inject detailed payroll history --}}
                        <div class="col-12 mt-3">
                            <div class="alert alert-light border">
                                Payroll history, allowances, deductions, staff loans, etc.
                                will appear here once those modules are wired to HR.
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <label class="text-muted d-block mb-2">Salary History</label>
                            @if($employee->salaryHistory && $employee->salaryHistory->count())
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Effective From</th>
                                            <th>Basic Salary</th>
                                            <th>Notes</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($employee->salaryHistory as $row)
                                            <tr>
                                                <td>{{ optional($row->EffectiveFrom)->format('Y-m-d') ?? '-' }}</td>
                                                <td>{{ number_format($row->BasicSalary, 2) }}</td>
                                                <td>{{ $row->Notes ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <span class="text-muted">No salary history recorded yet.</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Attendance --}}
                <div class="tab-pane fade" id="attendance" role="tabpanel" aria-labelledby="attendance-tab">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Last 30 Days</label>
                            <div>{{ $attendanceSummary->days ?? 0 }} days recorded</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Present</label>
                            <div>{{ $attendanceSummary->present_days ?? 0 }} days</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Other Status</label>
                            <div>{{ $attendanceSummary->other_days ?? 0 }} days</div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted d-block mb-1">Overtime (hrs)</label>
                            <div>{{ number_format($attendanceSummary->overtime_hours ?? 0, 2) }}</div>
                        </div>
                    </div>
                    <div class="alert alert-light border mb-3 mt-3">
                        Recent attendance entries.
                    </div>

                    @php
                        $recentAttendance = $employee->attendanceDaily()
                            ->orderByDesc('WorkDate')
                            ->limit(10)
                            ->get();
                    @endphp

                    @if($recentAttendance->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Shift</th>
                                    <th>First In</th>
                                    <th>Last Out</th>
                                    <th>Total Hrs</th>
                                    <th>Overtime</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($recentAttendance as $row)
                                    <tr>
                                        <td>{{ $row->WorkDate->format('Y-m-d') }}</td>
                                        <td>{{ $row->shift->Name ?? '-' }}</td>
                                        <td>{{ optional($row->FirstInTime)->format('H:i') ?? '-' }}</td>
                                        <td>{{ optional($row->LastOutTime)->format('H:i') ?? '-' }}</td>
                                        <td>{{ $row->TotalHours ?? '-' }}</td>
                                        <td>{{ $row->OvertimeHours ?? '-' }}</td>
                                        <td>{{ $row->Status }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No attendance records available yet.</p>
                    @endif
                </div>

                {{-- Leave --}}
                <div class="tab-pane fade" id="leave" role="tabpanel" aria-labelledby="leave-tab">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-muted d-block mb-1">Leave Balances</label>
                            @php
                                $leaveBalances = \App\Models\HR\LeaveBalance::with('type')
                                    ->where('EmployeeID', $employee->Id)->get();
                                $recentLeaves = \App\Models\HR\LeaveRequest::with('type')
                                    ->where('EmployeeID', $employee->Id)
                                    ->orderByDesc('Id')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            @if($leaveBalances->count())
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>Entitlement</th>
                                            <th>Accrued</th>
                                            <th>Taken</th>
                                            <th>Balance</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($leaveBalances as $bal)
                                            <tr>
                                                <td>{{ $bal->type->Name ?? '-' }}</td>
                                                <td>{{ $bal->Entitlement }}</td>
                                                <td>{{ $bal->Accrued }}</td>
                                                <td>{{ $bal->Taken }}</td>
                                                <td>{{ $bal->Balance }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-light border">No leave balances recorded.</div>
                            @endif
                        </div>

                        <div class="col-12">
                            <label class="text-muted d-block mb-1">Recent Leave Requests</label>
                            @if($recentLeaves->count())
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Days</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($recentLeaves as $req)
                                            <tr>
                                                <td>{{ $req->type->Name ?? '-' }}</td>
                                                <td>{{ $req->StartDate }}</td>
                                                <td>{{ $req->EndDate }}</td>
                                                <td>{{ $req->TotalDays }}</td>
                                                <td>{{ $req->Status }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-light border">No leave requests yet.</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- KPI & Appraisals --}}
                <div class="tab-pane fade" id="kpi" role="tabpanel" aria-labelledby="kpi-tab">
                    <div class="alert alert-light border">
                        KPI goals, submissions, and appraisal scores will appear here once linked to the KPI module.
                    </div>
                </div>

                {{-- Cases / Discipline --}}
                <div class="tab-pane fade" id="cases" role="tabpanel" aria-labelledby="cases-tab">
                    <div class="alert alert-light border">
                        Disciplinary cases and statuses will be shown here when the cases module is connected.
                    </div>
                </div>

                {{-- Documents --}}
                <div class="tab-pane fade" id="docs" role="tabpanel" aria-labelledby="docs-tab">
                    @if($employee->documents && $employee->documents->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                <tr>
                                    <th>File</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Uploaded On</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($employee->documents as $doc)
                                    <tr>
                                        <td><a href="{{ Storage::url($doc->FilePath) }}" target="_blank">{{ $doc->FileName }}</a></td>
                                        <td>{{ $doc->Category ?? '-' }}</td>
                                        <td>{{ $doc->Description ?? '-' }}</td>
                                        <td>{{ optional($doc->UploadedOn)->format('Y-m-d H:i') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-light border">No documents uploaded yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
