@extends('layouts.app')

@section('title', 'Acting Assignments')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Acting Assignments</h2>
        <a class="btn btn-primary" href="{{ route('hr.movements.acting.create') }}">+ New Acting Assignment</a>
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
                            <th>Employee</th>
                            <th>Acting Role</th>
                            <th>Dates</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $item)
                            <tr>
                                <td>{{ $item->employee->FirstName ?? '' }} {{ $item->employee->LastName ?? '' }}</td>
                                <td>{{ $item->ActingRoleID ?? '-' }}</td>
                                <td>{{ optional($item->StartDate)->format('Y-m-d') }} - {{ optional($item->EndDate)->format('Y-m-d') }}</td>
                                <td>{{ $item->Status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.movements.acting.show', $item->Id) }}">View</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.movements.acting.edit', $item->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No acting assignments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $assignments->links() }}
        </div>
    </div>
</div>
@endsection
