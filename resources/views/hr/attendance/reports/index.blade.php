@extends('layouts.app')

@section('title', 'Attendance Reports')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Attendance Reports & Analytics</h2>
    </div>

    <!-- Filters -->
    <form class="row g-3 mb-4" method="GET">
        <div class="col-md-3">
            <label class="form-label">From Date</label>
            <input type="date" name="from" class="form-control" value="{{ $startDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To Date</label>
            <input type="date" name="to" class="form-control" value="{{ $endDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Employee</label>
            <select name="employee_id" class="form-select">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->Id }}" @selected(request('employee_id') == $emp->Id)>
                        {{ $emp->FirstName }} {{ $emp->LastName }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">Apply Filters</button>
        </div>
    </form>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Hours -->
        <div class="col-md-3">
            <div class="card shadow-sm border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Hours Worked</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalHoursWorked, 2) }} hrs</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Late Minutes -->
        <div class="col-md-3">
            <div class="card shadow-sm border-left-danger h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Late Minutes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalLateMinutes) }} min</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Overtime Hours -->
        <div class="col-md-3">
            <div class="card shadow-sm border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Overtime</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalOvertimeHours, 2) }} hrs</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-business-time fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Attendance Rate -->
        <div class="col-md-3">
            <div class="card shadow-sm border-left-info h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Presence Rate</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        {{ $totalScheduled > 0 ? round(($totalPresent / $totalScheduled) * 100, 1) : 0 }}%
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: {{ $totalScheduled > 0 ? ($totalPresent / $totalScheduled) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <!-- Line Chart: Daily Trend -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Attendance Trend (Daily)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 300px;">
                        <canvas id="dailyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Chart: Status Distribution -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2" style="height: 300px;">
                        <canvas id="statusPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row">
        <!-- Bar Chart: Late Employees -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Top 5 Late Employees (Minutes)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar" style="height: 300px;">
                        <canvas id="lateBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bar Chart: Overtime Employees -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Top 5 Overtime Employees (Hours)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar" style="height: 300px;">
                        <canvas id="otBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Data passed from Controller
    const trendData = @json($dailyTrend);
    const statusData = @json($statusDist);
    const lateData = @json($topLateEmployees);
    const otData = @json($topOvertimeEmployees);

    // 1. Line Chart: Daily Trend
    const ctxTrend = document.getElementById('dailyTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: trendData.map(d => {
                const date = new Date(d.WorkDate);
                return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
            }),
            datasets: [
                {
                    label: 'Present',
                    data: trendData.map(d => d.present_count),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Late Occurrences',
                    data: trendData.map(d => d.late_count),
                    borderColor: '#e74a3b',
                    backgroundColor: 'rgba(231, 74, 59, 0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // 2. Pie Chart: Status
    const ctxPie = document.getElementById('statusPieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#e74a3b', '#f6c23e'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#be2617', '#dda20a'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
        }
    });

    // 3. Bar Chart: Late Employees
    const ctxLate = document.getElementById('lateBarChart').getContext('2d');
    new Chart(ctxLate, {
        type: 'bar',
        data: {
            labels: lateData.map(d => d.employee ? d.employee.FirstName + ' ' + d.employee.LastName : 'Unknown'),
            datasets: [{
                label: 'Late Minutes',
                data: lateData.map(d => d.total_late_min),
                backgroundColor: '#e74a3b',
            }]
        },
        options: {
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: { x: { beginAtZero: true } }
        }
    });

    // 4. Bar Chart: Overtime Employees
    const ctxOT = document.getElementById('otBarChart').getContext('2d');
    new Chart(ctxOT, {
        type: 'bar',
        data: {
            labels: otData.map(d => d.employee ? d.employee.FirstName + ' ' + d.employee.LastName : 'Unknown'),
            datasets: [{
                label: 'Overtime Hours',
                data: otData.map(d => d.total_ot_hours),
                backgroundColor: '#1cc88a',
            }]
        },
        options: {
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: { x: { beginAtZero: true } }
        }
    });

</script>
@endsection
