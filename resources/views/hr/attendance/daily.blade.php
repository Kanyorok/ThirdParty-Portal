@extends('layouts.app')

@section('title', 'Daily Attendance')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Daily Attendance</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Daily Attendance</li>
            </ol>
        </nav>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <div class="d-flex align-items-center">
                <span class="me-2">🕒</span>
                <h5 class="mb-0">Attendance Summary</h5>
            </div>
        </div>

        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-3">
                    <label class="form-label">Employee No</label>
                    <input type="text" name="employee_no" class="form-control"
                           value="{{ request('employee_no') }}" placeholder="Search employee no">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach(['Present','Absent','OnLeave','Late','EarlyExit','HalfDay','OffDay'] as $status)
                            <option value="{{ $status }}" @selected(request('status') == $status)>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Per Page</label>
                    <select name="per_page" class="form-select">
                        @foreach([25,50,100] as $size)
                            <option value="{{ $size }}" @selected(request('per_page',50) == $size)>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill" type="submit">Apply Filters</button>
                    <a href="{{ route('hr.attendance.daily') }}" class="btn btn-outline-secondary flex-fill">
                        Reset
                    </a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                @if($records->count())
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Employee No</th>
                            <th>Employee</th>
                            <th>Shift</th>
                            <th>First In</th>
                            <th>Last Out</th>
                            <th>Total Hrs</th>
                            <th>Overtime</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($records as $index => $row)
                            <tr>
                                <td>{{ $records->firstItem() + $index }}</td>
                                <td>{{ $row->WorkDate->format('Y-m-d') }}</td>
                                <td>{{ $row->employee->EmployeeNo ?? '-' }}</td>
                                <td>{{ optional($row->employee)->FirstName }} {{ optional($row->employee)->LastName }}</td>
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
                    <div class="mt-3">
                        {{ $records->withQueryString()->links() }}
                    </div>
                @else
                    <div class="py-5 text-center text-muted">
                        <div class="mb-2 fs-1">ℹ️</div>
                        <p class="mb-3">No attendance records found for the selected filters.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
