@extends('layouts.app')

@section('title', 'Training Session')

@section('content')
@php
    $attendanceStatuses = [
        'Present' => 'Attended',
        'Absent' => 'Not Attended',
    ];
    $canMarkAttendance = $session->Status !== 'Cancelled';
    $programCertificateScope = $session->program?->CertificateScope ?? 'Session';
    $isProgramCertificateScope = $programCertificateScope === 'Program';
    $bulkStatus = strtolower((string) ($bulkCertificateState['status'] ?? 'idle'));
    $bulkTotal = (int) ($bulkCertificateState['total'] ?? 0);
    $bulkProcessed = (int) ($bulkCertificateState['processed'] ?? 0);
    $bulkPercent = $bulkTotal > 0 ? (int) min(100, floor(($bulkProcessed / $bulkTotal) * 100)) : 0;
    $bulkIsRunning = in_array($bulkStatus, ['processing', 'cancelling'], true);
    $bulkStatusLabel = match ($bulkStatus) {
        'processing' => 'Processing',
        'cancelling' => 'Stopping',
        'completed' => 'Completed',
        'completed_with_errors' => 'Completed With Errors',
        'cancelled' => 'Stopped',
        'failed' => 'Failed',
        default => 'Idle',
    };
    $bulkStatusClass = match ($bulkStatus) {
        'processing', 'cancelling' => 'alert-info',
        'completed' => 'alert-success',
        'completed_with_errors', 'cancelled', 'failed' => 'alert-warning',
        default => 'alert-light',
    };
@endphp
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Training Session</h2>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('crm.training.certificate-templates.index') }}">Templates</a>
            <a class="btn btn-outline-primary" href="{{ route('crm.training.sessions.feedback.form', $session->Id) }}">Take Feedback</a>
            <a class="btn btn-outline-secondary" href="{{ route('crm.training.sessions.edit', $session->Id) }}">Edit</a>
            <a class="btn btn-outline-secondary" href="{{ route('crm.training.sessions.index') }}">Back</a>
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

    <div class="card shadow-sm session-participants-card">
        <div class="card-body">
            <h5 class="mb-3">Enrolled Participants</h5>
            <div class="text-muted small mb-2">
                Mark attendance per participant or use the bulk buttons to set all participants as attended or not attended.
            </div>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <form method="POST" action="{{ route('crm.training.sessions.participants.bulk-attendance', $session->Id) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="AttendanceStatus" value="Present">
                    <button class="btn btn-sm btn-outline-primary" type="submit"
                            onclick="return confirm('Mark all participants as attended?')"
                            @disabled(!$canMarkAttendance || $participants->isEmpty())>
                        Mark All Attended
                    </button>
                </form>
                <form method="POST" action="{{ route('crm.training.sessions.participants.bulk-attendance', $session->Id) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="AttendanceStatus" value="Absent">
                    <button class="btn btn-sm btn-outline-secondary" type="submit"
                            onclick="return confirm('Mark all participants as not attended?')"
                            @disabled(!$canMarkAttendance || $participants->isEmpty())>
                        Mark All Not Attended
                    </button>
                </form>
                @if(!$canMarkAttendance)
                    <span class="text-muted small align-self-center">Attendance is disabled for cancelled sessions.</span>
                @endif
            </div>
            @if($session->program?->HasCertification && !$isProgramCertificateScope)
                <div class="border rounded p-3 mb-3 bg-light-subtle">
                    <form method="POST"
                          action="{{ route('crm.training.sessions.participants.bulk-certificate', $session->Id) }}"
                          class="row g-2 align-items-end js-bulk-certificate-form"
                          data-status-url="{{ route('crm.training.sessions.participants.bulk-certificate.status', $session->Id) }}"
                          data-cancel-url="{{ route('crm.training.sessions.participants.bulk-certificate.cancel', $session->Id) }}">
                        @csrf
                        <div class="col-xl-3 col-lg-4">
                            <label class="form-label form-label-sm mb-1">Common Template *</label>
                            <select name="CertificateTemplateID" class="form-select form-select-sm" required>
                                <option value="">Select template</option>
                                @foreach($certificateTemplates as $template)
                                    <option value="{{ $template->Id }}">{{ $template->Name }}{{ $template->IsSample ? ' (Sample)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-4">
                            <label class="form-label form-label-sm mb-1">Certification Name</label>
                            <input type="text" name="CertificationName" class="form-control form-control-sm" placeholder="Optional">
                        </div>
                        <div class="col-xl-2 col-lg-4">
                            <label class="form-label form-label-sm mb-1">Issuing Body</label>
                            <input type="text" name="IssuingBody" class="form-control form-control-sm" placeholder="Optional">
                        </div>
                        <div class="col-xl-2 col-lg-3">
                            <label class="form-label form-label-sm mb-1">Issue Date</label>
                            <input type="date" name="IssuedOn" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-xl-2 col-lg-3">
                            <label class="form-label form-label-sm mb-1">Expiry Date</label>
                            <input type="date" name="ExpiresOn" class="form-control form-control-sm">
                        </div>
                        <div class="col-xl-1 col-lg-3">
                            <label class="form-label form-label-sm mb-1">No Prefix</label>
                            <input type="text" name="CertificateNumberPrefix" class="form-control form-control-sm" placeholder="CERT-2026">
                        </div>
                        <div class="col-xl-12 d-flex flex-wrap align-items-center gap-3 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="OnlyAttended" name="OnlyAttended" value="1" checked>
                                <label class="form-check-label small" for="OnlyAttended">Issue only for attended participants</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="BulkSendEmail" name="SendEmail" value="1">
                                <label class="form-check-label small" for="BulkSendEmail">Auto-send certificate email after generation</label>
                            </div>
                            <button class="btn btn-sm btn-primary ms-auto js-bulk-certificate-submit" type="submit"
                                    onclick="return confirm('Issue certificates in bulk using the selected template?')"
                                    data-base-disabled="{{ $participants->isEmpty() ? 1 : 0 }}"
                                    @disabled($bulkIsRunning || $participants->isEmpty())>
                                Mass Issue Certificates
                            </button>
                        </div>
                        <div class="col-12">
                            <div class="form-text mb-0">Bulk issue skips participants who already have certificates in this session.</div>
                        </div>
                    </form>
                    <div class="alert {{ $bulkStatusClass }} mt-3 mb-0 js-bulk-certificate-status"
                         data-state="{{ $bulkStatus }}">
                        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold">
                                    Bulk Issue Status:
                                    <span class="js-bulk-certificate-status-label">{{ $bulkStatusLabel }}</span>
                                </div>
                                <div class="small js-bulk-certificate-status-message">
                                    {{ $bulkCertificateState['message'] ?: 'No active bulk certificate run.' }}
                                </div>
                            </div>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger js-bulk-certificate-stop {{ $bulkIsRunning ? '' : 'd-none' }}">
                                Stop Processing
                            </button>
                        </div>
                        <div class="progress mt-2 js-bulk-certificate-progress-wrap {{ $bulkTotal > 0 ? '' : 'd-none' }}">
                            <div class="progress-bar js-bulk-certificate-progress-bar"
                                 role="progressbar"
                                 style="width: {{ $bulkPercent }}%;"
                                 aria-valuemin="0"
                                 aria-valuemax="100"
                                 aria-valuenow="{{ $bulkPercent }}">
                                {{ $bulkPercent }}%
                            </div>
                        </div>
                        <div class="small mt-2 js-bulk-certificate-counters">
                            Processed {{ $bulkProcessed }} / {{ $bulkTotal }},
                            Issued {{ (int) ($bulkCertificateState['issued'] ?? 0) }},
                            Skipped {{ (int) ($bulkCertificateState['skipped'] ?? 0) }},
                            Failed {{ (int) ($bulkCertificateState['failed'] ?? 0) }}.
                            @if((bool) ($bulkCertificateState['send_email'] ?? false))
                                Emails Sent {{ (int) ($bulkCertificateState['emails_sent'] ?? 0) }},
                                Email Failures {{ (int) ($bulkCertificateState['emails_failed'] ?? 0) }}.
                            @endif
                        </div>
                    </div>
                </div>
            @elseif($session->program?->HasCertification && $isProgramCertificateScope)
                <div class="alert alert-info mb-3">
                    This program uses <strong>course/program-level certification</strong>. Manage certificate issuance from
                    <a href="{{ route('crm.training.programs.certification', $session->ProgramID) }}" class="alert-link">Program Certification</a>.
                </div>
            @endif
            <div class="session-table-scroll-helper js-participants-scroll-top d-none" aria-hidden="true">
                <div class="session-table-scroll-helper-inner js-participants-scroll-top-inner"></div>
            </div>
            <div class="table-responsive js-participants-scroll-body">
                <table class="table table-striped mb-0 align-middle js-participants-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Client ID</th>
                            <th>Attendance (Actual)</th>
                            <th>Marked On</th>
                            <th>Certificate</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $participant)
                            @php($cert = $certificates[$participant->ClientID] ?? null)
                            <tr>
                                <td>{{ $participant->client?->Name ?? $participant->ClientID }}</td>
                                <td>{{ $participant->ClientID }}</td>
                                <td>{{ $participant->AttendanceStatus ?? '-' }}</td>
                                <td>{{ $participant->AttendanceMarkedOn?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>
                                    @if($cert)
                                        <span class="text-success">Issued</span>
                                    @elseif($session->program?->HasCertification && $isProgramCertificateScope)
                                        <span class="text-muted">Program Scope</span>
                                    @elseif($session->program?->HasCertification)
                                        <span class="text-muted">Pending</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('crm.training.sessions.participants.update', [$session->Id, $participant->Id]) }}" class="d-inline">
                                        @csrf
                                        <select name="AttendanceStatus" class="form-select form-select-sm d-inline w-auto" @disabled(!$canMarkAttendance)>
                                            @if($participant->AttendanceStatus && !array_key_exists($participant->AttendanceStatus, $attendanceStatuses))
                                                <option value="{{ $participant->AttendanceStatus }}" selected>{{ $participant->AttendanceStatus }}</option>
                                            @endif
                                            @foreach($attendanceStatuses as $statusValue => $statusLabel)
                                                <option value="{{ $statusValue }}" @selected($participant->AttendanceStatus === $statusValue)>{{ $statusLabel }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-outline-primary ms-1" type="submit" @disabled(!$canMarkAttendance)>Save Attendance</button>
                                    </form>

                                    @if($session->program?->HasCertification && !$isProgramCertificateScope && !$cert)
                                        <details class="d-inline-block ms-2">
                                            <summary class="btn btn-sm btn-outline-secondary">Issue Certificate</summary>
                                            <form method="POST"
                                                  action="{{ route('crm.training.sessions.participants.certificate', [$session->Id, $participant->Id]) }}"
                                                  enctype="multipart/form-data"
                                                  class="mt-2 js-issue-certificate-form"
                                                  data-participant-name="{{ $participant->client?->Name ?? $participant->ClientID }}"
                                                  data-client-id="{{ $participant->ClientID }}"
                                                  data-program-name="{{ $session->program?->Title ?? '' }}"
                                                  data-session-title="{{ $session->Title ?? $session->SessionCode ?? ('Session #' . $session->Id) }}">
                                                @csrf
                                                <div class="row g-2">
                                                    <div class="col-md-12">
                                                        <label class="form-label form-label-sm mb-1">Template</label>
                                                        <select name="CertificateTemplateID" class="form-select form-select-sm js-cert-template-select">
                                                            <option value="">No template</option>
                                                            @foreach($certificateTemplates as $template)
                                                                <option value="{{ $template->Id }}"
                                                                        data-template-name="{{ $template->Name }}"
                                                                        data-template-description="{{ $template->Description ?? '' }}"
                                                                        data-template-body="{{ $template->TemplateBody ?? '' }}"
                                                                        data-default-issuing-body="{{ $template->DefaultIssuingBody ?? '' }}"
                                                                        data-default-validity-months="{{ $template->DefaultValidityMonths ?? '' }}">
                                                                    {{ $template->Name }}{{ $template->IsSample ? ' (Sample)' : '' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="form-text">Select a sample/custom template, or upload a new one below.</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" name="CertificateTemplateName" class="form-control form-control-sm" placeholder="Uploaded template name (optional)">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="file" name="CertificateTemplateFile" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.svg,.pdf" title="Upload template file">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" name="CertificationName" class="form-control form-control-sm js-cert-name" placeholder="Certification name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" name="IssuingBody" class="form-control form-control-sm js-cert-issuer" placeholder="Issuing body">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" name="CertificateNumber" class="form-control form-control-sm js-cert-number" placeholder="Certificate number">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="date" name="IssuedOn" class="form-control form-control-sm js-cert-issued-on">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="date" name="ExpiresOn" class="form-control form-control-sm js-cert-expires-on">
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="file" name="CertificateFile" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                                        <div class="form-text">Leave empty to auto-generate a printable certificate from the selected template.</div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="SendEmail_{{ $participant->Id }}" name="SendEmail" value="1">
                                                            <label class="form-check-label small" for="SendEmail_{{ $participant->Id }}">Send certificate via email after issue</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="border rounded p-2 small bg-light">
                                                            <div class="fw-semibold mb-1">Template Preview</div>
                                                            <div class="text-muted js-cert-template-preview">Select a template to preview certificate wording.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-primary" type="submit">Issue Certificate</button>
                                                </div>
                                            </form>
                                        </details>
                                    @elseif($session->program?->HasCertification && $isProgramCertificateScope)
                                        <a class="btn btn-sm btn-outline-secondary ms-2"
                                           href="{{ route('crm.training.programs.certification', $session->ProgramID) }}">
                                            Program Certs
                                        </a>
                                    @elseif($cert && $cert->document)
                                        <a class="btn btn-sm btn-outline-secondary ms-2" href="{{ route('file.preview', ['document' => $cert->document->DocumentId]) }}" target="_blank">View / Print</a>
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
</div>
@endsection

@push('styles')
<style>
    .session-participants-card {
        overflow: visible;
    }

    .session-table-scroll-helper {
        height: 14px;
        overflow-x: auto;
        overflow-y: hidden;
        margin-bottom: 0.5rem;
        border: 1px solid #d9dde3;
        border-radius: 999px;
        background: #f4f6fa;
        position: sticky;
        top: 0.5rem;
        z-index: 20;
    }

    .session-table-scroll-helper-inner {
        height: 1px;
    }

    .session-table-scroll-helper::-webkit-scrollbar {
        height: 10px;
    }

    .session-table-scroll-helper::-webkit-scrollbar-thumb {
        background: #b6bfd0;
        border-radius: 999px;
    }

    .js-participants-scroll-body {
        max-width: 100%;
        overflow-x: auto;
    }

    .js-participants-table {
        width: max-content;
        min-width: 1800px;
    }

    .js-participants-table th,
    .js-participants-table td {
        white-space: nowrap;
    }

    .js-participants-table td:last-child {
        min-width: 560px;
    }

    .js-participants-table details {
        position: relative;
    }

    .js-participants-table details[open] > form {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        width: 720px;
        max-width: min(82vw, 720px);
        background: #ffffff;
        border: 1px solid #d9dde3;
        border-radius: 10px;
        padding: 12px;
        z-index: 30;
        box-shadow: 0 10px 24px rgba(15, 28, 63, 0.16);
    }

    .js-participants-table details[open] > summary {
        position: relative;
        z-index: 31;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addMonths = function (dateValue, monthsValue) {
            if (!dateValue || !monthsValue) {
                return '';
            }
            const months = parseInt(monthsValue, 10);
            if (isNaN(months) || months <= 0) {
                return '';
            }

            const date = new Date(dateValue + 'T00:00:00');
            if (isNaN(date.getTime())) {
                return '';
            }

            date.setMonth(date.getMonth() + months);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return year + '-' + month + '-' + day;
        };

        const formatDateLabel = function (dateValue) {
            if (!dateValue) {
                return '';
            }
            const date = new Date(dateValue + 'T00:00:00');
            if (isNaN(date.getTime())) {
                return dateValue;
            }

            return date.toISOString().slice(0, 10);
        };

        document.querySelectorAll('.js-issue-certificate-form').forEach(function (form) {
            const select = form.querySelector('.js-cert-template-select');
            const certNameInput = form.querySelector('.js-cert-name');
            const issuerInput = form.querySelector('.js-cert-issuer');
            const certNoInput = form.querySelector('.js-cert-number');
            const issuedOnInput = form.querySelector('.js-cert-issued-on');
            const expiresOnInput = form.querySelector('.js-cert-expires-on');
            const preview = form.querySelector('.js-cert-template-preview');
            const token = function (name) {
                return '{' + '{' + name + '}' + '}';
            };

            if (!select || !preview) {
                return;
            }

            const renderPreview = function () {
                const selected = select.options[select.selectedIndex];
                const templateBody = selected ? (selected.getAttribute('data-template-body') || selected.getAttribute('data-template-description') || '') : '';
                const templateName = selected ? (selected.getAttribute('data-template-name') || '') : '';
                const defaultIssuer = selected ? (selected.getAttribute('data-default-issuing-body') || '') : '';
                const defaultValidityMonths = selected ? (selected.getAttribute('data-default-validity-months') || '') : '';

                if (certNameInput && certNameInput.value.trim() === '' && templateName !== '') {
                    certNameInput.value = templateName;
                }

                if (issuerInput && issuerInput.value.trim() === '' && defaultIssuer !== '') {
                    issuerInput.value = defaultIssuer;
                }

                const issuedOn = issuedOnInput && issuedOnInput.value ? issuedOnInput.value : '';
                if (expiresOnInput && expiresOnInput.value === '' && issuedOn !== '' && defaultValidityMonths !== '') {
                    const calculatedExpiry = addMonths(issuedOn, defaultValidityMonths);
                    if (calculatedExpiry !== '') {
                        expiresOnInput.value = calculatedExpiry;
                    }
                }

                if (templateBody.trim() === '') {
                    preview.textContent = 'Select a template to preview content.';
                    return;
                }

                const replacements = {
                    [token('participant_name')]: form.dataset.participantName || '',
                    [token('client_id')]: form.dataset.clientId || '',
                    [token('program_name')]: form.dataset.programName || '',
                    [token('session_title')]: form.dataset.sessionTitle || '',
                    [token('issue_date')]: formatDateLabel(issuedOnInput ? issuedOnInput.value : ''),
                    [token('expiry_date')]: formatDateLabel(expiresOnInput ? expiresOnInput.value : ''),
                    [token('certificate_number')]: certNoInput ? certNoInput.value : '',
                    [token('issuing_body')]: issuerInput ? issuerInput.value : '',
                };

                let rendered = templateBody;
                Object.keys(replacements).forEach(function (token) {
                    rendered = rendered.split(token).join(replacements[token]);
                });

                preview.textContent = rendered;
            };

            select.addEventListener('change', renderPreview);
            if (issuedOnInput) {
                issuedOnInput.addEventListener('change', renderPreview);
            }
            if (expiresOnInput) {
                expiresOnInput.addEventListener('change', renderPreview);
            }
            if (certNoInput) {
                certNoInput.addEventListener('input', renderPreview);
            }
            if (issuerInput) {
                issuerInput.addEventListener('input', renderPreview);
            }

            renderPreview();
        });

        const bulkForm = document.querySelector('.js-bulk-certificate-form');
        const bulkStatusBox = document.querySelector('.js-bulk-certificate-status');
        const bulkStatusLabel = document.querySelector('.js-bulk-certificate-status-label');
        const bulkStatusMessage = document.querySelector('.js-bulk-certificate-status-message');
        const bulkProgressWrap = document.querySelector('.js-bulk-certificate-progress-wrap');
        const bulkProgressBar = document.querySelector('.js-bulk-certificate-progress-bar');
        const bulkCounters = document.querySelector('.js-bulk-certificate-counters');
        const bulkSubmitButton = document.querySelector('.js-bulk-certificate-submit');
        const bulkStopButton = document.querySelector('.js-bulk-certificate-stop');

        if (bulkForm && bulkStatusBox && bulkStatusLabel && bulkStatusMessage && bulkProgressWrap && bulkProgressBar && bulkCounters && bulkSubmitButton && bulkStopButton) {
            const statusUrl = bulkForm.dataset.statusUrl || '';
            const cancelUrl = bulkForm.dataset.cancelUrl || '';
            const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';
            const runningStatuses = ['processing', 'cancelling'];
            const defaultSubmitText = bulkSubmitButton.textContent.trim();
            const submitBaseDisabled = bulkSubmitButton.dataset.baseDisabled === '1';
            let pollHandle = null;

            const statusLabels = {
                processing: 'Processing',
                cancelling: 'Stopping',
                completed: 'Completed',
                completed_with_errors: 'Completed With Errors',
                cancelled: 'Stopped',
                failed: 'Failed',
                idle: 'Idle',
            };

            const statusClass = function (status) {
                if (status === 'processing' || status === 'cancelling') {
                    return 'alert-info';
                }
                if (status === 'completed') {
                    return 'alert-success';
                }
                if (status === 'completed_with_errors' || status === 'cancelled' || status === 'failed') {
                    return 'alert-warning';
                }

                return 'alert-light';
            };

            const normalizeNumber = function (value) {
                const parsed = parseInt(String(value || '0'), 10);
                return isNaN(parsed) ? 0 : parsed;
            };

            const renderBulkState = function (run) {
                const status = String((run && run.status) ? run.status : 'idle').toLowerCase();
                const total = normalizeNumber(run.total);
                const processed = normalizeNumber(run.processed);
                const issued = normalizeNumber(run.issued);
                const skipped = normalizeNumber(run.skipped);
                const failed = normalizeNumber(run.failed);
                const emailsSent = normalizeNumber(run.emails_sent);
                const emailsFailed = normalizeNumber(run.emails_failed);
                const sendEmail = run && Boolean(run.send_email);
                const message = (run && run.message) ? String(run.message) : 'No active bulk certificate run.';
                const isRunning = runningStatuses.includes(status);
                const percent = total > 0 ? Math.min(100, Math.floor((processed / total) * 100)) : 0;

                bulkStatusBox.dataset.state = status;
                bulkStatusBox.classList.remove('alert-light', 'alert-info', 'alert-success', 'alert-warning', 'alert-danger');
                bulkStatusBox.classList.add(statusClass(status));
                bulkStatusLabel.textContent = statusLabels[status] || status.replace(/_/g, ' ');
                bulkStatusMessage.textContent = message;

                if (total > 0) {
                    bulkProgressWrap.classList.remove('d-none');
                } else {
                    bulkProgressWrap.classList.add('d-none');
                }
                bulkProgressBar.style.width = percent + '%';
                bulkProgressBar.setAttribute('aria-valuenow', String(percent));
                bulkProgressBar.textContent = percent + '%';

                let counterText = 'Processed ' + processed + ' / ' + total + ', Issued ' + issued + ', Skipped ' + skipped + ', Failed ' + failed + '.';
                if (sendEmail) {
                    counterText += ' Emails Sent ' + emailsSent + ', Email Failures ' + emailsFailed + '.';
                }
                bulkCounters.textContent = counterText;

                bulkStopButton.classList.toggle('d-none', !isRunning);
                bulkStopButton.disabled = false;
                bulkSubmitButton.disabled = isRunning || submitBaseDisabled;
                bulkSubmitButton.textContent = isRunning
                    ? (status === 'cancelling' ? 'Stopping...' : 'Processing...')
                    : defaultSubmitText;
            };

            const stopPolling = function () {
                if (pollHandle) {
                    window.clearInterval(pollHandle);
                    pollHandle = null;
                }
            };

            const fetchBulkStatus = function () {
                if (!statusUrl || !window.fetch) {
                    return;
                }

                fetch(statusUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        const run = payload && payload.run ? payload.run : {};
                        renderBulkState(run);

                        const status = String((run && run.status) ? run.status : 'idle').toLowerCase();
                        if (!runningStatuses.includes(status)) {
                            stopPolling();
                        }
                    })
                    .catch(function () {
                    });
            };

            const startPolling = function () {
                if (pollHandle) {
                    return;
                }
                pollHandle = window.setInterval(fetchBulkStatus, 2000);
            };

            bulkForm.addEventListener('submit', function (event) {
                if (!window.fetch) {
                    return;
                }
                event.preventDefault();

                const formData = new FormData(bulkForm);
                if (csrfToken && !formData.has('_token')) {
                    formData.append('_token', csrfToken);
                }

                bulkSubmitButton.disabled = true;
                bulkSubmitButton.textContent = 'Starting...';
                startPolling();

                fetch(bulkForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            if (!response.ok) {
                                throw payload;
                            }
                            return payload;
                        });
                    })
                    .then(function (payload) {
                        if (payload && payload.run) {
                            renderBulkState(payload.run);
                        }
                        fetchBulkStatus();
                    })
                    .catch(function (payload) {
                        const message = payload && payload.message
                            ? payload.message
                            : 'Bulk certificate request failed.';
                        bulkStatusBox.classList.remove('alert-light', 'alert-success', 'alert-warning');
                        bulkStatusBox.classList.add('alert-danger');
                        bulkStatusLabel.textContent = 'Failed';
                        bulkStatusMessage.textContent = message;
                        bulkSubmitButton.disabled = submitBaseDisabled;
                        bulkSubmitButton.textContent = defaultSubmitText;
                        stopPolling();
                    });
            });

            bulkStopButton.addEventListener('click', function () {
                if (!cancelUrl || !window.fetch) {
                    return;
                }
                if (!window.confirm('Stop the current bulk certificate process?')) {
                    return;
                }

                bulkStopButton.disabled = true;

                const cancelData = new FormData();
                if (csrfToken) {
                    cancelData.append('_token', csrfToken);
                }

                fetch(cancelUrl, {
                    method: 'POST',
                    body: cancelData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        if (payload && payload.run) {
                            renderBulkState(payload.run);
                        }
                        startPolling();
                    })
                    .catch(function () {
                        bulkStopButton.disabled = false;
                    });
            });

            const initialState = String(bulkStatusBox.dataset.state || 'idle').toLowerCase();
            if (runningStatuses.includes(initialState)) {
                startPolling();
                fetchBulkStatus();
            }
        }

        const topScroll = document.querySelector('.js-participants-scroll-top');
        const topScrollInner = document.querySelector('.js-participants-scroll-top-inner');
        const bodyScroll = document.querySelector('.js-participants-scroll-body');
        const participantsTable = document.querySelector('.js-participants-table');

        if (topScroll && topScrollInner && bodyScroll && participantsTable) {
            let syncing = false;

            const syncScroll = function (source, target) {
                source.addEventListener('scroll', function () {
                    if (syncing) {
                        return;
                    }
                    syncing = true;
                    target.scrollLeft = source.scrollLeft;
                    window.requestAnimationFrame(function () {
                        syncing = false;
                    });
                });
            };

            const updateHorizontalScroll = function () {
                const tableWidth = participantsTable.scrollWidth;
                const viewportWidth = bodyScroll.clientWidth;

                topScrollInner.style.width = tableWidth + 'px';

                if (tableWidth > viewportWidth + 1) {
                    topScroll.classList.remove('d-none');
                } else {
                    topScroll.classList.add('d-none');
                    topScroll.scrollLeft = 0;
                    bodyScroll.scrollLeft = 0;
                }
            };

            syncScroll(topScroll, bodyScroll);
            syncScroll(bodyScroll, topScroll);
            updateHorizontalScroll();

            window.addEventListener('resize', updateHorizontalScroll);
            document.querySelectorAll('details').forEach(function (detailsElement) {
                detailsElement.addEventListener('toggle', function () {
                    window.requestAnimationFrame(updateHorizontalScroll);
                });
            });
        }
    });
</script>
@endpush
