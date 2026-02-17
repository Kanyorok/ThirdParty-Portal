@extends('layouts.app')

@section('title', 'Exit Types')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Exit Types</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.exit-types.create') }}">+ New Exit Type</a>
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
                            <th>Employer Initiated</th>
                            <th>Requires Case</th>
                            <th>Redundancy</th>
                            <th>Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                            <tr>
                                <td>{{ $type->Code }}</td>
                                <td>{{ $type->Name }}</td>
                                <td>{{ $type->IsEmployerInitiated ? 'Yes' : 'No' }}</td>
                                <td>{{ $type->RequiresCase ? 'Yes' : 'No' }}</td>
                                <td>{{ $type->IsRedundancy ? 'Yes' : 'No' }}</td>
                                <td>{{ $type->IsActive ? 'Yes' : 'No' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.exit-types.edit', $type->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No exit types found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
