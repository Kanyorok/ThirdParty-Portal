@extends('layouts.app')

@section('title', 'Attendance Logs')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Attendance Logs</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Attendance Logs</li>
            </ol>
        </nav>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <div class="d-flex align-items-center">
                <span class="me-2">📄</span>
                <h5 class="mb-0">Raw Clock Events</h5>
            </div>
        </div>

        <div class="card-body">
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
                    <label class="form-label">Channel</label>
                    <select name="channel" class="form-select">
                        <option value="">All Channels</option>
                        @foreach(['AndroidApp','Biometric','WebManual'] as $channel)
                            <option value="{{ $channel }}" @selected(request('channel') == $channel)>
                                {{ $channel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Per Page</label>
                    <select name="per_page" class="form-select">
                        @foreach([50,100,200] as $size)
                            <option value="{{ $size }}" @selected(request('per_page',100) == $size)>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill" type="submit">Apply Filters</button>
                    <a href="{{ route('hr.attendance.logs') }}" class="btn btn-outline-secondary flex-fill">
                        Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                @if($logs->count())
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date/Time</th>
                            <th>Employee No</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Channel</th>
                            <th>Device</th>
                            <th>Processed</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($logs as $index => $log)
                            <tr>
                                <td>{{ $logs->firstItem() + $index }}</td>
                                <td>{{ $log->LogTime }}</td>
                                <td>{{ $log->employee->EmployeeNo ?? '-' }}</td>
                                <td>{{ optional($log->employee)->FirstName }} {{ optional($log->employee)->LastName }}</td>
                                <td>{{ $log->LogType }}</td>
                                <td>{{ $log->Channel ?? '-' }}</td>
                                <td>{{ $log->DeviceID ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->IsProcessed ? 'success' : 'warning' }}">
                                        {{ $log->IsProcessed ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $logs->withQueryString()->links() }}
                    </div>
                @else
                    <div class="py-5 text-center text-muted">
                        <div class="mb-2 fs-1">ℹ️</div>
                        <p class="mb-3">No attendance logs found for the selected filters.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
