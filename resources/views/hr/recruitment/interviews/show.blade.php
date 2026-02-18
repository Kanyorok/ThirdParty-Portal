@extends('layouts.app')

@section('title', 'Interview Session')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Interview Session</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.interviews.index') }}">Back</a>
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
                    <div><strong>Job Opening:</strong> {{ $session->opening?->Title ?? '-' }}</div>
                    <div><strong>Round:</strong> {{ $session->RoundLabel ?? 'Round '.$session->RoundNo }}</div>
                    <div><strong>Interview Type:</strong> {{ $session->InterviewType ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Date:</strong> {{ $session->InterviewDate ? \Carbon\Carbon::parse($session->InterviewDate)->format('Y-m-d') : '-' }}</div>
                    <div><strong>Status:</strong> {{ $session->Status }}</div>
                    <div><strong>Location:</strong> {{ $session->Location ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Panel Members</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('hr.recruitment.interviews.panel.add', $session->Id) }}">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-12">
                                <label class="form-label">Search employees</label>
                                <input type="text" id="panelSearch" class="form-control" placeholder="Type a name to filter">
                            </div>
                            <div class="col-12">
                                <div id="panelList" class="border rounded p-2" style="max-height: 220px; overflow:auto;">
                                    @foreach($employees as $employee)
                                        @php
                                            $label = trim(($employee->FirstName ?? '').' '.($employee->LastName ?? ''));
                                            $meta = trim(($employee->department?->Name ?? '').' '.($employee->role?->Name ?? ''));
                                            $isSelected = in_array($employee->Id, $panelists, true);
                                        @endphp
                                        <label class="d-flex align-items-center gap-2 mb-2 panel-item" data-label="{{ strtolower($label.' '.$meta) }}">
                                            <input type="checkbox" name="PanelistIDs[]" value="{{ $employee->Id }}" {{ $isSelected ? 'disabled checked' : '' }}>
                                            <span>{{ $label }} @if($meta) <span class="text-muted">- {{ $meta }}</span> @endif</span>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="form-text">Already added panelists are checked.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Panel Role (optional)</label>
                                <input type="text" name="Role" class="form-control" placeholder="Chair, Member, HR">
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-primary w-100" type="submit">Save Panel</button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-4">
                        <h6 class="mb-2">Current Panel</h6>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($session->panelMembers as $panel)
                                        <tr>
                                            <td>{{ $panel->employee?->FirstName }} {{ $panel->employee?->LastName }}</td>
                                            <td>{{ $panel->Role ?? '-' }}</td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('hr.recruitment.interviews.panel.remove', [$session->Id, $panel->Id]) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted">No panel members assigned.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="mb-2">Panel Notifications</h6>
                        <ul class="mb-0">
                            @forelse($session->notifications as $note)
                                <li>{{ $note->employee?->FirstName }} {{ $note->employee?->LastName }} - {{ $note->Message }}</li>
                            @empty
                                <li class="text-muted">No notifications recorded.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">Session Questions</h6>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.recruitment.interview-question-groups.index') }}">Question Groups</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.interview-questions.create') }}">Add Question</a>
                    </div>
                </div>
                <div class="card-body">
                    @if($questionGroups->isEmpty())
                        <div class="text-muted">No interview questions configured yet. Add questions to build the library.</div>
                    @else
                        <form method="POST" action="{{ route('hr.recruitment.interviews.questions.update', $session->Id) }}">
                            @csrf
                            <div class="border rounded p-3" style="max-height: 300px; overflow:auto;">
                                @foreach($questionGroups as $groupName => $questions)
                                    <div class="mb-3">
                                        <div class="fw-semibold">{{ $groupName }}</div>
                                        @foreach($questions as $question)
                                            <label class="d-flex gap-2 align-items-center mb-2">
                                                <input type="checkbox" name="QuestionIDs[]" value="{{ $question->Id }}"
                                                    {{ in_array($question->Id, $selectedQuestionIds, true) ? 'checked' : '' }}>
                                                <span>
                                                    {{ $question->Title }}
                                                    @if(in_array($question->Id, $openingQuestionIds ?? [], true))
                                                        <span class="badge bg-light text-muted ms-1">Opening</span>
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-outline-primary" type="submit">Save Questions</button>
                            </div>
                        </form>

                        <div class="mt-4">
                            <form method="POST" action="{{ route('hr.recruitment.interviews.questions.assign', $session->Id) }}">
                                @csrf
                                <h6 class="mb-2">Assign Questions to Panelists</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Question</th>
                                                <th style="width: 220px;">Assigned Panelist</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($questionGroups as $groupName => $questions)
                                                @foreach($questions as $question)
                                                    @if(in_array($question->Id, $selectedQuestionIds, true))
                                                        <tr>
                                                            <td>
                                                                <div>{{ $question->Title }}</div>
                                                                @if($question->Guidance)
                                                                    <div class="text-muted small">{{ $question->Guidance }}</div>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <select name="Assignments[{{ $question->Id }}]" class="form-select form-select-sm">
                                                                    <option value="">Unassigned</option>
                                                                    @foreach($session->panelMembers as $panel)
                                                                        <option value="{{ $panel->EmployeeID }}" @selected((($assignments[$question->Id] ?? null)?->PanelistID) == $panel->EmployeeID)>
                                                                            {{ $panel->employee?->FirstName }} {{ $panel->employee?->LastName }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-outline-primary" type="submit">Save Assignments</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Candidates</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('hr.recruitment.interviews.candidates.bulk', $session->Id) }}">
                        @csrf
                        <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
                            <div>
                                <label class="form-label">Bulk Action</label>
                                <select name="Action" class="form-select form-select-sm" required>
                                    <option value="">Select</option>
                                    <option value="next_round">Proceed to Next Round</option>
                                    <option value="offer">Select for Offer</option>
                                </select>
                            </div>
                            <div>
                                <button class="btn btn-outline-primary btn-sm" type="submit">Apply</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 36px;">
                                            <input type="checkbox" id="selectAllCandidates">
                                        </th>
                                        <th>Candidate</th>
                                        <th>Slot</th>
                                        <th>Status</th>
                                        <th class="text-end">Avg Score</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($session->candidates as $candidate)
                                        @php
                                            $avgScore = $scoreSummary[$candidate->Id]->AvgScore ?? null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="CandidateIDs[]" value="{{ $candidate->Id }}">
                                            </td>
                                            <td>{{ $candidate->application?->applicant?->FirstName }} {{ $candidate->application?->applicant?->LastName }}</td>
                                            <td>{{ $candidate->SlotTime ? \Carbon\Carbon::parse($candidate->SlotTime)->format('Y-m-d H:i') : '-' }}</td>
                                            <td>{{ $candidate->Status }}</td>
                                            <td class="text-end">{{ $avgScore !== null ? number_format($avgScore, 2) : '-' }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.interviews.candidates.evaluate', [$session->Id, $candidate->Id]) }}">Evaluate</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted">No candidates scheduled.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const search = document.getElementById('panelSearch');
        const list = document.getElementById('panelList');
        if (!search || !list) return;
        search.addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            list.querySelectorAll('.panel-item').forEach(function (item) {
                const label = item.getAttribute('data-label') || '';
                item.style.display = label.includes(term) ? '' : 'none';
            });
        });
    })();
    (function () {
        const selectAll = document.getElementById('selectAllCandidates');
        if (!selectAll) return;
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('input[name="CandidateIDs[]"]').forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
        });
    })();
</script>
@endsection
