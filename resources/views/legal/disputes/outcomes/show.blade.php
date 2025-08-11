@extends('layouts.app')
@section('title', 'Case Outcome Details')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📜 Outcome for: {{ $case->CaseTitle }}</h4>

    <ul class="list-group">
        <li class="list-group-item"><strong>Outcome:</strong> {{ $outcome->Outcome }}</li>
        <li class="list-group-item"><strong>Judgment Date:</strong> {{ \Carbon\Carbon::parse($outcome->JudgmentDate)->format('d M Y') }}</li>
        <li class="list-group-item"><strong>Judge:</strong> {{ $outcome->JudgeName }}</li>
        <li class="list-group-item"><strong>Decision:</strong> {{ $outcome->CourtDecision }}</li>
        <li class="list-group-item"><strong>Penalty:</strong> {{ number_format($outcome->PenaltyAmount, 2) }}</li>
        <li class="list-group-item"><strong>Remarks:</strong> {{ $outcome->Remarks }}</li>
    </ul>

    <a href="{{ route('legal.disputes.outcomes.index', $case->ID) }}" class="btn btn-secondary mt-3">↩️ Back to Outcomes</a>
</div>
@endsection
