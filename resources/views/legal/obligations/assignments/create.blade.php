@extends('layouts.app')
@section('title', 'Assign Staff to Obligation')

@section('content')
    <div class="card shadow p-4 rounded-4">
        <h4 class="mb-4">👤 Assign Staff to Obligation</h4>

        <form method="POST" action="{{ route('legal.obligations.assignments.store', $obligation->ID) }}">
            @csrf
            <input type="hidden" name="LegalObligationID" value="{{ $obligation->ID }}">

            <div class="mb-3">
                <label for="AssigneeName" class="form-label">Assignee Name</label>
                <input type="text" name="AssigneeName" class="form-control" required placeholder="e.g. John Doe">
            </div>

            <div class="mb-3">
                <label for="Remarks" class="form-label">Remarks</label>
                <textarea name="Remarks" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
            </div>

            <button type="submit" class="btn btn-success">💾 Assign</button>
            <a href="{{ route('legal.obligations.assignments.index', $obligation->ID) }}" class="btn btn-secondary">↩️
                Back</a>
        </form>
    </div>
@endsection
