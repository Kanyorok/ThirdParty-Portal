@extends('layouts.app')

@section('title', 'Leave Reports')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">HR</a></li>
    <li class="breadcrumb-item"><a href="#">Leave Management</a></li>
@endsection

@section('content')
<div class="row">
    <!-- Key Metrics Cards -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-c-blue text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="m-0 text-white">{{ $onLeaveToday }}</h4>
                        <span>On Leave Today</span>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-clock f-30"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-c-yellow text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="m-0 text-white">{{ $pendingRequests }}</h4>
                        <span>Pending Requests</span>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hourglass-half f-30"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-c-green text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="m-0 text-white">{{ $approvedMonth }}</h4>
                        <span>Approved (This Month)</span>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle f-30"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-c-red text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="m-0 text-white">{{ $rejectedMonth }}</h4>
                        <span>Rejected (This Month)</span>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle f-30"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Charts -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Leave Type Distribution (Year to Date)</h5>
            </div>
            <div class="card-body">
                <canvas id="leaveTypeChart" style="height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Monthly Approved Leaves Trend (Last 6 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="trendChart" style="height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Detailed Report Table -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Detailed Leave Reports</h5>
                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-filter"></i> Filters
                </button>
            </div>
            <div class="collapse {{ request()->anyFilled(['status', 'employee_id']) ? 'show' : '' }}" id="filterCollapse">
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('hr.leave.reports.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach(['Pending', 'Approved', 'Rejected', 'Cancelled'] as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select select2">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->Id }}" {{ request('employee_id') == $emp->Id ? 'selected' : '' }}>
                                        {{ $emp->FirstName }} {{ $emp->LastName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Apply</button>
                            <a href="{{ route('hr.leave.reports.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="reportTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Requested On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $leave)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        {{-- Avatar could go here --}}
                                        <div>
                                            <h6 class="mb-0">{{ $leave->employee->FirstName ?? '' }} {{ $leave->employee->LastName ?? '' }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $leave->type->Name ?? '-' }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($leave->StartDate)->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($leave->EndDate)->format('M d, Y') }}
                                </td>
                                <td>{{ $leave->TotalDays }}</td>
                                <td>
                                    @if($leave->Status == 'Approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($leave->Status == 'Pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($leave->Status == 'Rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $leave->Status }}</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($leave->CreatedOn)->format('M d, Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $leaves->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- Leave Type Chart ---
        const typeCtx = document.getElementById('leaveTypeChart').getContext('2d');
        const typeData = @json($typeStats);
        
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: typeData.map(d => d.label),
                datasets: [{
                    data: typeData.map(d => d.value),
                    backgroundColor: [
                        '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', 
                        '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // --- Trend Chart ---
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendData = @json($trendStats);

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.month),
                datasets: [{
                    label: 'Approved Leaves',
                    data: trendData.map(d => d.count),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    });
</script>
@endpush
