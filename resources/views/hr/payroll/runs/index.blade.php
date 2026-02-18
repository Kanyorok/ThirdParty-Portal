@extends('layouts.app')

@section('title', 'Payroll Runs')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Payroll Runs</h2>
        <a class="btn btn-primary" href="{{ route('hr.payroll.runs.create') }}">Generate Payroll</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>ID</th><th>Cycle</th><th>Status</th><th>Generated</th><th></th></tr></thead>
                <tbody>
                    @forelse($runs as $run)
                        <tr>
                            <td>#{{ $run->Id }}</td>
                            <td>{{ $run->cycle?->Month }}/{{ $run->cycle?->Year }}</td>
                            <td>{{ $run->Status }}</td>
                            <td>{{ $run->GeneratedOn }}</td>
                            <td><a href="{{ route('hr.payroll.runs.show', $run->Id) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No runs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {{ $runs->links() }}
    </div>
</div>
@endsection
