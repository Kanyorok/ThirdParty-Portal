@extends('layouts.app')
@section('title', 'Legal Case Details')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📄 Legal Case Details</h4>

    <ul class="list-group">
        <li class="list-group-item"><strong>Case Title:</strong> {{ $case->CaseTitle }}</li>
        <li class="list-group-item"><strong>Case Number:</strong> {{ $case->CaseNumber }}</li>
        <li class="list-group-item"><strong>Court:</strong> {{ $case->CourtName }}</li>
        <li class="list-group-item"><strong>Filing Date:</strong> {{ \Carbon\Carbon::parse($case->FilingDate)->format('d M Y') }}</li>
        <li class="list-group-item"><strong>Opposing Party:</strong> {{ $case->OpposingParty }}</li>
        <li class="list-group-item"><strong>Case Type:</strong> {{ $case->CaseType }}</li>
        <li class="list-group-item"><strong>Status:</strong> {{ $case->Status }}</li>
        <li class="list-group-item"><strong>Summary:</strong> {{ $case->Summary }}</li>
        <li class="list-group-item"><strong>DMS Reference:</strong> {{ $case->DMSDocID }}</li>
    </ul>
</div>

{{-- Legal Counsel Section --}}
<div class="card shadow p-4 rounded-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">🧑‍⚖️ Assigned Legal Counsels</h5>
        <a href="{{ route('legal.disputes.counsels.create', ['case_id' => $case->ID]) }}" class="btn btn-sm btn-primary">➕ Assign Counsel</a>
    </div>
    @if($case->counsels->isEmpty())
        <p class="text-muted">No legal counsels assigned yet.</p>
    @else
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Law Firm</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($case->counsels as $counsel)
                <tr>
                    <td>{{ $counsel->CounselName }}</td>
                    <td>{{ $counsel->LawFirm }}</td>
                    <td>{{ $counsel->ContactEmail }}</td>
                    <td>{{ $counsel->ContactPhone }}</td>
                    <td>{{ $counsel->Role }}</td>
                    <td>{{ $counsel->Remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Case Outcome Section --}}
<div class="card shadow p-4 rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">🧾 Case Outcome</h5>
        @if($case->outcome)
            <a href="{{ route('legal.disputes.outcomes.edit', $case->outcome->ID) }}" class="btn btn-sm btn-warning">✏️ Edit Outcome</a>
        @else
            <a href="{{ route('legal.disputes.outcomes.create', ['case_id' => $case->ID]) }}" class="btn btn-sm btn-primary">➕ Add Outcome</a>
        @endif
    </div>

    @if($case->outcome)
    <ul class="list-group">
        <li class="list-group-item"><strong>Outcome:</strong> {{ $case->outcome->Outcome }}</li>
        <li class="list-group-item"><strong>Judgment Date:</strong> {{ $case->outcome->JudgmentDate }}</li>
        <li class="list-group-item"><strong>Judge Name:</strong> {{ $case->outcome->JudgeName }}</li>
        <li class="list-group-item"><strong>Court Decision:</strong> {{ $case->outcome->CourtDecision }}</li>
        <li class="list-group-item"><strong>Penalty Amount:</strong> {{ number_format($case->outcome->PenaltyAmount, 2) }}</li>
        <li class="list-group-item"><strong>Remarks:</strong> {{ $case->outcome->Remarks }}</li>
    </ul>
    @else
        <p class="text-muted">No outcome entered yet.</p>
    @endif
</div>
@endsection
