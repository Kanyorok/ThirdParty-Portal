@extends('layouts.app')

@section('title', 'Training Program')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Training Program</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.training.programs.index') }}">Back</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <h5 class="mb-1">{{ $program->Title }}</h5>
                    <div class="text-muted">{{ $program->Objectives ?? 'No objectives captured.' }}</div>
                </div>
                <div class="col-md-4">
                    <div><strong>Code:</strong> {{ $program->Code }}</div>
                    <div><strong>Category:</strong> {{ $program->category?->Name ?? '-' }}</div>
                    <div><strong>Mode:</strong> {{ $program->DeliveryMode ?? '-' }}</div>
                    <div><strong>Duration:</strong> {{ $program->DurationHours ?? '-' }} hrs</div>
                    <div><strong>Status:</strong> {{ $program->Status }}</div>
                    <div><strong>Mandatory:</strong> {{ $program->IsMandatory ? 'Yes' : 'No' }}</div>
                    <div><strong>Certification:</strong> {{ $program->HasCertification ? 'Yes' : 'No' }}</div>
                </div>
                <div class="col-12">
                    <div><strong>Target Audience:</strong> {{ $program->TargetAudience ?? '-' }}</div>
                    <div class="mt-2"><strong>Target Departments:</strong> {{ $targetNames['departments'] ?: '-' }}</div>
                    <div><strong>Target Grades:</strong> {{ $targetNames['grades'] ?: '-' }}</div>
                    <div><strong>Target Roles:</strong> {{ $targetNames['roles'] ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Sessions</h5>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.training.sessions.create') }}">+ Schedule Session</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Session</th>
                            <th>Date</th>
                            <th>Trainer</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($program->sessions as $session)
                            <tr>
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
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.training.sessions.show', $session->Id) }}">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No sessions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
