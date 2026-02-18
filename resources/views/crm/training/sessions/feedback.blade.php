@extends('layouts.app')

@section('title', 'Session Feedback')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
@endsection

@section('content')
@php
    $participantNameMap = $participants->mapWithKeys(function ($participant) {
        $clientId = (string) $participant->ClientID;
        $clientName = $participant->client?->Name ?? $clientId;

        return [$clientId => $clientName];
    });

    $feedbackRows = collect();
    $oldFeedback = collect(old('Feedback', []));
    if ($oldFeedback->isNotEmpty()) {
        $feedbackRows = $oldFeedback->map(function ($row, $clientId) use ($participantNameMap) {
            $clientId = (string) $clientId;

            return [
                'ClientID' => $clientId,
                'ClientName' => $participantNameMap->get($clientId, $clientId),
                'RatingContent' => $row['RatingContent'] ?? '',
                'RatingTrainer' => $row['RatingTrainer'] ?? '',
                'RatingRelevance' => $row['RatingRelevance'] ?? '',
                'Comments' => $row['Comments'] ?? '',
            ];
        })->values();
    } else {
        $feedbackRows = $feedback->map(function ($row, $clientId) use ($participantNameMap) {
            $clientId = (string) $clientId;

            return [
                'ClientID' => $clientId,
                'ClientName' => $participantNameMap->get($clientId, $clientId),
                'RatingContent' => $row->RatingContent ?? '',
                'RatingTrainer' => $row->RatingTrainer ?? '',
                'RatingRelevance' => $row->RatingRelevance ?? '',
                'Comments' => $row->Comments ?? '',
            ];
        })->values();
    }
@endphp
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Session Feedback</h2>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('crm.training.sessions.show', $session->Id) }}">Session Details</a>
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
                    </div>
                </div>
                <div class="col-md-4">
                    <div><strong>Trainer:</strong> {{ $session->trainer?->Name ?? '-' }}</div>
                    <div><strong>Status:</strong> {{ $session->Status }}</div>
                    <div><strong>Participants:</strong> {{ $participants->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-2 align-items-end mb-3">
                <div class="col-md-8">
                    <label class="form-label">Search Participant</label>
                    <select id="FeedbackClientPicker" class="form-select"></select>
                    <div class="form-text">Search by name, ClientID, or ID number, then add participant to capture feedback.</div>
                </div>
                <div class="col-md-4">
                    <button id="AddFeedbackParticipantBtn" class="btn btn-outline-primary w-100" type="button">Add Participant</button>
                </div>
            </div>

            <form method="POST" action="{{ route('crm.training.sessions.feedback', $session->Id) }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Content (1-5)</th>
                                <th>Trainer (1-5)</th>
                                <th>Relevance (1-5)</th>
                                <th>Comments</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="FeedbackRows">
                            @foreach($feedbackRows as $row)
                                <tr data-client-id="{{ $row['ClientID'] }}">
                                    <td>
                                        <div class="fw-semibold">{{ $row['ClientName'] }}</div>
                                        <div class="text-muted small">{{ $row['ClientID'] }}</div>
                                    </td>
                                    <td>
                                        <input type="number" min="1" max="5" name="Feedback[{{ $row['ClientID'] }}][RatingContent]"
                                               class="form-control form-control-sm"
                                               value="{{ $row['RatingContent'] }}">
                                    </td>
                                    <td>
                                        <input type="number" min="1" max="5" name="Feedback[{{ $row['ClientID'] }}][RatingTrainer]"
                                               class="form-control form-control-sm"
                                               value="{{ $row['RatingTrainer'] }}">
                                    </td>
                                    <td>
                                        <input type="number" min="1" max="5" name="Feedback[{{ $row['ClientID'] }}][RatingRelevance]"
                                               class="form-control form-control-sm"
                                               value="{{ $row['RatingRelevance'] }}">
                                    </td>
                                    <td>
                                        <input type="text" name="Feedback[{{ $row['ClientID'] }}][Comments]"
                                               class="form-control form-control-sm"
                                               value="{{ $row['Comments'] }}">
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger RemoveFeedbackRowBtn">Remove</button>
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="FeedbackEmptyState" class="{{ $feedbackRows->isNotEmpty() ? 'd-none' : '' }}">
                                <td colspan="6" class="text-center text-muted">No participants added for feedback yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary" type="submit">Save Feedback</button>
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
        const $picker = $('#FeedbackClientPicker');
        const $addBtn = $('#AddFeedbackParticipantBtn');
        const $rows = $('#FeedbackRows');
        const $empty = $('#FeedbackEmptyState');
        const searchUrl = @json(route('crm.training.sessions.participants.search', $session->Id));

        const escapeHtml = function (value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const updateEmptyState = function () {
            const hasRows = $rows.find('tr[data-client-id]').length > 0;
            $empty.toggleClass('d-none', hasRows);
        };

        const hasClientRow = function (clientId) {
            const targetId = String(clientId);
            let exists = false;
            $rows.find('tr[data-client-id]').each(function () {
                if (String($(this).data('client-id')) === targetId) {
                    exists = true;
                    return false;
                }
            });

            return exists;
        };

        const createFeedbackRow = function (clientId, clientName) {
            const safeId = escapeHtml(clientId);
            const safeName = escapeHtml(clientName || clientId);

            return `
                <tr data-client-id="${safeId}">
                    <td>
                        <div class="fw-semibold">${safeName}</div>
                        <div class="text-muted small">${safeId}</div>
                    </td>
                    <td>
                        <input type="number" min="1" max="5" name="Feedback[${safeId}][RatingContent]" class="form-control form-control-sm">
                    </td>
                    <td>
                        <input type="number" min="1" max="5" name="Feedback[${safeId}][RatingTrainer]" class="form-control form-control-sm">
                    </td>
                    <td>
                        <input type="number" min="1" max="5" name="Feedback[${safeId}][RatingRelevance]" class="form-control form-control-sm">
                    </td>
                    <td>
                        <input type="text" name="Feedback[${safeId}][Comments]" class="form-control form-control-sm">
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger RemoveFeedbackRowBtn">Remove</button>
                    </td>
                </tr>
            `;
        };

        const clearPicker = function () {
            $picker.val(null).trigger('change');
        };

        const addSelectedParticipant = function () {
            const selected = $picker.select2('data')[0];
            if (!selected || !selected.id) {
                return;
            }

            const clientId = String(selected.id).trim();
            if (clientId === '' || hasClientRow(clientId)) {
                clearPicker();
                return;
            }

            const clientName = selected.name || selected.text || clientId;
            $rows.append(createFeedbackRow(clientId, clientName));
            updateEmptyState();
            clearPicker();
        };

        $picker.select2({
            width: '100%',
            placeholder: 'Search participant...',
            minimumInputLength: 1,
            ajax: {
                url: searchUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || ''
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results || []
                    };
                }
            }
        });

        $addBtn.on('click', addSelectedParticipant);
        $picker.on('select2:select', addSelectedParticipant);

        $rows.on('click', '.RemoveFeedbackRowBtn', function () {
            $(this).closest('tr[data-client-id]').remove();
            updateEmptyState();
        });

        updateEmptyState();
    });
</script>
@endpush
