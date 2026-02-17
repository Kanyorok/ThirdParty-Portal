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

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>Member Name</th>
                    <th>Current Role</th>
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
                        <td colspan="4" class="text-center py-4">No committee members found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

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
                                        @endphp
                                        <tr>
                                            <td>{{ $memberName }}</td>
                                            <td>{{ $item->Role ?? 'Member' }}</td>
                                            <td>
                                                <input type="hidden" name="memberID[]" value="{{ $item->UserID }}">
                                                <select class="form-select" name="memberRole[]">
                                                    <option value="Member" {{ ($item->Role ?? 'Member') === 'Member' ? 'selected' : '' }}>Member</option>
                                                    <option value="Chairperson" {{ $item->Role === 'Chairperson' ? 'selected' : '' }}>Chairperson</option>
                                                    <option value="Technical Evaluator" {{ $item->Role === 'Technical Evaluator' ? 'selected' : '' }}>Technical Evaluator</option>
                                                    <option value="Financial Evaluator" {{ $item->Role === 'Financial Evaluator' ? 'selected' : '' }}>Financial Evaluator</option>
                                                    <option value="Legal Advisor" {{ $item->Role === 'Legal Advisor' ? 'selected' : '' }}>Legal Advisor</option>
                                                    <option value="Observer" {{ $item->Role === 'Observer' ? 'selected' : '' }}>Observer</option>
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
