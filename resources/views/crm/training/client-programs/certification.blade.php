@extends('layouts.app')

@section('title', 'Program Certification')

@php
    $completionRule = $completionConfig['rule'] ?? 'AnySession';
    $requiredSessions = (int) ($completionConfig['required_sessions'] ?? 1);
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Program Certification</h2>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('crm.training.programs.show', $program->Id) }}">Program</a>
            <a class="btn btn-outline-secondary" href="{{ route('crm.training.programs.participants', $program->Id) }}">Participants</a>
            <a class="btn btn-outline-secondary" href="{{ route('crm.training.programs.index') }}">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-8">
                    <h5 class="mb-1">{{ $program->Title }}</h5>
                    <div class="text-muted">Issue one certificate per participant for the full course/program.</div>
                </div>
                <div class="col-lg-4">
                    <div><strong>Code:</strong> {{ $program->Code }}</div>
                    <div><strong>Sessions:</strong> {{ number_format($summary['total_sessions']) }}</div>
                    <div><strong>Completion Rule:</strong> {{ $completionConfig['label'] }}</div>
                    <div><strong>Eligible:</strong> {{ number_format($summary['eligible_participants']) }}</div>
                    <div><strong>Pending Issue:</strong> {{ number_format($summary['pending_eligible']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h5 class="mb-3">Mass Issue Program Certificates</h5>
            <form method="POST" action="{{ route('crm.training.programs.certification.issue', $program->Id) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-xl-3 col-lg-4">
                    <label class="form-label form-label-sm mb-1">Common Template *</label>
                    <select name="CertificateTemplateID" class="form-select form-select-sm" required>
                        <option value="">Select template</option>
                        @foreach($certificateTemplates as $template)
                            <option value="{{ $template->Id }}">{{ $template->Name }}{{ $template->IsSample ? ' (Sample)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-4">
                    <label class="form-label form-label-sm mb-1">Certification Name</label>
                    <input type="text" name="CertificationName" class="form-control form-control-sm" placeholder="Optional">
                </div>
                <div class="col-xl-2 col-lg-4">
                    <label class="form-label form-label-sm mb-1">Issuing Body</label>
                    <input type="text" name="IssuingBody" class="form-control form-control-sm" placeholder="Optional">
                </div>
                <div class="col-xl-2 col-lg-3">
                    <label class="form-label form-label-sm mb-1">Issue Date</label>
                    <input type="date" name="IssuedOn" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                </div>
                <div class="col-xl-2 col-lg-3">
                    <label class="form-label form-label-sm mb-1">Expiry Date</label>
                    <input type="date" name="ExpiresOn" class="form-control form-control-sm">
                </div>
                <div class="col-xl-1 col-lg-3">
                    <label class="form-label form-label-sm mb-1">No Prefix</label>
                    <input type="text" name="CertificateNumberPrefix" class="form-control form-control-sm" placeholder="CERT-2026">
                </div>
                <div class="col-12 d-flex flex-wrap align-items-center gap-3 pt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ProgramBulkSendEmail" name="SendEmail" value="1">
                        <label class="form-check-label small" for="ProgramBulkSendEmail">Auto-send certificate email after generation</label>
                    </div>
                    <button class="btn btn-sm btn-primary ms-auto" type="submit"
                            @disabled($summary['pending_eligible'] === 0)
                            onclick="return confirm('Issue program certificates for all eligible participants without certificates?')">
                        Mass Issue Program Certificates
                    </button>
                </div>
            </form>
            <div class="form-text mt-2 mb-0">
                Rule: {{ $completionConfig['label'] }}.
                Eligible participants with existing program certificates are skipped automatically.
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Program Participants</h5>

            <form method="GET" action="{{ route('crm.training.programs.certification', $program->Id) }}" class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" name="participant_name" class="form-control" value="{{ $participantFilters['name'] }}" placeholder="e.g. Jane Doe">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ClientID</label>
                    <input type="text" name="participant_client_id" class="form-control" value="{{ $participantFilters['client_id'] }}" placeholder="e.g. 012345678">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Number</label>
                    <input type="text" name="participant_id_number" class="form-control" value="{{ $participantFilters['id_number'] }}" placeholder="Passport/Certificate number">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-outline-primary" type="submit">Filter Participants</button>
                    <a class="btn btn-outline-secondary" href="{{ route('crm.training.programs.certification', $program->Id) }}">Clear Filters</a>
                </div>
            </form>

            @if($participants->isEmpty())
                <div class="text-muted">No participants match the selected filters.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Client ID</th>
                                <th>ID Number</th>
                                <th>Attended Sessions</th>
                                <th>Required</th>
                                <th>Eligible</th>
                                <th>Program Certificate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($participants as $client)
                                <tr>
                                    <td>{{ $client->Name }}</td>
                                    <td>{{ $client->ClientID }}</td>
                                    <td>{{ $client->individual?->PassportNo ?? $client->corporate?->CertificateNo ?? '-' }}</td>
                                    <td>{{ number_format((int) ($client->AttendedSessions ?? 0)) }}</td>
                                    <td>
                                        @if($completionRule === 'AllSessions')
                                            {{ number_format((int) ($completionConfig['total_sessions'] ?? 0)) }}
                                        @else
                                            {{ number_format($requiredSessions) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($client->IsEligibleForProgramCertificate)
                                            <span class="text-success">Eligible</span>
                                        @else
                                            <span class="text-muted">Not Yet</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($client->ProgramCertificateIssued)
                                            <span class="text-success">Issued</span>
                                        @elseif($client->IsEligibleForProgramCertificate)
                                            <span class="text-warning">Pending</span>
                                        @else
                                            <span class="text-muted">Not Eligible</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $participants->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
