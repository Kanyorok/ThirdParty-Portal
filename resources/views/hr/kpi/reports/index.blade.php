@extends('layouts.app')

@section('title', 'KPI Reports')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">KPI Reports</h2>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Group By</label>
                    <select name="group" class="form-select">
                        <option value="employee" @selected($group === 'employee')>Individual</option>
                        <option value="department" @selected($group === 'department')>Department</option>
                        <option value="branch" @selected($group === 'branch')>Branch</option>
                        <option value="org" @selected($group === 'org')>Organization</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period_id" class="form-select">
                        <option value="">All</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->Id }}" @selected((int)$periodId === $period->Id)>{{ $period->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Approved" @selected($status === 'Approved')>Approved</option>
                        <option value="Submitted" @selected($status === 'Submitted')>Submitted</option>
                        <option value="Draft" @selected($status === 'Draft')>Draft</option>
                        <option value="Rejected" @selected($status === 'Rejected')>Rejected</option>
                        <option value="">All</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary" type="submit">Run</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        @if($group === 'employee')
                            <th>Employee</th>
                            <th>Branch</th>
                            <th>Department</th>
                        @elseif($group === 'department')
                            <th>Department</th>
                        @elseif($group === 'branch')
                            <th>Branch</th>
                        @else
                            <th>Organization</th>
                        @endif
                        <th class="text-end">Appraisals</th>
                        <th class="text-end">Avg Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            @if($group === 'employee')
                                <td>{{ $row->FirstName }} {{ $row->LastName }} ({{ $row->EmployeeNo }})</td>
                                <td>{{ $row->BranchName ?? '-' }}</td>
                                <td>{{ $row->DepartmentName ?? '-' }}</td>
                            @elseif($group === 'department')
                                <td>{{ $row->DepartmentName ?? '-' }}</td>
                            @elseif($group === 'branch')
                                <td>{{ $row->BranchName ?? '-' }}</td>
                            @else
                                <td>Organization</td>
                            @endif
                            <td class="text-end">{{ number_format((float)($row->Appraisals ?? 0), 0) }}</td>
                            <td class="text-end">{{ number_format((float)($row->AvgScore ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
