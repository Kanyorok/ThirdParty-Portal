@extends('layouts.app')

@section('title', 'KPI Rating Scales')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Rating Scales</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.kpi.ratingscales.create') }}">+ New Rating Scale</a>
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
                            <th>Min</th>
                            <th>Max</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scales as $scale)
                            <tr>
                                <td>{{ $scale->Code }}</td>
                                <td>{{ $scale->Name }}</td>
                                <td>{{ $scale->MinScore }}</td>
                                <td>{{ $scale->MaxScore }}</td>
                                <td>{{ $scale->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.kpi.ratingscales.edit', $scale->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.config.kpi.ratingscales.destroy', $scale->Id) }}" method="POST" onsubmit="return confirm('Deactivate this scale?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No rating scales found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $scales->links() }}
        </div>
    </div>
</div>
@endsection
