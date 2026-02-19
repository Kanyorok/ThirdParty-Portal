@extends('layouts.app')

@section('title', 'KPI Perspective Weights')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Perspective Weights</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.kpi.perspective-weights.create') }}">+ New Weight</a>
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
                            <th>Perspective</th>
                            <th>Period</th>
                            <th>Grade</th>
                            <th>Role</th>
                            <th class="text-end">Weight</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($weights as $weight)
                            <tr>
                                <td>{{ $weight->perspective?->Name ?? $weight->PerspectiveID }}</td>
                                <td>{{ $weight->period?->Name ?? $weight->PeriodID }}</td>
                                <td>{{ $weight->grade?->Name ?? '-' }}</td>
                                <td>{{ $weight->role?->Name ?? '-' }}</td>
                                <td class="text-end">{{ number_format((float)$weight->Weight, 4) }}</td>
                                <td>{{ $weight->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.kpi.perspective-weights.edit', $weight->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.config.kpi.perspective-weights.destroy', $weight->Id) }}" method="POST" onsubmit="return confirm('Deactivate this weight?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No perspective weights found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $weights->links() }}
        </div>
    </div>
</div>
@endsection
