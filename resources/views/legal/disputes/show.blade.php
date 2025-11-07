@extends('layouts.app')
@section('title', 'Legal Case Details')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border rounded-4 overflow-hidden">
        
        {{-- Header --}}
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
            <h5 class="text-info mb-0">
                <i class="fas fa-gavel"></i> Legal Case Details
            </h5>
            <a href="{{ route('legal.cases.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        {{-- Body --}}
        <div class="card-body bg-white">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Case Title:</strong>
                        <div>{{ $case->CaseTitle }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Case Number:</strong>
                        <div>{{ $case->CaseNumber }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Court:</strong>
                        <div>{{ $case->CourtName }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Filing Date:</strong>
                        <div>{{ \Carbon\Carbon::parse($case->FilingDate)->format('d M Y') }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Opposing Party:</strong>
                        <div>{{ $case->OpposingParty }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Case Type:</strong>
                        <div>{{ $case->CaseType }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Status:</strong>
                        <span class="badge {{ $case->Status == 'Open' ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                            {{ $case->Status }}
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>DMS Reference:</strong>
                        <div>{{ $case->DMSDocID ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            {{-- Summary --}}
            <div class="border rounded p-3 bg-light mt-4">
                <h6 class="text-info fw-bold mb-1"><i class="fas fa-scroll"></i> Summary</h6>
                <p class="mb-0 fw-semibold">{{ $case->Summary }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Legal Counsel Section --}}
<div class="container mt-4">
    <div class="card shadow-sm border rounded-4 overflow-hidden">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
            <h5 class="text-info mb-0"><i class="fas fa-user-tie"></i> Assigned Legal Counsels</h5>
            <a href="{{ route('legal.disputes.counsels.create', $case->Id) }}" class="btn btn-sm btn-info">
                <i class="fas fa-plus"></i> Assign Counsel
            </a>
        </div>
        
        <div class="card-body bg-white">
            <p class="text-muted">List of legal counsels assigned to this case.</p>
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Law Firm</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($case->counsels->count())
                            @foreach ($counsels as $counsel)
                            <tr>
                                <td class="fw-semibold">{{ $counsel->CounselName }}</td>
                                <td>{{ $counsel->FirmName }}</td>
                                <td><a href="mailto:{{ $counsel->Email }}">{{ $counsel->Email }}</a></td>
                                <td><a href="tel:{{ $counsel->Phone }}">{{ $counsel->Phone }}</a></td>
                                <td>
                                    <span class="badge bg-info px-3 py-2">{{ $counsel->Role }}</span>
                                </td>
                                <td>
                                   <a href="{{ route('legal.disputes.counsels.show', [$case->Id, $counsel->Id]) }}" 
                                        class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('legal.disputes.counsels.edit', [$case->Id, $counsel->Id]) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{ $counsel->CounselName }}"
                                        data-route="{{ route('legal.disputes.counsels.destroy', [$case->Id, $counsel->Id]) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button> 
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            <i>No counsels assigned yet.</i>
                                        </p>
                                        <a href="{{ route('legal.disputes.counsels.create', $case->Id) }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Assign Counsel
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


{{-- Case Outcome Section --}}
<div class="card shadow rounded-4 border-0 mb-4">
    <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-info"><i class="fas fa-gavel me-2"></i> Case Outcome</h5>
        <a href="{{ route('legal.disputes.outcomes.create', $case->Id) }}" class="btn btn-sm btn-info">
            <i class="fas fa-plus"></i> Add Outcome
        </a>
    </div>

    <div class="card-body bg-white">
        <p class="text-muted">Details of the legal case outcomes.</p>
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Outcome</th>
                            <th>Judgment Date</th>
                            <th>Judge Name</th>
                            <th>Penalty Amount</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if($outcomes->count())
                        @foreach ($outcomes as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->Outcome }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->JudgmentDate)->format('d-m-Y') }}</td>
                            <td>{{ $item->JudgeName }}</td>
                            <td>{{ number_format($item->PenaltyAmount, 2) }}</td>
                            <td>{{ $item->Remarks }}</td>
                            <td>
                                <a href="{{ route('legal.disputes.outcomes.show',[$case->Id, $item->Id]) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('legal.disputes.outcomes.edit', [$case->Id, $item->Id]) }}" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-danger custom-delete-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#customDeleteConfirmModal"
                                    data-name="{{ $case->outcome->Outcome }}"
                                    data-route="{{ route('legal.disputes.outcomes.destroy', [$case->Id, $item->Id]) }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="p-0">
                                <div class="text-centre p-4 border rounded-3 bg-light">
                                    <p class="mb-3 text-muted fs-5">
                                        <i class="fas fa-info-circle me-2 text-info"></i>   
                                        <i>No outcomes recorded for this case.</i>
                                    </p>
                                    <a href="{{ route('legal.disputes.outcomes.create', $case->Id) }}" class="btn btn-info px-4 py-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Outcome
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
    </div>
</div>


@include('components.modals.delete-confirm')
@endsection
