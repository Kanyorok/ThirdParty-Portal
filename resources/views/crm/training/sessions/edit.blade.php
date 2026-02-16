@extends('layouts.app')

@section('title', 'Edit Training Session')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Training Session</h2>
        <a class="btn btn-outline-secondary" href="{{ route('crm.training.sessions.show', $session->Id) }}">Back</a>
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
            <form method="POST" action="{{ route('crm.training.sessions.update', $session->Id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Program *</label>
                        <select id="ProgramID" name="ProgramID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->Id }}" @selected(old('ProgramID', $session->ProgramID) == $program->Id)>{{ $program->Title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Session Code</label>
                        <input type="text" name="SessionCode" class="form-control" value="{{ old('SessionCode', $session->SessionCode) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="Title" class="form-control" value="{{ old('Title', $session->Title) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="StartDate" class="form-control" value="{{ old('StartDate', optional($session->StartDate)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="EndDate" class="form-control" value="{{ old('EndDate', optional($session->EndDate)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="StartTime" class="form-control" value="{{ old('StartTime', $session->StartTime) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Time</label>
                        <input type="time" name="EndTime" class="form-control" value="{{ old('EndTime', $session->EndTime) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location</label>
                        <input type="text" name="Location" class="form-control" value="{{ old('Location', $session->Location) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Online Link</label>
                        <input type="text" name="OnlineLink" class="form-control" value="{{ old('OnlineLink', $session->OnlineLink) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Trainer</label>
                        <select name="TrainerID" class="form-select">
                            <option value="">Select</option>
                            @foreach($trainers as $trainer)
                                <option value="{{ $trainer->Id }}" @selected(old('TrainerID', $session->TrainerID) == $trainer->Id)>{{ $trainer->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max Participants</label>
                        <input type="number" name="MaxParticipants" class="form-control" value="{{ old('MaxParticipants', $session->MaxParticipants) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            <option value="Planned" @selected(old('Status', $session->Status) === 'Planned')>Planned</option>
                            <option value="Open" @selected(old('Status', $session->Status) === 'Open')>Open for enrollment</option>
                            <option value="Ongoing" @selected(old('Status', $session->Status) === 'Ongoing')>Ongoing</option>
                            <option value="Completed" @selected(old('Status', $session->Status) === 'Completed')>Completed</option>
                            <option value="Cancelled" @selected(old('Status', $session->Status) === 'Cancelled')>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Agenda File</label>
                        <input type="file" name="AgendaFile" class="form-control">
                        @if($session->agendaDocument)
                            <div class="mt-1">
                                Current: <a href="{{ route('file.preview', ['document' => $session->agendaDocument->DocumentId]) }}" target="_blank">{{ $session->agendaDocument->Name }}</a>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Materials File</label>
                        <input type="file" name="MaterialsFile" class="form-control">
                        @if($session->materialsDocument)
                            <div class="mt-1">
                                Current: <a href="{{ route('file.preview', ['document' => $session->materialsDocument->DocumentId]) }}" target="_blank">{{ $session->materialsDocument->Name }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <button id="UpdateSessionBtn" class="btn btn-primary" type="submit">Update Session</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Add Participants</h5>
                @if($remainingSlots === null || $remainingSlots > 0)
                    <form method="POST" action="{{ route('crm.training.sessions.participants.add-all', $session->Id) }}">
                        @csrf
                        <button class="btn btn-outline-primary" type="submit">Add All Program Participants</button>
                    </form>
                @endif
            </div>
            <div class="mb-2">
                <small class="text-muted">
                    Current participants: {{ number_format($currentParticipantsCount) }}
                    @if($remainingSlots !== null)
                        | Max: {{ number_format((int) $session->MaxParticipants) }} | Remaining slots: {{ number_format($remainingSlots) }}
                    @endif
                </small>
            </div>
            <form method="POST" action="{{ route('crm.training.sessions.participants', $session->Id) }}">
                @csrf
                <input type="hidden" id="SessionProgramID" value="{{ $session->ProgramID }}">
                <div class="row g-3 mt-1">
                    <div class="col-md-12">
                        <label class="form-label">Select Clients</label>
                        <select id="ClientIDs" name="ClientIDs[]" class="form-select" multiple>
                            @foreach($selectedClients as $client)
                                @php
                                    $idNumber = $client->individual?->PassportNo ?? $client->corporate?->CertificateNo;
                                @endphp
                                <option value="{{ $client->ClientID }}" selected>
                                    {{ $client->Name }} ({{ $client->ClientID }}){{ $idNumber ? ' - '.$idNumber : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Type name, ClientID, or ID number. Only participants registered on this session's saved program are shown.</div>
                        @if((int) old('ProgramID', $session->ProgramID) !== (int) $session->ProgramID)
                            <div class="form-text text-warning">You changed the session program above. Save session changes first, then add participants for the new program.</div>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    @if($remainingSlots !== null && $remainingSlots <= 0)
                        <button class="btn btn-outline-primary" type="submit" disabled>Session Full</button>
                    @else
                        <button class="btn btn-outline-primary" type="submit">Add Selected Participants</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const $clients = $('#ClientIDs');
        const programId = $('#SessionProgramID').val() || '';
        const $form = $('form[action="{{ route('crm.training.sessions.update', $session->Id) }}"]');
        const $updateBtn = $('#UpdateSessionBtn');

        $clients.select2({
            width: '100%',
            placeholder: 'Search program participants...',
            minimumInputLength: 1,
            ajax: {
                url: '{{ route('crm.training.sessions.program-clients') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || '',
                        program_id: programId,
                        session_id: '{{ $session->Id }}',
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results || []
                    };
                }
            }
        });

        $form.on('submit', function () {
            $updateBtn.prop('disabled', true).text('Saving...');
        });
    });
</script>
@endpush
