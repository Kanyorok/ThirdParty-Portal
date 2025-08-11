@extends('layouts.app')
@section('title', 'Edit Case Outcome')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">✏️ Edit Outcome for: {{ $case->CaseTitle }}</h4>

    <form method="POST" action="{{ route('legal.disputes.outcomes.update', [$case->ID, $outcome->ID]) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Outcome</label>
            <input type="text" name="Outcome" value="{{ $outcome->Outcome }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Judgment Date</label>
            <input type="date" name="JudgmentDate" value="{{ $outcome->JudgmentDate }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Judge Name</label>
            <input type="text" name="JudgeName" value="{{ $outcome->JudgeName }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Court Decision</label>
            <textarea name="CourtDecision" class="form-control">{{ $outcome->CourtDecision }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Penalty Amount</label>
            <input type="number" step="0.01" name="PenaltyAmount" value="{{ $outcome->PenaltyAmount }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="Remarks" class="form-control">{{ $outcome->Remarks }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">💾 Update</button>
        <a href="{{ route('legal.disputes.outcomes.index', $case->ID) }}" class="btn btn-secondary">↩️ Back</a>
    </form>
</div>
@endsection
