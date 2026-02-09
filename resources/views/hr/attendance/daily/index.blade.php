@extends('layouts.app')

@section('title', 'Daily Attendance Summary')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Daily Attendance Summary</h2>
    </div>

    <form class="row g-3 mb-3" method="GET">
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Employee</label>
            <select name="employee_id" class="form-select">
                <option value="">All</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->Id }}" @selected(request('employee_id') == $emp->Id)>{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Any</option>
                @foreach(['Present','Absent','On Leave','Late','Early Exit','Half-Day','Off-Day'] as $st)
                    <option value="{{ $st }}" @selected(request('status') == $st)>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Shift</th>
                            <th>Arrived Work</th>
                            <th>Left Work</th>
                            <th>Hours</th>
                            <th>Overtime</th>
                            <th>Status</th>
                            <th>Late</th>
                            <th>Early Exit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailies as $row)
                            <tr>
                                <td>{{ $row->WorkDate }}</td>
                                <td>{{ $row->employee->FirstName ?? '' }} {{ $row->employee->LastName ?? '' }}</td>
                                <td>{{ $row->shift->Name ?? '-' }}</td>
                                <td>{{ optional($row->FirstInTime)->format('H:i') ?? '-' }}</td>
                                <td>{{ optional($row->LastOutTime)->format('H:i') ?? '-' }}</td>
                                <td>{{ $row->TotalHours ? $row->TotalHours . ' hrs' : '-' }}</td>
                                <td>{{ $row->OvertimeHours ? $row->OvertimeHours . ' hrs' : '-' }}</td>
                                <td>{{ $row->Status }}</td>
                                <td>{{ $row->LateMinutes ? abs($row->LateMinutes) . ' min' : '-' }}</td>
                                <td>{{ $row->EarlyExitMinutes ? abs($row->EarlyExitMinutes) . ' min' : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted">No records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $dailies->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
