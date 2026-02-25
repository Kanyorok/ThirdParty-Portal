@extends('layouts.app')
@section('title', 'Committee Members Overview')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Committee Members ({{ $tenderTitle }})</h4>
            <span class="badge {{ $isCommitteeActive ? 'bg-success' : 'bg-secondary' }}">
                {{ $isCommitteeActive ? 'Active' : 'Inactive' }}
            </span>
        </div>

        @if (!$isCommitteeActive)
            <div class="alert alert-info">
                This committee is inactive. Member changes are disabled.
            </div>
        @endif

        <div class="mb-3">
            <button
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#manageMembersModal"
                {{ $isCommitteeActive ? '' : 'disabled' }}
            >
                Manage Members
            </button>
        </div>

        {{-- Main members overview table --}}
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>Member Name</th>
                    <th>Current Role</th>
                    <th>Pending Role</th>
                    <th>Appointment Date</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($committeeMembers as $item)
                    @php
                        $resolvedUser = $item->user ?? $item->userByEmployee;
                        $resolvedEmployee = $resolvedUser?->employee;
                        $memberName = $resolvedEmployee?->full_name ?? $resolvedUser?->Name ?? 'N/A';
                    @endphp
                    <tr>
                        <td>{{ $memberName }}</td>
                        <td>{{ $item->Role ?? 'Member' }}</td>
                        <td>
                            @if ($item->PendingRole)
                                <span class="badge bg-warning text-dark">{{ $item->PendingRole }}</span>
                                <small class="text-muted d-block">Awaiting Approval</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $appointmentDate ? \Carbon\Carbon::parse($appointmentDate)->format('d/m/Y') : 'N/A' }}</td>
                        <td>
                            @if ((int) $item->Response === 1)
                                <span class="badge bg-success">Accepted</span>
                            @elseif ((int) $item->Response === 2)
                                <span class="badge bg-danger">Declined</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No committee members found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Role Change History Section --}}
        @php
            $hasAnyHistory = $committeeMembers->contains(fn ($m) => $m->roleHistory && $m->roleHistory->count() > 0);
        @endphp
        @if ($hasAnyHistory)
            <div class="mt-4">
                <h5>
                    <a class="text-decoration-none" data-bs-toggle="collapse" href="#roleHistorySection" role="button" aria-expanded="false" aria-controls="roleHistorySection">
                        📋 Role Change History <small class="text-muted">(click to expand)</small>
                    </a>
                </h5>
                <div class="collapse" id="roleHistorySection">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>Member</th>
                                <th>Previous Role</th>
                                <th>New Role</th>
                                <th>Changed On</th>
                                <th>Status</th>
                                <th>Responded On</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($committeeMembers as $item)
                                @if ($item->roleHistory && $item->roleHistory->count() > 0)
                                    @php
                                        $resolvedUser = $item->user ?? $item->userByEmployee;
                                        $resolvedEmployee = $resolvedUser?->employee;
                                        $memberName = $resolvedEmployee?->full_name ?? $resolvedUser?->Name ?? 'N/A';
                                    @endphp
                                    @foreach ($item->roleHistory as $history)
                                        <tr>
                                            <td>{{ $memberName }}</td>
                                            <td>{{ $history->PreviousRole ?? '—' }}</td>
                                            <td>{{ $history->NewRole }}</td>
                                            <td>{{ $history->ChangedOn ? \Carbon\Carbon::parse($history->ChangedOn)->format('d/m/Y H:i') : '—' }}</td>
                                            <td>
                                                @if ((int) $history->Status === 1)
                                                    <span class="badge bg-success">Accepted</span>
                                                @elseif ((int) $history->Status === 2)
                                                    <span class="badge bg-danger">Declined</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>
                                            <td>{{ $history->RespondedOn ? \Carbon\Carbon::parse($history->RespondedOn)->format('d/m/Y H:i') : '—' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Manage Members Modal --}}
    <div class="modal fade" id="manageMembersModal" tabindex="-1" aria-labelledby="manageMembersLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageMembersLabel">Manage Committee Members</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('tendercommittee.save') }}" method="POST">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="committeeType" value="{{ $committeeType }}">
                    <input type="hidden" name="tenderID" value="{{ $tenderID }}">
                    <input type="hidden" name="committeeID" value="{{ $committeeID }}">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Reference</label>
                            <input type="text" class="form-control" value="{{ $tenderTitle }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Members</label>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Member Name</th>
                                        <th>Current Role</th>
                                        <th>Assign Role</th>
                                        <th>Remove</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($committeeMembers as $item)
                                        @php
                                            $resolvedUser = $item->user ?? $item->userByEmployee;
                                            $resolvedEmployee = $resolvedUser?->employee;
                                            $memberName = $resolvedEmployee?->full_name ?? $resolvedUser?->Name ?? 'N/A';
                                            $displayRole = $item->Role ?? 'Member';
                                        @endphp
                                        <tr>
                                            <td>{{ $memberName }}</td>
                                            <td>
                                                {{ $displayRole }}
                                                @if ($item->PendingRole)
                                                    <br><small class="text-warning">Pending: {{ $item->PendingRole }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="hidden" name="memberID[]" value="{{ $item->UserID }}">
                                                <select class="form-select" name="memberRole[]">
                                                    <option value="Member" {{ $displayRole === 'Member' ? 'selected' : '' }}>Member</option>
                                                    <option value="Chairperson" {{ $displayRole === 'Chairperson' ? 'selected' : '' }}>Chairperson</option>
                                                    <option value="Technical Evaluator" {{ $displayRole === 'Technical Evaluator' ? 'selected' : '' }}>Technical Evaluator</option>
                                                    <option value="Financial Evaluator" {{ $displayRole === 'Financial Evaluator' ? 'selected' : '' }}>Financial Evaluator</option>
                                                    <option value="Legal Advisor" {{ $displayRole === 'Legal Advisor' ? 'selected' : '' }}>Legal Advisor</option>
                                                    <option value="Observer" {{ $displayRole === 'Observer' ? 'selected' : '' }}>Observer</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input" name="removeMembers[]" value="{{ $item->UserID }}">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-3">No members to manage.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Add New Members</label>
                            <select class="form-select" name="newMembers[]" multiple>
                                @forelse ($availableMembers as $member)
                                    <option value="{{ $member->Id }}">
                                        {{ optional($member->employee)->full_name ?? $member->Name }} - {{ optional(optional($member->employee)->role)->Name ?? 'N/A' }}
                                    </option>
                                @empty
                                    <option value="" disabled>No available members to add</option>
                                @endforelse
                            </select>
                            <small class="form-text text-muted">Only employees with active user accounts are listed.</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button
                            type="submit"
                            class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

