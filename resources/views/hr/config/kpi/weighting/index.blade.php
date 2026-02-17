@extends('layouts.app')

@section('title', 'KPI Weighting Rules')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Weighting Rules</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.kpi.weighting.create') }}">+ New Rule</a>
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
                            <th>Grade</th>
                            <th>Role</th>
                            <th>Total Weight</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                            <tr>
                                <td>{{ $rule->Code }}</td>
                                <td>{{ $rule->Name }}</td>
                                <td>{{ $rule->GradeID }}</td>
                                <td>{{ $rule->RoleID }}</td>
                                <td>{{ $rule->TotalWeight }}</td>
                                <td>{{ $rule->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.kpi.weighting.edit', $rule->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.config.kpi.weighting.destroy', $rule->Id) }}" method="POST" onsubmit="return confirm('Deactivate this rule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No weighting rules found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $rules->links() }}
        </div>
    </div>
</div>
@endsection
