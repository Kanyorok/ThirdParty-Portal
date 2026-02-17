@extends('layouts.app')

@section('title', 'Interviews')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Interviews</h2>
        <a class="btn btn-primary" href="{{ route('hr.recruitment.interviews.create') }}">+ Schedule Interview</a>
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
                            <th>Job Opening</th>
                            <th>Round</th>
                            <th>Date</th>
                            <th>Candidates</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr>
                                <td>{{ $session->opening?->Title ?? '-' }}</td>
                                <td>{{ $session->RoundLabel ?? 'Round '.$session->RoundNo }}</td>
                                <td>{{ $session->InterviewDate ? \Carbon\Carbon::parse($session->InterviewDate)->format('Y-m-d') : '-' }}</td>
                                <td>{{ $session->candidates_count }}</td>
                                <td>{{ $session->Status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.interviews.show', $session->Id) }}">View</a>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.recruitment.interviews.edit', $session->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No interview sessions found.</td></tr>
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
