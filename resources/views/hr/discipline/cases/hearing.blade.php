@extends('layouts.app')

@section('title', 'Hearing')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Hearing</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.discipline.cases.show', $case->Id) }}">Back</a>
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
            <h5 class="mb-3">Investigation Summary</h5>
            @if($investigation)
                <div class="row g-3">
                    <div class="col-md-4">
                        <strong>Status:</strong>
                        <div>{{ $investigation->Status ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Investigator:</strong>
                        <div>
                            @if($investigation->InvestigatorType === 'External')
                                {{ $investigation->InvestigatorName ?? '-' }}
                            @else
                                {{ $investigation->investigator?->FirstName }} {{ $investigation->investigator?->LastName }}
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <strong>Dates:</strong>
                        <div>
                            {{ $investigation->StartDate?->format('Y-m-d') ?? '-' }}
                            @if($investigation->EndDate)
                                to {{ $investigation->EndDate->format('Y-m-d') }}
                            @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <strong>Findings Summary:</strong>
                        <div>{{ $investigation->FindingsSummary ?? '-' }}</div>
                    </div>
                    @if($investigation->reportDocument)
                        <div class="col-12">
                            <a href="{{ route('file.preview', ['document' => $investigation->reportDocument->DocumentId]) }}" target="_blank">View Investigation Report</a>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-muted">No investigation captured yet.</div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form id="hearingForm" action="{{ route('hr.discipline.cases.hearing.store', $case->Id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Hearing Date *</label>
                        <input type="datetime-local" name="HearingDate" class="form-control" value="{{ old('HearingDate', $hearing?->HearingDate?->format('Y-m-d\\TH:i')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Venue</label>
                        <input type="text" name="Venue" class="form-control" value="{{ old('Venue', $hearing->Venue ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">HR Facilitator</label>
                        <select name="HRFacilitatorID" class="form-select">
                            <option value="">Select</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->Id }}" @selected(old('HRFacilitatorID', $hearing->HRFacilitatorID ?? null) == $employee->Id)>
                                    {{ $employee->FirstName }} {{ $employee->LastName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Employee Representative</label>
                        <input type="text" name="EmployeeRepName" class="form-control" value="{{ old('EmployeeRepName', $hearing->EmployeeRepName ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input type="text" name="Status" class="form-control" value="{{ old('Status', $hearing->Status ?? 'Scheduled') }}">
                    </div>
                    @if($hearing)
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="CreateSubsequent" value="1" id="createSubsequent">
                                <label class="form-check-label" for="createSubsequent">Schedule as subsequent hearing (creates a new hearing record)</label>
                            </div>
                        </div>
                    @endif
                </div>

            </form>
        </div>
    </div>

    @if($hearing)
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">Panel Members</h5>
                        <form action="{{ route('hr.discipline.cases.hearing.panel', $case->Id) }}" method="POST">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <select name="PanelMembers[]" class="form-select" multiple required>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->Id }}">{{ $employee->FirstName }} {{ $employee->LastName }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Hold Ctrl/Cmd to select multiple.</small>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="PanelRoles[]" class="form-control" placeholder="Role (optional)">
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-outline-primary">Add Panel</button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-3">
                            <ul class="list-unstyled">
                                @forelse($panel as $member)
                                    <li>{{ $member->employee?->FirstName }} {{ $member->employee?->LastName }} {{ $member->Role ? '(' . $member->Role . ')' : '' }}</li>
                                @empty
                                    <li class="text-muted">No panel members yet.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">Minutes</h5>
                        <form action="{{ route('hr.discipline.cases.hearing.minutes', $case->Id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-2">
                                <div class="col-12">
                                    <input type="file" name="MinutesDocument" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <textarea name="PanelRecommendation" class="form-control" rows="3" placeholder="Panel recommendation">{{ old('PanelRecommendation', $hearing->PanelRecommendation ?? '') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-outline-primary">Upload Minutes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-4 d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary" form="hearingForm">Save Hearing</button>
    </div>

    @if($hearing)
        @if(($hearings ?? collect())->count() > 1)
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h5 class="mb-3">Hearing History</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Venue</th>
                                    <th>Facilitator</th>
                                    <th>Status</th>
                                    <th>Panel</th>
                                    <th>Minutes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hearings as $index => $item)
                                    <tr @class(['table-success' => $index === 0])>
                                        <td>{{ $hearings->count() - $index }}</td>
                                        <td>{{ $item->HearingDate?->format('Y-m-d H:i') ?? '-' }}</td>
                                        <td>{{ $item->Venue ?? '-' }}</td>
                                        <td>{{ $item->facilitator?->FirstName }} {{ $item->facilitator?->LastName }}</td>
                                        <td>{{ $item->Status ?? '-' }}</td>
                                        <td>{{ $item->panelMembers?->count() ?? 0 }}</td>
                                        <td>
                                            @if($item->minutes)
                                                <a href="{{ route('file.preview', ['document' => $item->minutes->DocumentId]) }}" target="_blank">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">Latest hearing highlighted.</small>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
