@extends('layouts.app')

@section('title', 'Payroll Cycle')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Payroll Cycle {{ $cycle->Month }}/{{ $cycle->Year }}</h2>
        <div class="d-flex gap-2">
            @if($cycle->Status !== 'Closed')
                <a class="btn btn-outline-primary" href="{{ route('hr.payroll.runs.create') }}">Generate Payroll</a>
            @else
                <span class="text-muted align-self-center">Cycle closed</span>
            @endif
            <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.cycles.index') }}">Back</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex justify-content-between">
            <div>
                <div class="text-muted small">Status</div>
                <div class="h5 mb-0">{{ $cycle->Status }}</div>
            </div>
            <div>
                <div class="text-muted small">Opened</div>
                <div>{{ $cycle->OpenedOn }}</div>
            </div>
            <div>
                <div class="text-muted small">Closed</div>
                <div>{{ $cycle->ClosedOn ?? '—' }}</div>
            </div>
            <div>
                <div class="text-muted small">Reopened</div>
                <div>{{ $cycle->ReopenedOn ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">Payroll Runs</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>ID</th><th>Status</th><th>Generated</th><th></th></tr></thead>
                <tbody>
                    @forelse($cycle->runs as $run)
                        <tr>
                            <td>#{{ $run->Id }}</td>
                            <td>{{ $run->Status }}</td>
                            <td>{{ $run->GeneratedOn }}</td>
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
@endsection
