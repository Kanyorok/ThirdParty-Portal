@extends('layouts.app')

@section('title', 'KPI Perspectives')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">KPI Perspectives</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.kpi.perspectives.create') }}">+ New Perspective</a>
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
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($perspectives as $perspective)
                            <tr>
                                <td>{{ $perspective->Code }}</td>
                                <td>{{ $perspective->Name }}</td>
                                <td>{{ $perspective->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.kpi.perspectives.edit', $perspective->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.config.kpi.perspectives.destroy', $perspective->Id) }}" method="POST" onsubmit="return confirm('Deactivate this perspective?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No perspectives found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $perspectives->links() }}
        </div>
    </div>
</div>
@endsection
