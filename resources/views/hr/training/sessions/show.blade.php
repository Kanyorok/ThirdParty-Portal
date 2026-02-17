@extends('layouts.app')

@section('title', 'Training Session')

@section('content')
@php
    $rsvpStatuses = ['Nominated', 'Approved', 'Confirmed', 'Declined', 'Withdrawn', 'Completed'];
    $attendanceStatuses = ['Present', 'Late', 'Absent', 'No-show'];
    $canUpdateRsvp = in_array($session->Status, ['Planned', 'Open'], true);
    $canMarkAttendance = in_array($session->Status, ['Ongoing', 'Completed'], true);
@endphp
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Training Session</h2>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('hr.training.sessions.edit', $session->Id) }}">Edit</a>
            <a class="btn btn-outline-secondary" href="{{ route('hr.training.sessions.index') }}">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <h5 class="mb-1">{{ $session->Title ?? $session->SessionCode ?? ('Session #' . $session->Id) }}</h5>
                    <div class="text-muted">{{ $session->program?->Title ?? '-' }}</div>
                    <div class="mt-2">
                        <strong>Date:</strong>
                        {{ $session->StartDate?->format('Y-m-d') ?? '-' }}
                        @if($session->EndDate && $session->EndDate->format('Y-m-d') !== $session->StartDate?->format('Y-m-d'))
                            - {{ $session->EndDate->format('Y-m-d') }}
                        @endif
                        <span class="ms-3"><strong>Time:</strong> {{ $session->StartTime ?? '-' }} - {{ $session->EndTime ?? '-' }}</span>
                    </div>
                    <div class="mt-2"><strong>Location:</strong> {{ $session->Location ?? '-' }}</div>
                    <div><strong>Online Link:</strong> {{ $session->OnlineLink ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div><strong>Trainer:</strong> {{ $session->trainer?->Name ?? '-' }}</div>
                    <div><strong>Status:</strong> {{ $session->Status }}</div>
                    <div><strong>Max Participants:</strong> {{ $session->MaxParticipants ?? '-' }}</div>
                    <div><strong>Participants:</strong> {{ $participants->count() }}</div>
                    <div class="mt-2">
                        @if($session->agendaDocument)
                            <div><a href="{{ route('file.preview', ['document' => $session->agendaDocument->DocumentId]) }}" target="_blank">View Agenda</a></div>
                        @endif
                        @if($session->materialsDocument)
                            <div><a href="{{ route('file.preview', ['document' => $session->materialsDocument->DocumentId]) }}" target="_blank">View Materials</a></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Enrolled Participants</h5>
            <div class="text-muted small mb-2">
                RSVP updates are available before the session starts. Attendance can be marked once the session is ongoing or completed.
            </div>
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>RSVP Status</th>
                            <th>Attendance (Actual)</th>
                            <th>Marked On</th>
                            <th>Certificate</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $participant)
                            @php($cert = $certificates[$participant->EmployeeID] ?? null)
                            @php($allowAttendance = $canMarkAttendance && in_array($participant->Status, ['Approved', 'Confirmed', 'Completed'], true))
                            <tr>
                                <td>{{ $participant->employee?->FirstName }} {{ $participant->employee?->LastName }}</td>
                                <td>{{ $participant->Status ?? '-' }}</td>
                                <td>{{ $participant->AttendanceStatus ?? '-' }}</td>
                                <td>{{ $participant->AttendanceMarkedOn?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>
                                    @if($cert)
                                        <span class="text-success">Issued</span>
                                    @elseif($session->program?->HasCertification)
                                        <span class="text-muted">Pending</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('hr.training.sessions.participants.update', [$session->Id, $participant->Id]) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="Action" value="rsvp">
                                        <select name="Status" class="form-select form-select-sm d-inline w-auto" @disabled(!$canUpdateRsvp)>
                                            <option value="">RSVP</option>
                                            @foreach($rsvpStatuses as $status)
                                                <option value="{{ $status }}" @selected($participant->Status === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-outline-primary ms-1" type="submit" @disabled(!$canUpdateRsvp)>Update RSVP</button>
                                    </form>

                                    <form method="POST" action="{{ route('hr.training.sessions.participants.update', [$session->Id, $participant->Id]) }}" class="d-inline ms-2">
                                        @csrf
                                        <input type="hidden" name="Action" value="attendance">
                                        <select name="AttendanceStatus" class="form-select form-select-sm d-inline w-auto" @disabled(!$allowAttendance)>
                                            <option value="">Attendance</option>
                                            @foreach($attendanceStatuses as $status)
                                                <option value="{{ $status }}" @selected($participant->AttendanceStatus === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-outline-primary ms-1" type="submit" @disabled(!$allowAttendance)>Mark Attendance</button>
                                    </form>

                                    @if($session->program?->HasCertification && !$cert)
                                        <details class="d-inline-block ms-2">
                                            <summary class="btn btn-sm btn-outline-secondary">Issue Certificate</summary>
                                            <form method="POST" action="{{ route('hr.training.sessions.participants.certificate', [$session->Id, $participant->Id]) }}" enctype="multipart/form-data" class="mt-2">
                                                @csrf
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <input type="text" name="CertificationName" class="form-control form-control-sm" placeholder="Certification name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" name="CertificateNumber" class="form-control form-control-sm" placeholder="Certificate number">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="date" name="IssuedOn" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="date" name="ExpiresOn" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="file" name="CertificateFile" class="form-control form-control-sm">
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-primary" type="submit">Save</button>
                                                </div>
                                            </form>
                                        </details>
                                    @elseif($cert && $cert->document)
                                        <a class="btn btn-sm btn-outline-secondary ms-2" href="{{ route('file.preview', ['document' => $cert->document->DocumentId]) }}" target="_blank">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No participants yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <h5 class="mb-3">Session Feedback</h5>
            <form method="POST" action="{{ route('hr.training.sessions.feedback', $session->Id) }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Content (1-5)</th>
                                <th>Trainer (1-5)</th>
                                <th>Relevance (1-5)</th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($participants as $participant)
                                @php($fb = $feedback[$participant->EmployeeID] ?? null)
                                <tr>
                                    <td>{{ $participant->employee?->FirstName }} {{ $participant->employee?->LastName }}</td>
                                    <td>
                                        <input type="number" min="1" max="5" name="Feedback[{{ $participant->EmployeeID }}][RatingContent]"
                                               class="form-control form-control-sm"
                                               value="{{ old('Feedback.' . $participant->EmployeeID . '.RatingContent', $fb->RatingContent ?? '') }}">
                                    </td>
                                    <td>
                                        <input type="number" min="1" max="5" name="Feedback[{{ $participant->EmployeeID }}][RatingTrainer]"
                                               class="form-control form-control-sm"
                                               value="{{ old('Feedback.' . $participant->EmployeeID . '.RatingTrainer', $fb->RatingTrainer ?? '') }}">
                                    </td>
                                    <td>
                                        <input type="number" min="1" max="5" name="Feedback[{{ $participant->EmployeeID }}][RatingRelevance]"
                                               class="form-control form-control-sm"
                                               value="{{ old('Feedback.' . $participant->EmployeeID . '.RatingRelevance', $fb->RatingRelevance ?? '') }}">
                                    </td>
                                    <td>
                                        <input type="text" name="Feedback[{{ $participant->EmployeeID }}][Comments]"
                                               class="form-control form-control-sm"
                                               value="{{ old('Feedback.' . $participant->EmployeeID . '.Comments', $fb->Comments ?? '') }}">
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No participants yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button class="btn btn-outline-primary" type="submit">Save Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
