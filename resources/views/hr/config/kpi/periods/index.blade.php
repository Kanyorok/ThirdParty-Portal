@extends('layouts.app')

@section('title', 'KPI Periods')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">KPI Periods</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.kpi.periods.create') }}">+ New Period</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Start Month</th>
                            <th>End Month</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $period)
                            <tr>
                                <td>{{ $period->Code }}</td>
                                <td>{{ $period->Name }}</td>
                                <td>{{ $period->StartMonth }}</td>
                                <td>{{ $period->EndMonth }}</td>
                                <td>{{ $period->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.kpi.periods.edit', $period->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.config.kpi.periods.destroy', $period->Id) }}" method="POST" onsubmit="return confirm('Deactivate this period?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No KPI periods found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $periods->links() }}
        </div>
    </div>
</div>
@endsection
