@extends('layouts.app')

@section('title', 'Application Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Application Details</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.applications.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div><strong>Applicant:</strong> {{ $application->applicant?->FirstName }} {{ $application->applicant?->LastName }}</div>
                    <div><strong>Email:</strong> {{ $application->applicant?->Email ?? '-' }}</div>
                    <div><strong>Phone:</strong> {{ $application->applicant?->Phone ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Job Opening:</strong> {{ $application->opening?->Title ?? '-' }}</div>
                    <div><strong>Status:</strong> {{ $application->Status }}</div>
                    <div><strong>Applied On:</strong> {{ $application->AppliedOn ? \Carbon\Carbon::parse($application->AppliedOn)->format('Y-m-d') : '-' }}</div>
                </div>
            </div>
            @if($application->Notes)
                <div class="mt-3"><strong>Notes:</strong> {{ $application->Notes }}</div>
            @endif
        </div>
    </div>

    @php
        $latestScreening = $application->screenings
            ->sortByDesc(fn ($item) => $item->ScreenedOn ?? $item->CreatedOn)
            ->first();
        $statusValue = $latestScreening?->Status ?? $application->Status;
        if ($statusValue === 'New') {
            $statusValue = 'Applied';
        } elseif ($statusValue === 'Screening') {
            $statusValue = 'Under Screening';
        }
        if (in_array($statusValue, ['Applied', 'Under Screening'], true)) {
            $statusValue = 'Under Screening';
        }
        $approvalLabel = $latestScreening?->ApprovalStatus
            ? ucfirst(strtolower($latestScreening->ApprovalStatus))
            : 'Not Required';
    @endphp

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm" id="shortlisting">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Shortlisting</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('hr.recruitment.applications.screen', $application->Id) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Status</label>
                            <select name="Status" class="form-select" required>
                                <option value="Under Screening" @selected(old('Status', $statusValue) === 'Under Screening')>Under Screening</option>
                                <option value="Shortlisted" @selected(old('Status', $statusValue) === 'Shortlisted')>Shortlisted</option>
                                <option value="Rejected" @selected(old('Status', $statusValue) === 'Rejected')>Rejected</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Score</label>
                            <input type="number" step="0.01" name="Score" class="form-control" value="{{ old('Score', $latestScreening?->Score) }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Notes</label>
                            <textarea name="Notes" class="form-control" rows="3">{{ old('Notes', $latestScreening?->Notes) }}</textarea>
                        </div>
                        <button class="btn btn-outline-primary" type="submit">Update Shortlisting</button>
                    </form>
                    <div class="mt-3">
                        <div class="small text-muted">Current Status: {{ $application->Status }}</div>
                        <div class="small text-muted">Approval: {{ $approvalLabel }}</div>
                        @if($latestScreening?->ScreenedOn)
                            <div class="small text-muted">Last screened: {{ \Carbon\Carbon::parse($latestScreening->ScreenedOn)->format('Y-m-d H:i') }}</div>
                        @endif
                    </div>
                    @if($latestScreening && $latestScreening->Status === 'Shortlisted' && ($latestScreening->ApprovalStatus ?? 'Pending') === 'Pending')
                        <form method="POST" action="{{ route('hr.recruitment.applications.approveShortlist', $application->Id) }}" class="mt-3">
                            @csrf
                            <button class="btn btn-outline-success" type="submit">Approve Shortlist (HR)</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Documents</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        @if($application->ResumePath)
                            <li><a href="{{ asset('storage/'.$application->ResumePath) }}" target="_blank">Resume</a></li>
                        @endif
                        @if($application->CoverLetterPath)
                            <li><a href="{{ asset('storage/'.$application->CoverLetterPath) }}" target="_blank">Cover Letter</a></li>
                        @endif
                        @forelse($application->documents as $doc)
                            <li><a href="{{ asset('storage/'.$doc->FilePath) }}" target="_blank">{{ $doc->FileName }}</a></li>
                        @empty
                            @if(!$application->ResumePath && !$application->CoverLetterPath)
                                <li class="text-muted">No documents uploaded.</li>
                            @endif
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Interview Rounds</h6>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.interviews.create', ['opening_id' => $application->JobOpeningID]) }}">Schedule</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Round</th>
                                <th>Slot</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($application->interviewCandidates as $candidate)
                                <tr>
                                    <td>{{ $candidate->session?->RoundLabel ?? 'Round '.$candidate->session?->RoundNo }}</td>
                                    <td>{{ $candidate->SlotTime ? \Carbon\Carbon::parse($candidate->SlotTime)->format('Y-m-d H:i') : '-' }}</td>
                                    <td>{{ $candidate->Status }}</td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.interviews.candidates.evaluate', [$candidate->SessionID, $candidate->Id]) }}">Evaluate</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No interview rounds yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Offers</h6>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.offers.create', ['application_id' => $application->Id]) }}">Create Offer</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Offer Date</th>
                                <th>Salary</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($application->offers as $offer)
                                <tr>
                                    <td>{{ $offer->OfferDate ? \Carbon\Carbon::parse($offer->OfferDate)->format('Y-m-d') : '-' }}</td>
                                    <td>{{ $offer->SalaryOffered ? number_format($offer->SalaryOffered, 2) : '-' }}</td>
                                    <td>{{ $offer->Status }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No offers yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
