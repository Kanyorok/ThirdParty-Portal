@extends('layouts.app')

@section('title', 'Payroll Run')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Payroll Run #{{ $run->Id }}</h2>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.runs.bankfile', $run->Id) }}">CBS File (CSV)</a>
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.runs.eft', $run->Id) }}">EFT XML</a>
            <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.runs.index') }}">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap gap-4">
            <div>
                <div class="text-muted small">Cycle</div>
                <div class="h6 mb-0">{{ $run->cycle?->Month }}/{{ $run->cycle?->Year }}</div>
            </div>
            <div>
                <div class="text-muted small">Status</div>
                <div class="h6 mb-0">{{ $run->Status }}</div>
            </div>
            <div>
                <div class="text-muted small">Finance Journal</div>
                <div class="h6 mb-0">
                    @if($run->FinanceJournalEntryID)
                        #{{ $run->FinanceJournalEntryID }} ({{ $run->FinancePostingMode ?? 'posted' }})
                    @else
                        Not posted
                    @endif
                </div>
            </div>
            <div>
                <div class="text-muted small">Generated On</div>
                <div>{{ $run->GeneratedOn ? $run->GeneratedOn->format('Y-m-d H:i') : '-' }}</div>
            </div>
            @if($run->ApprovedOn)
                <div>
                    <div class="text-muted small">Approved On</div>
                    <div>{{ $run->ApprovedOn->format('Y-m-d H:i') }}</div>
                </div>
            @elseif($run->RejectedOn)
                <div>
                    <div class="text-muted small">Rejected On</div>
                    <div>{{ $run->RejectedOn->format('Y-m-d H:i') }}</div>
                </div>
            @endif
        </div>
        @if($run->RejectedOn && $run->RejectionReason)
            <div class="card-footer bg-white">
                <span class="text-muted">Rejection Reason:</span> {{ $run->RejectionReason }}
            </div>
        @endif
    </div>

    @if($run->Status !== 'Approved')
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white">Approval</div>
            <div class="card-body d-flex flex-wrap gap-3">
                <form method="POST" action="{{ route('hr.payroll.runs.approve', $run->Id) }}">
                    @csrf
                    <button class="btn btn-success" type="submit">Approve Run</button>
                </form>
                <form method="POST" action="{{ route('hr.payroll.runs.reject', $run->Id) }}" class="d-flex flex-wrap gap-2 align-items-center">
                    @csrf
                    <input name="RejectionReason" class="form-control" style="min-width: 240px;" placeholder="Rejection reason (optional)">
                    <button class="btn btn-outline-danger" type="submit">Reject Run</button>
                </form>
            </div>
        </div>
    @endif

    @if(!$run->FinanceJournalEntryID)
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Post to Finance</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('hr.payroll.runs.postFinance', $run->Id) }}" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label">Posting Mode *</label>
                        <select name="posting_mode" class="form-select" required>
                            <option value="summary" @selected(old('posting_mode', 'summary') === 'summary')>Summary (consolidated)</option>
                            <option value="branch_department" @selected(old('posting_mode') === 'branch_department')>Granular (by Branch/Department)</option>
                        </select>
                        <div class="form-text">Choose how detailed you want the journal lines to be.</div>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary" type="submit">Create Finance Journal (Draft)</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white">Reports</div>
        <div class="card-body d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.reports.summary', $run->Id) }}">Company Summary</a>
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.reports.master', $run->Id) }}">Master Register</a>
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.reports.branches', $run->Id) }}">Branch Summary</a>
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.reports.departments', $run->Id) }}">Department Summary</a>
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.returns.index', $run->Id) }}">Statutory Returns</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">Employees</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Basic</th>
                        <th>Allowances</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                        <th class="text-end">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($run->lines as $line)
                        @php
                            $empAllowances = $allowancesByEmployee[$line->EmployeeID] ?? collect();
                            $empDeductions = $deductionsByEmployee[$line->EmployeeID] ?? collect();
                            $detailId = 'payrollLine_' . $line->Id;
                        @endphp
                        <tr>
                            <td>{{ $line->employee?->FirstName }} {{ $line->employee?->LastName }}</td>
                            <td>{{ number_format($line->BasicSalary, 2) }}</td>
                            <td>{{ number_format($line->TotalAllowances, 2) }}</td>
                            <td>{{ number_format($line->TotalDeductions, 2) }}</td>
                            <td>{{ number_format($line->NetPay, 2) }}</td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap justify-content-end gap-1">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.payroll.runs.payslip', [$run->Id, $line->EmployeeID]) }}">Payslip</a>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.payroll.runs.p9', [$run->Id, $line->EmployeeID]) }}">P9</a>
                                    @if($run->Status !== 'Approved')
                                        <form method="POST" action="{{ route('hr.payroll.runs.recalc', [$run->Id, $line->EmployeeID]) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" type="submit">Recalc</button>
                                        </form>
                                    @endif
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $detailId }}">
                                        Breakdown
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="collapse" id="{{ $detailId }}">
                            <td colspan="6">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="fw-semibold mb-2">Allowances</div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Taxable</th>
                                                        <th class="text-end">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($empAllowances as $row)
                                                        <tr>
                                                            <td>{{ $row->Name }}</td>
                                                            <td>{{ $row->IsTaxable ? 'Yes' : 'No' }}</td>
                                                            <td class="text-end">{{ number_format($row->Amount, 2) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="3" class="text-muted">No allowances for this period.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="fw-semibold mb-2">Deductions</div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th class="text-end">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($empDeductions as $row)
                                                        <tr>
                                                            <td>{{ $row->Name }}</td>
                                                            <td class="text-end">{{ number_format($row->Amount, 2) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="2" class="text-muted">No deductions for this period.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="fw-semibold mb-2">Adjustments</div>
                                        <table class="table table-sm mb-0">
                                            <tbody>
                                                <tr>
                                                    <td>Overtime</td>
                                                    <td class="text-end">{{ number_format($line->Overtime ?? 0, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Attendance</td>
                                                    <td class="text-end">{{ number_format($line->AttendanceAdjustments ?? 0, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Leave (Unpaid)</td>
                                                    <td class="text-end">{{ number_format($line->LeaveAdjustments ?? 0, 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No lines yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
