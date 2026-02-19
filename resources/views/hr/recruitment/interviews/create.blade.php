@extends('layouts.app')

@section('title', 'Schedule Interview')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Schedule Interview</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.interviews.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.recruitment.interviews.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Job Opening *</label>
                        <select name="JobOpeningID" id="openingSelect" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($openings as $opening)
                                <option value="{{ $opening->Id }}" @selected(old('JobOpeningID', $selectedOpening?->Id) == $opening->Id)>{{ $opening->Title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Round *</label>
                        <input type="number" name="RoundNo" class="form-control" min="1" value="{{ old('RoundNo', 1) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Round Label</label>
                        <input type="text" name="RoundLabel" class="form-control" value="{{ old('RoundLabel') }}" placeholder="Round 1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Interview Type</label>
                        <input type="text" name="InterviewType" class="form-control" value="{{ old('InterviewType') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Interview Date *</label>
                        <input type="date" name="InterviewDate" class="form-control" value="{{ old('InterviewDate') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="Location" class="form-control" value="{{ old('Location') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            @foreach($statusList as $status)
                                <option value="{{ $status }}" @selected(old('Status', 'Scheduled') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Source Round</label>
                        <select name="SourceSessionID" id="sourceSession" class="form-select">
                            <option value="">Shortlisted candidates</option>
                            @foreach($sourceSessions as $session)
                                <option value="{{ $session->Id }}" @selected(old('SourceSessionID', $selectedSourceSession?->Id) == $session->Id)>
                                    {{ $session->RoundLabel ?? 'Round '.$session->RoundNo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($selectedOpening)
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Candidates</h6>
                            <span class="text-muted small">Select candidates and set slot times.</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Select</th>
                                        <th>Candidate</th>
                                        <th>Status</th>
                                        <th style="width: 220px;">Slot Time *</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($candidates as $candidate)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="CandidateIDs[]" value="{{ $candidate->ApplicationID }}" checked>
                                            </td>
                                            <td>{{ $candidate->Name }}</td>
                                            <td>{{ $candidate->Status }}</td>
                                            <td>
                                                <input type="time"
                                                       class="form-control"
                                                       name="SlotTimes[{{ $candidate->ApplicationID }}]"
                                                       value="{{ old("SlotTimes.{$candidate->ApplicationID}") }}"
                                                       step="60">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">No eligible candidates found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mt-3">Select a job opening to load candidates.</div>
                @endif

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Create Interview Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const openingSelect = document.getElementById('openingSelect');
        const sourceSession = document.getElementById('sourceSession');
        function reload() {
            const url = new URL(window.location.href);
            if (openingSelect.value) {
                url.searchParams.set('opening_id', openingSelect.value);
            } else {
                url.searchParams.delete('opening_id');
            }
            if (sourceSession.value) {
                url.searchParams.set('source_session_id', sourceSession.value);
            } else {
                url.searchParams.delete('source_session_id');
            }
            window.location.href = url.toString();
        }
        if (openingSelect) {
            openingSelect.addEventListener('change', reload);
        }
        if (sourceSession) {
            sourceSession.addEventListener('change', reload);
        }
    })();
</script>
@endsection
