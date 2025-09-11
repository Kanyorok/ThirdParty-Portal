@extends('layouts.app')
@section('title','Incidents Dashboard')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-4">📊 Compliance Incidents Dashboard</h4>

    <!-- Quick Stats -->
    <div class="row mb-4 text-center">
        <div class="col-md-3">
            <div class="card bg-light p-3 shadow-sm">
                <h5>Total Incidents</h5>
                <h2>{{ $total }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning p-3 shadow-sm">
                <h5>Open</h5>
                <h2>{{ $open }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success p-3 shadow-sm text-white">
                <h5>Resolved</h5>
                <h2>{{ $resolved }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger p-3 shadow-sm text-white">
                <h5>Escalated</h5>
                <h2>{{ $escalated }}</h2>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-6">
            <h5 class="text-center">Incidents by Severity</h5>
            <canvas id="severityChart"></canvas>
        </div>
        <div class="col-md-6">
            <h5 class="text-center">Incidents by Status</h5>
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const severityCtx = document.getElementById('severityChart').getContext('2d');
    new Chart(severityCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($severityData->keys()) !!},
            datasets: [{
                data: {!! json_encode($severityData->values()) !!},
                backgroundColor: ['#28a745','#ffc107','#fd7e14','#dc3545'],
            }]
        }
    });

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($statusData->keys()) !!},
            datasets: [{
                label: 'Incidents',
                data: {!! json_encode($statusData->values()) !!},
                backgroundColor: '#007bff'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
</script>
@endsection
