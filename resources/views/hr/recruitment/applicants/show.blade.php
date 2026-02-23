@extends('layouts.app')

@section('title', 'Applicant Profile')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Applicant Profile</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.applicants.index') }}">Back</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div><strong>Name:</strong> {{ $applicant->FirstName }} {{ $applicant->LastName }}</div>
                    <div><strong>Email:</strong> {{ $applicant->Email ?? '-' }}</div>
                    <div><strong>Phone:</strong> {{ $applicant->Phone ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Source:</strong> {{ $applicant->Source ?? '-' }}</div>
                    <div><strong>Gender:</strong> {{ $applicant->Gender ?? '-' }}</div>
                    <div><strong>Address:</strong> {{ $applicant->Address ?? '-' }}</div>
                </div>
            </div>
            @if($applicant->Notes)
                <div class="mt-3">
                    <strong>Notes:</strong>
                    <div>{{ $applicant->Notes }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Applications</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Job</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applicant->applications as $application)
                            <tr>
                                <td>{{ $application->opening?->Title ?? '-' }}</td>
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
