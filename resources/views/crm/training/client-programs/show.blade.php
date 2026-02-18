@extends('layouts.app')

@section('title', 'Training Program')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Training Program</h2>
        <div class="d-flex gap-2">
            @if($program->HasCertification && (($program->CertificateScope ?? 'Session') === 'Program'))
                <a class="btn btn-outline-primary" href="{{ route('crm.training.programs.certification', $program->Id) }}">Program Certificates</a>
            @endif
            <a class="btn btn-primary" href="{{ route('crm.training.programs.participants', $program->Id) }}">Manage Participants</a>
            <a class="btn btn-outline-secondary" href="{{ route('crm.training.programs.index') }}">Back</a>
        </div>
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
                    @if($program->HasCertification)
                        <div><strong>Certificate Scope:</strong> {{ ($program->CertificateScope ?? 'Session') === 'Program' ? 'Whole Program/Course' : 'Per Session' }}</div>
                        @if(($program->CertificateScope ?? 'Session') === 'Program')
                            <div>
                                <strong>Completion Rule:</strong>
                                @if(($program->CertificationCompletionRule ?? 'AnySession') === 'AllSessions')
                                    Attend all program sessions
                                @elseif(($program->CertificationCompletionRule ?? 'AnySession') === 'MinSessions')
                                    Attend at least {{ (int) ($program->CertificationMinimumSessions ?: 1) }} session(s)
                                @else
                                    Attend at least one session
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
                <div class="col-12">
                    <div><strong>Target Audience:</strong> {{ $program->TargetAudience ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Target Clients ({{ $targetClients->count() }})</h5>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('crm.training.programs.participants', $program->Id) }}">Update Participants</a>
            </div>

            @if($targetClients->isEmpty())
                <div class="text-muted">No target clients assigned yet.</div>
            @else
                @php
                    $previewClients = $targetClients->take(20);
                @endphp
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ClientID</th>
                                <th>Name</th>
                                <th>ID Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewClients as $client)
                                <tr>
                                    <td>{{ $client->ClientID }}</td>
                                    <td>{{ $client->Name }}</td>
                                    <td>{{ $client->individual?->PassportNo ?? $client->corporate?->CertificateNo ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($targetClients->count() > $previewClients->count())
                    <div class="form-text mt-2">Showing first {{ $previewClients->count() }} of {{ $targetClients->count() }} clients. Use "Update Participants" to view all.</div>
                @endif
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Sessions</h5>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('crm.training.sessions.create') }}">+ Schedule Session</a>
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
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('crm.training.sessions.show', $session->Id) }}">View</a>
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
