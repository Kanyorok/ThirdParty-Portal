@extends('layouts.app')

@section('title', 'Job Opening Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Job Opening Details</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.openings.index') }}">Back</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div><strong>Code:</strong> {{ $opening->Code }}</div>
                    <div><strong>Title:</strong> {{ $opening->Title }}</div>
                    <div><strong>Status:</strong> {{ $opening->Status }}</div>
                    <div><strong>Vacancies:</strong> {{ $opening->Vacancies }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Department:</strong> {{ $opening->department?->Name ?? '-' }}</div>
                    <div><strong>Branch:</strong> {{ $opening->branch?->Name ?? '-' }}</div>
                    <div><strong>Job Grade:</strong> {{ $opening->grade?->Name ?? '-' }}</div>
                    <div><strong>Job Role:</strong> {{ $opening->role?->Name ?? '-' }}</div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <div><strong>Employment Type:</strong> {{ $opening->EmploymentType ?? '-' }}</div>
                    <div><strong>Contract Type:</strong> {{ $opening->ContractType ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Published On:</strong> {{ $opening->PublishedOn ? \Carbon\Carbon::parse($opening->PublishedOn)->format('Y-m-d') : '-' }}</div>
                    <div><strong>Close Date:</strong> {{ $opening->CloseDate ? \Carbon\Carbon::parse($opening->CloseDate)->format('Y-m-d') : '-' }}</div>
                </div>
            </div>
            @if($opening->Description)
                <div class="mt-3">
                    <strong>Description:</strong>
                    <div>{{ $opening->Description }}</div>
                </div>
            @endif
            @if($opening->Requirements)
                <div class="mt-2">
                    <strong>Requirements:</strong>
                    <div>{{ $opening->Requirements }}</div>
                </div>
            @endif
            @if($opening->questions?->count())
                <div class="mt-2">
                    <strong>Interview Questions:</strong>
                    <ul class="mb-0">
                        @foreach($opening->questions as $question)
                            <li>{{ $question->Title }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if($opening->requisition)
                <div class="mt-2">
                    <strong>Requisition:</strong> {{ $opening->requisition->Code }} - {{ $opening->requisition->Title }}
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Applications</h6>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.applications.index', ['opening_id' => $opening->Id]) }}">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opening->applications as $application)
                            <tr>
                                <td>{{ $application->applicant?->FirstName }} {{ $application->applicant?->LastName }}</td>
                                <td>{{ $application->AppliedOn ? \Carbon\Carbon::parse($application->AppliedOn)->format('Y-m-d') : '-' }}</td>
                                <td>{{ $application->Status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.applications.show', $application->Id) }}">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No applications yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
