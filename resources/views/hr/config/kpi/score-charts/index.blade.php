@extends('layouts.app')

@section('title', 'KPI Score Chart')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Score Chart</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.kpi.scorecharts.create') }}">+ New Row</a>
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
                            <th>Rating Scale</th>
                            <th class="text-end">Min %</th>
                            <th class="text-end">Max %</th>
                            <th class="text-end">Rating</th>
                            <th>Label</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($charts as $chart)
                            <tr>
                                <td>{{ $chart->ratingScale?->Name ?? 'All Scales' }}</td>
                                <td class="text-end">{{ number_format((float)$chart->MinPercent, 2) }}</td>
                                <td class="text-end">{{ $chart->MaxPercent !== null ? number_format((float)$chart->MaxPercent, 2) : 'No cap' }}</td>
                                <td class="text-end">{{ number_format((float)$chart->RatingValue, 2) }}</td>
                                <td>{{ $chart->RatingLabel }}</td>
                                <td>{{ $chart->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.kpi.scorecharts.edit', $chart->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.config.kpi.scorecharts.destroy', $chart->Id) }}" method="POST" onsubmit="return confirm('Deactivate this row?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No score chart rows found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $charts->links() }}
        </div>
    </div>
</div>
@endsection
