@extends('layouts.app')

@section('title', 'Job Grades')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Job Grades</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.jobgrades.create') }}">+ New Grade</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Min Salary</th>
                        <th>Max Salary</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($grades as $grade)
                    <tr>
                        <td>{{ $grade->Code }}</td>
                        <td>{{ $grade->Name }}</td>
                        <td>{{ $grade->MinSalary }}</td>
                        <td>{{ $grade->MaxSalary }}</td>
                        <td>{{ $grade->IsActive ? 'Active' : 'Inactive' }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.jobgrades.edit', $grade->Id) }}">Edit</a>
                            @if($grade->IsActive)
                                <form class="d-inline" method="POST" action="{{ route('hr.config.jobgrades.destroy', $grade->Id) }}" onsubmit="return confirm('Deactivate this grade?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                </form>
                            @else
                                <form class="d-inline" method="POST" action="{{ route('hr.config.jobgrades.activate', $grade->Id) }}" onsubmit="return confirm('Activate this grade?');">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" type="submit">Activate</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No job grades found.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $grades->links() }}
        </div>
    </div>
</div>
@endsection
