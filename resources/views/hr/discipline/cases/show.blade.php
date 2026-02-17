@extends('layouts.app')

@section('title', 'Case Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Case {{ $case->CaseNo }}</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.discipline.cases.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <strong>Employee:</strong>
                    <div>{{ $case->employee?->FirstName }} {{ $case->employee?->LastName }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Offence:</strong>
                    <div>{{ $case->offence?->Name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Status:</strong>
                    <div>{{ $case->Status }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Severity:</strong>
                    <div>{{ $case->Severity ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Incident Date:</strong>
                    <div>{{ $case->IncidentDate ? $case->IncidentDate->format('Y-m-d') : '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Reported Date:</strong>
                    <div>{{ $case->ReportedDate ? $case->ReportedDate->format('Y-m-d') : '-' }}</div>
                </div>
                <div class="col-12">
                    <strong>Description:</strong>
                    <div>{{ $case->Description ?? '-' }}</div>
                </div>
            </div>
            <div class="mt-3 d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary" href="{{ route('hr.discipline.cases.notice.create', $case->Id) }}">Show Cause</a>
                <a class="btn btn-outline-primary" href="{{ route('hr.discipline.cases.response.create', $case->Id) }}">Response</a>
                <a class="btn btn-outline-primary" href="{{ route('hr.discipline.cases.investigation.edit', $case->Id) }}">Investigation</a>
                <a class="btn btn-outline-primary" href="{{ route('hr.discipline.cases.hearing.edit', $case->Id) }}">Hearing</a>
                <a class="btn btn-outline-primary" href="{{ route('hr.discipline.cases.decision.edit', $case->Id) }}">Decision</a>
                <a class="btn btn-outline-primary" href="{{ route('hr.discipline.cases.appeal.edit', $case->Id) }}">Appeal</a>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Evidence & Documents</h5>
                    <ul class="list-unstyled">
                        @forelse($case->documents as $doc)
                            <li>
                                @if($doc->document)
                                    <a href="{{ route('file.preview', ['document' => $doc->document->DocumentId]) }}" target="_blank">
                                        {{ $doc->Title ?? $doc->document->Name }}
                                    </a>
                                @else
                                    {{ $doc->Title ?? 'Document' }}
                                @endif
                            </li>
                        @empty
                            <li class="text-muted">No documents uploaded.</li>
                        @endforelse
                    </ul>

                    <form action="{{ route('hr.discipline.cases.documents.store', $case->Id) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="file" name="Document" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="Title" class="form-control" placeholder="Title">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-outline-primary">Upload Document</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Status Log</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Remarks</th>
                                    <th>Changed On</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($case->statusLogs as $log)
                                    <tr>
                                        <td>{{ $log->FromStatus ?? '-' }}</td>
                                        <td>{{ $log->ToStatus }}</td>
                                        <td>{{ $log->Remarks ?? '-' }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($log->ChangedOn)->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">No status changes yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
