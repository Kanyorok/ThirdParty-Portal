@extends('layouts.app')

@section('title', 'Manage Program Participants')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Manage Program Participants</h2>
        <div class="d-flex gap-2">
            @if($program->HasCertification && (($program->CertificateScope ?? 'Session') === 'Program'))
                <a class="btn btn-outline-primary" href="{{ route('crm.training.programs.certification', $program->Id) }}">Program Certificates</a>
            @endif
            <a class="btn btn-outline-secondary" href="{{ route('crm.training.programs.edit', $program->Id) }}">Edit Program</a>
            <a class="btn btn-outline-secondary" href="{{ route('crm.training.programs.index') }}">Back</a>
        </div>
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
            <div class="row g-3">
                <div class="col-md-8">
                    <h5 class="mb-1">{{ $program->Title }}</h5>
                    <div class="text-muted">{{ $program->Objectives ?? 'No objectives captured.' }}</div>
                </div>
                <div class="col-md-4">
                    <div><strong>Code:</strong> {{ $program->Code }}</div>
                    <div><strong>Category:</strong> {{ $program->category?->Name ?? '-' }}</div>
                    <div><strong>Status:</strong> {{ $program->Status }}</div>
                    <div><strong>Participants:</strong> {{ number_format($totalParticipants) }}</div>
                    @if($program->HasCertification)
                        <div><strong>Certificate Scope:</strong> {{ ($program->CertificateScope ?? 'Session') === 'Program' ? 'Whole Program/Course' : 'Per Session' }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h5 class="mb-3">Add Participants from Marketing List</h5>

            @if($marketingLists->isEmpty())
                <div class="text-muted">No eligible client marketing lists found. Create one in CRM Marketing Lists and return here.</div>
            @else
                <form id="marketing-list-preview-form" method="GET" action="{{ route('crm.training.programs.participants', $program->Id) }}" class="row g-3 align-items-end mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Marketing List</label>
                        <select id="preview-marketing-list-id" name="preview_marketing_list_id" class="form-select" required>
                            <option value="">Select list</option>
                            @foreach($marketingLists as $list)
                                <option value="{{ $list->MarketingListID }}" @selected((string)$previewListId === (string)$list->MarketingListID)>
                                    {{ $list->Label }} ({{ $list->Type }} | {{ $list->SourceLabel }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="participant_name" value="{{ $participantFilters['name'] }}">
                        <input type="hidden" name="participant_client_id" value="{{ $participantFilters['client_id'] }}">
                        <input type="hidden" name="participant_id_number" value="{{ $participantFilters['id_number'] }}">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-outline-primary" type="submit">Preview List</button>
                        <a class="btn btn-outline-secondary" href="{{ route('marketing-list.index') }}">Open Marketing Lists</a>
                    </div>
                </form>

                @if($listPreview)
                    <div class="alert alert-info">
                        <div><strong>List:</strong> {{ $listPreview['Label'] }} ({{ $listPreview['Type'] }} | {{ $listPreview['SourceLabel'] }})</div>
                        <div><strong>Total IDs from list:</strong> {{ number_format($listPreview['raw_count']) }}</div>
                        <div><strong>Valid clients found:</strong> {{ number_format($listPreview['valid_count']) }}</div>
                        <div><strong>Already in this program:</strong> {{ number_format($listPreview['already_selected_count']) }}</div>
                        <div><strong>Eligible to add now:</strong> {{ number_format($listPreview['eligible_count']) }}</div>
                    </div>

                    @if($listPreview['eligible_count'] > 0)
                        <form method="POST" action="{{ route('crm.training.programs.participants.add-list', $program->Id) }}" class="mb-3">
                            @csrf
                            <input type="hidden" name="MarketingListID" value="{{ $listPreview['MarketingListID'] }}">
                            <button class="btn btn-primary" type="submit">Add {{ number_format($listPreview['eligible_count']) }} Clients from This List</button>
                        </form>
                    @endif

                    @if($previewClients->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>ClientID</th>
                                        <th>Name</th>
                                        <th>ID Number</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previewClients as $client)
                                        <tr>
                                            <td>{{ $client->ClientID }}</td>
                                            <td>{{ $client->Name }}</td>
                                            <td>{{ $client->individual?->PassportNo ?? $client->corporate?->CertificateNo ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($listPreview['eligible_count'] > $previewClients->count())
                            <div class="form-text mt-2">Showing first {{ $previewClients->count() }} eligible clients.</div>
                        @endif
                    @elseif($listPreview['eligible_count'] === 0)
                        <div class="text-muted">No new clients to add from this list.</div>
                    @endif
                @endif

                <div class="form-text mt-2">Static lists use current member entries. Dynamic lists resolve clients from the configured filters at preview/import time.</div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Selected Participants ({{ number_format($totalParticipants) }})</h5>
            </div>

            <form method="GET" action="{{ route('crm.training.programs.participants', $program->Id) }}" class="row g-3 mb-3">
                @if($previewListId > 0)
                    <input type="hidden" name="preview_marketing_list_id" value="{{ $previewListId }}">
                @endif
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" name="participant_name" class="form-control" value="{{ $participantFilters['name'] }}" placeholder="e.g. Jane Doe">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ClientID</label>
                    <input type="text" name="participant_client_id" class="form-control" value="{{ $participantFilters['client_id'] }}" placeholder="e.g. 012345678">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Number</label>
                    <input type="text" name="participant_id_number" class="form-control" value="{{ $participantFilters['id_number'] }}" placeholder="Passport/Certificate number">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-outline-primary" type="submit">Filter Participants</button>
                    @if($previewListId > 0)
                        <a class="btn btn-outline-secondary" href="{{ route('crm.training.programs.participants', ['id' => $program->Id, 'preview_marketing_list_id' => $previewListId]) }}">Clear Filters</a>
                    @else
                        <a class="btn btn-outline-secondary" href="{{ route('crm.training.programs.participants', $program->Id) }}">Clear Filters</a>
                    @endif
                </div>
            </form>

            @if($selectedParticipants->isEmpty())
                @if($hasParticipantFilters)
                    <div class="text-muted">No participants match the selected filters.</div>
                @else
                    <div class="text-muted">No participants have been added to this program yet.</div>
                @endif
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ClientID</th>
                                <th>Name</th>
                                <th>ID Number</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedParticipants as $client)
                                <tr>
                                    <td>{{ $client->ClientID }}</td>
                                    <td>{{ $client->Name }}</td>
                                    <td>{{ $client->individual?->PassportNo ?? $client->corporate?->CertificateNo ?? '-' }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('crm.training.programs.participants.remove', [$program->Id, $client->ClientID]) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $selectedParticipants->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const previewForm = document.getElementById('marketing-list-preview-form');
        const previewSelect = document.getElementById('preview-marketing-list-id');

        if (previewForm && previewSelect) {
            previewSelect.addEventListener('change', function () {
                if (previewSelect.value) {
                    previewForm.submit();
                }
            });
        }
    });
</script>
@endpush
