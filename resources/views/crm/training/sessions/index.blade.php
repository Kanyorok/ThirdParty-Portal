@extends('layouts.app')

@section('title', 'Training Sessions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Training Sessions</h2>
        <a class="btn btn-primary" href="{{ route('crm.training.sessions.create') }}">+ New Session</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Program</label>
                    <select name="program_id" class="form-select">
                        <option value="">All</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->Id }}" @selected(request('program_id') == $program->Id)>{{ $program->Title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach($statusList as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Session</th>
                            <th>Date</th>
                            <th>Trainer</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr>
                                <td>{{ $session->program?->Title ?? '-' }}</td>
                                <td>{{ $session->Title ?? $session->SessionCode ?? ('Session #' . $session->Id) }}</td>
                                <td>
                                    {{ $session->StartDate?->format('Y-m-d') ?? '-' }}
                                    @if($session->EndDate && $session->EndDate->format('Y-m-d') !== $session->StartDate?->format('Y-m-d'))
                                        - {{ $session->EndDate->format('Y-m-d') }}
                                    @endif
                                </td>
                                <td>{{ $session->trainer?->Name ?? '-' }}</td>
                                <td>{{ $session->Status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('crm.training.sessions.show', $session->Id) }}">View</a>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('crm.training.sessions.edit', $session->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No sessions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $sessions->links() }}
    </div>
</div>
@endsection

