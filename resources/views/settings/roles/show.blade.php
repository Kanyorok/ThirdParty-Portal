@extends('layouts.app')

@section('title', 'Role: ' . $role->name)

@section('content')
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">{{ $role->name }}</h1>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Assigned Users</h5>
        </div>
        <div class="card-body">
            @if($role->users->isEmpty())
                <div class="alert alert-warning mb-0">No users are currently assigned to this role.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>UserID</th>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($role->users as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $user->UserID ?? '-' }}</td>
                                    <td>{{ $user->Name ?? $user->name }}</td>
                                    <td>{{ $user->Email ?? $user->email }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
