@extends('layouts.app')

@section('title', 'Payroll Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Payroll Dashboard</h2>
        <div class="d-flex gap-2">
            <a class="btn btn-primary" href="{{ route('hr.payroll.cycles.create') }}">Open Payroll Cycle</a>
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.runs.create') }}">Generate Payroll</a>
            <form method="POST" action="{{ route('hr.payroll.syncMandatory') }}">
                @csrf
                <button class="btn btn-outline-secondary" type="submit">Sync Mandatory</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Open Cycle</div>
                    <div class="h5 mb-0">
                        @if($totals['openCycle'])
                            {{ $totals['openCycle']->Month }}/{{ $totals['openCycle']->Year }} ({{ $totals['openCycle']->Status }})
                        @else
                            None
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Payroll Cycles</div>
                    <div class="h5 mb-0">{{ $totals['cycles'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Payroll Runs</div>
                    <div class="h5 mb-0">{{ $totals['runs'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Employees Processed</div>
                    <div class="h5 mb-0">{{ $totals['employeesProcessed'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between">
                    <span>Recent Payroll Runs</span>
                    <a href="{{ route('hr.payroll.runs.index') }}">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>ID</th><th>Cycle</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse($recentRuns as $run)
                                <tr>
                                    <td>#{{ $run->Id }}</td>
                                    <td>{{ $run->cycle?->Month }}/{{ $run->cycle?->Year }}</td>
                                    <td>{{ $run->Status }}</td>
                                    <td><a href="{{ route('hr.payroll.runs.show', $run->Id) }}">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No runs yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between">
                    <span>Recent Payroll Cycles</span>
                    <a href="{{ route('hr.payroll.cycles.index') }}">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>Period</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse($recentCycles as $cycle)
                                <tr>
                                    <td>{{ $cycle->Month }}/{{ $cycle->Year }}</td>
                                    <td>{{ $cycle->Status }}</td>
                                    <td><a href="{{ route('hr.payroll.cycles.show', $cycle->Id) }}">Details</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No cycles yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
