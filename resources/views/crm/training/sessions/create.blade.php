@extends('layouts.app')

@section('title', 'New Training Session')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Training Session</h2>
        <a class="btn btn-outline-secondary" href="{{ route('crm.training.sessions.index') }}">Back</a>
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
            <form method="POST" action="{{ route('crm.training.sessions.store') }}" enctype="multipart/form-data" onsubmit="const btn = document.getElementById('SaveSessionBtn'); if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Program *</label>
                        <select id="ProgramID" name="ProgramID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->Id }}" @selected(old('ProgramID') == $program->Id)>{{ $program->Title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Session Code</label>
                        <input type="text" name="SessionCode" class="form-control" value="{{ old('SessionCode') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="Title" class="form-control" value="{{ old('Title') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="StartDate" class="form-control" value="{{ old('StartDate') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="EndDate" class="form-control" value="{{ old('EndDate') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="StartTime" class="form-control" value="{{ old('StartTime') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Time</label>
                        <input type="time" name="EndTime" class="form-control" value="{{ old('EndTime') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location</label>
                        <input type="text" name="Location" class="form-control" value="{{ old('Location') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Online Link</label>
                        <input type="text" name="OnlineLink" class="form-control" value="{{ old('OnlineLink') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Trainer</label>
                        <select name="TrainerID" class="form-select">
                            <option value="">Select</option>
                            @foreach($trainers as $trainer)
                                <option value="{{ $trainer->Id }}" @selected(old('TrainerID') == $trainer->Id)>{{ $trainer->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max Participants</label>
                        <input type="number" name="MaxParticipants" class="form-control" value="{{ old('MaxParticipants') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            <option value="Planned" @selected(old('Status', 'Planned') === 'Planned')>Planned</option>
                            <option value="Open" @selected(old('Status') === 'Open')>Open for enrollment</option>
                            <option value="Ongoing" @selected(old('Status') === 'Ongoing')>Ongoing</option>
                            <option value="Completed" @selected(old('Status') === 'Completed')>Completed</option>
                            <option value="Cancelled" @selected(old('Status') === 'Cancelled')>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Agenda File</label>
                        <input type="file" name="AgendaFile" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Materials File</label>
                        <input type="file" name="MaterialsFile" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="AutoAddProgramParticipants" name="AutoAddProgramParticipants" value="1" @checked(old('AutoAddProgramParticipants'))>
                            <label class="form-check-label" for="AutoAddProgramParticipants">
                                Auto-add all registered program participants
                            </label>
                        </div>
                        <div class="form-text">If checked, all current participants from the selected program will be added to this session on save.</div>
                    </div>
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
                        <div class="form-text">Type name, ClientID, or ID number. Only participants registered on the selected program will appear.</div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button id="SaveSessionBtn" class="btn btn-primary" type="submit">Save Session</button>
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
        const $program = $('#ProgramID');
        const $clients = $('#ClientIDs');
        const $autoAdd = $('#AutoAddProgramParticipants');
        const $form = $('form[action="{{ route('crm.training.sessions.store') }}"]');
        const $saveBtn = $('#SaveSessionBtn');

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
                        program_id: $program.val() || '',
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results || []
                    };
                }
            }
        });

        $program.on('change', function () {
            $clients.val(null).trigger('change');
        });

        const toggleClientSelector = function () {
            const autoAddChecked = $autoAdd.is(':checked');
            $clients.prop('disabled', autoAddChecked);
        };

        toggleClientSelector();
        $autoAdd.on('change', toggleClientSelector);

        $form.on('submit', function () {
            $saveBtn.prop('disabled', true).text('Saving...');
        });
    });
</script>
@endpush
