@extends('layouts.app')

@section('title', 'Applications')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Applications</h2>
        <a class="btn btn-primary" href="{{ route('hr.recruitment.applications.create') }}">+ New Application</a>
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
                    <label class="form-label">Job Opening</label>
                    <select name="opening_id" class="form-select">
                        <option value="">All</option>
                        @foreach($openings as $opening)
                            <option value="{{ $opening->Id }}" @selected(request('opening_id') == $opening->Id)>{{ $opening->Title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach($statusList as $status)
                            <option value="{{ $status }}" @selected(request('status') == $status)>{{ $status }}</option>
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
                            <th>Applicant</th>
                            <th>Job Opening</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                            <tr>
                                <td>{{ $application->applicant?->FirstName }} {{ $application->applicant?->LastName }}</td>
                                <td>{{ $application->opening?->Title ?? '-' }}</td>
                                <td>{{ $application->AppliedOn ? \Carbon\Carbon::parse($application->AppliedOn)->format('Y-m-d') : '-' }}</td>
                                <td>{{ $application->Status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.applications.show', $application->Id) }}">View</a>
                                    @if(in_array($application->Status, ['Applied','Under Screening','New','Screening'], true))
                                        <a class="btn btn-sm btn-outline-success" href="{{ route('hr.recruitment.applications.show', $application->Id) }}#shortlisting">Shortlist</a>
                                    @endif
                                    @if(!in_array($application->Status, ['Rejected','Withdrawn','Hired'], true))
                                        <a class="btn btn-sm btn-outline-danger" href="{{ route('hr.recruitment.applications.show', $application->Id) }}#shortlisting">Reject</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No applications found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3">
        {{ $applications->links() }}
    </div>
</div>
@endsection
