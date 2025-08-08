@extends('layouts.app')
@section('title', 'Add Case Outcome')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📜 Add Outcome – {{ $case->CaseTitle }}</h4>

    <form method="POST" action="{{ route('legal.disputes.outcomes.store', $case->ID) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Outcome</label>
            <input type="text" name="Outcome" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Judgment Date</label>
            <input type="date" name="JudgmentDate" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Judge Name</label>
            <input type="text" name="JudgeName" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Court Decision</label>
            <textarea name="CourtDecision" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Penalty Amount</label>
            <input type="number" step="0.01" name="PenaltyAmount" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="Remarks" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">💾 Save</button>
        <a href="{{ route('legal.disputes.outcomes.index', $case->ID) }}" class="btn btn-secondary">↩️ Cancel</a>
    </form>
</div>
@endsection
