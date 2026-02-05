@extends('layouts.app')

@section('title', 'Job Roles')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Job Roles</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.jobroles.create') }}">+ New Role</a>
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
                        <th>Grade</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td>{{ $role->Code }}</td>
                        <td>{{ $role->Name }}</td>
                        <td>{{ optional($role->grade)->Name }}</td>
                        <td>{{ optional($role->department)->Name }}</td>
                        <td>{{ $role->IsActive ? 'Active' : 'Inactive' }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.jobroles.edit', $role->Id) }}">Edit</a>
                            @if($role->IsActive)
                                <form class="d-inline" method="POST" action="{{ route('hr.config.jobroles.destroy', $role->Id) }}" onsubmit="return confirm('Deactivate this role?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                </form>
                            @else
                                <form class="d-inline" method="POST" action="{{ route('hr.config.jobroles.activate', $role->Id) }}" onsubmit="return confirm('Activate this role?');">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" type="submit">Activate</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No job roles found.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $roles->links() }}
        </div>
    </div>
</div>
@endsection
