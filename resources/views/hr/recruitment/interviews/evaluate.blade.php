@extends('layouts.app')

@section('title', 'Evaluate Candidate')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Candidate Evaluation</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.interviews.show', $candidate->SessionID) }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    @if($panelistLocked ?? false)
        <div class="alert alert-info">Scores for this panelist are locked and can only be viewed.</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div><strong>Candidate:</strong> {{ $candidate->application?->applicant?->FirstName }} {{ $candidate->application?->applicant?->LastName }}</div>
                    <div><strong>Job Opening:</strong> {{ $candidate->application?->opening?->Title ?? '-' }}</div>
                    <div><strong>Slot:</strong> {{ $candidate->SlotTime ? \Carbon\Carbon::parse($candidate->SlotTime)->format('Y-m-d H:i') : '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Status:</strong> {{ $candidate->Status }}</div>
                    <div><strong>Round:</strong> {{ $candidate->session?->RoundLabel ?? 'Round '.$candidate->session?->RoundNo }}</div>
                    <div><strong>Panelist Avg:</strong> {{ $panelistAverage !== null ? number_format($panelistAverage, 2) : '-' }}</div>
                    <div><strong>All Panelists Avg:</strong> {{ $overallAverage !== null ? number_format($overallAverage, 2) : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Documents</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        @if($candidate->application?->ResumePath)
                            <li><a href="{{ asset('storage/'.$candidate->application->ResumePath) }}" target="_blank">Resume</a></li>
                        @endif
                        @if($candidate->application?->CoverLetterPath)
                            <li><a href="{{ asset('storage/'.$candidate->application->CoverLetterPath) }}" target="_blank">Cover Letter</a></li>
                        @endif
                        @forelse($candidate->application?->documents ?? [] as $doc)
                            <li><a href="{{ asset('storage/'.$doc->FilePath) }}" target="_blank">{{ $doc->FileName }}</a></li>
                        @empty
                            @if(!$candidate->application?->ResumePath && !$candidate->application?->CoverLetterPath)
                                <li class="text-muted">No documents uploaded.</li>
                            @endif
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Panelist</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('hr.recruitment.interviews.candidates.evaluate', [$candidate->SessionID, $candidate->Id]) }}">
                        <label class="form-label">Select panelist</label>
                        <select name="panelist_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Select panelist</option>
                            @foreach($candidate->session?->panelMembers ?? [] as $panel)
                                <option value="{{ $panel->EmployeeID }}" @selected((string)$panelistId === (string)$panel->EmployeeID)>
                                    {{ $panel->employee?->FirstName }} {{ $panel->employee?->LastName }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    @if(!$panelistId)
                        <div class="text-muted small mt-2">Select a panelist to load assigned questions.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Evaluation Questions</h6>
                </div>
                <div class="card-body">
                    @if($questionGroups->isEmpty())
                        <div class="text-muted">No questions available for this session.</div>
                    @else
                        <form method="POST" action="{{ route('hr.recruitment.interviews.candidates.scores', [$candidate->SessionID, $candidate->Id]) }}">
                            @csrf
                            <input type="hidden" name="PanelistID" value="{{ $panelistId }}">
                            <div class="mb-3">
                                <label class="form-label">Candidate Status</label>
                                <select name="Status" class="form-select" required {{ ($panelistLocked ?? false) ? 'disabled' : '' }}>
                                    @foreach(['Scheduled','Completed','Passed','Failed','No-show','Proceed','Offer'] as $status)
                                        <option value="{{ $status }}" @selected(old('Status', $candidate->Status) === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @foreach($questionGroups as $groupName => $questions)
                                <div class="mb-3">
                                    <h6 class="mb-2">{{ $groupName }}</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Question</th>
                                                    <th style="width: 120px;">Score</th>
                                                    <th>Comment</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($questions as $question)
                                                    @php
                                                        $scoreRow = $scores[$question->Id] ?? null;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $question->Title }}</td>
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control form-control-sm" name="Scores[{{ $question->Id }}]" value="{{ old("Scores.{$question->Id}", $scoreRow?->Score) }}" {{ ($panelistLocked ?? false) ? 'disabled' : '' }}>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm" name="Comments[{{ $question->Id }}]" value="{{ old("Comments.{$question->Id}", $scoreRow?->Comment) }}" {{ ($panelistLocked ?? false) ? 'disabled' : '' }}>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                            <div class="text-end">
                                <button class="btn btn-primary" type="submit" {{ (!$panelistId || ($panelistLocked ?? false)) ? 'disabled' : '' }}>Save Scores</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
