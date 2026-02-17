@extends('layouts.app')

@section('title', 'Trainers')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Trainers</h2>
        <a class="btn btn-primary" href="{{ route('crm.training.trainers.create') }}">+ New Trainer</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Internal User</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trainers as $trainer)
                            <tr>
                                <td>{{ $trainer->Name }}</td>
                                <td>{{ $trainer->TrainerType }}</td>
                                <td>
                                    @if($trainer->user)
                                        {{ $trainer->user->Name }} ({{ $trainer->user->UserID }})
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $trainer->Email ?? $trainer->Phone ?? '-' }}</td>
                                <td>{{ $trainer->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('crm.training.trainers.edit', $trainer->Id) }}">Edit</a>
                                    @if($trainer->IsActive)
                                        <form method="POST" action="{{ route('crm.training.trainers.destroy', $trainer->Id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No trainers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $trainers->links() }}
    </div>
</div>
@endsection
