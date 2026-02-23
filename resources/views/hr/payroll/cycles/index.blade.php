@extends('layouts.app')

@section('title', 'Payroll Cycles')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Payroll Cycles</h2>
        <div>
            <a href="{{ route('hr.payroll.cycles.create') }}" class="btn btn-primary">Open Payroll Cycle</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Opened On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cycles as $cycle)
                        <tr>
                            <td>{{ $cycle->Month }}/{{ $cycle->Year }}</td>
                            <td>{{ $cycle->Status }}</td>
                            <td>{{ $cycle->OpenedOn }}</td>
                            <td class="d-flex gap-2">
                                <a href="{{ route('hr.payroll.cycles.show', $cycle->Id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @if($cycle->Status !== 'Closed')
                                    <form method="POST" action="{{ route('hr.payroll.cycles.close', $cycle->Id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Close</button>
                                    </form>
                                @endif
                                @if($cycle->Status === 'Closed')
                                    <form method="POST" action="{{ route('hr.payroll.cycles.reopen', $cycle->Id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary" type="submit">Reopen</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No payroll cycles yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {{ $cycles->links() }}
    </div>
</div>
@endsection
