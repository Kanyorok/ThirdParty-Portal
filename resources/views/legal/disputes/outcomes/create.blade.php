@extends('layouts.app')
@section('title', 'Add Case Outcome')

@section('content')
<div class="container">
    <div class="card p-2 shadow rounded-4 mb-0">
        <div class="card-body mb-0">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <p class="text-muted">Fill out the form below to record the outcome for <strong>{{ $case->CaseTitle }}</strong>.</p>

            <form method="POST" action="{{ route('legal.disputes.outcomes.store', $case->Id) }}">
                @csrf
                <div class="row mb-3">
                    <input type="hidden" name="LegalCaseID" value="{{ $case->Id }}">
                    <div class="col-md-6">
                        <label class="form-label">Outcome</label>
                        <input type="text" 
                               name="Outcome" 
                               placeholder="e.g. Case dismissed, Won, Settled" 
                               class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Judgment Date</label>
                        <input type="date" 
                               name="JudgmentDate"
                               placeholder="Select judgment date" 
                               class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Judge Name</label>
                        <input type="text" 
                               name="JudgeName"
                               placeholder="Enter judge's full name" 
                               class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Penalty Amount</label>
                        <input type="number" 
                               step="0.01" 
                               name="PenaltyAmount"
                               placeholder="e.g. 50000.00"
                               min="0.00"
                               class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Court Decision</label>
                    <textarea name="CourtDecision" 
                              class="form-control" 
                              rows="3" 
                              placeholder="Enter details of the court decision" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="Remarks" 
                              class="form-control" 
                              rows="3" 
                              placeholder="Additional comments or notes" required></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3">
                    <a href="{{ route('legal.cases.show', $case->Id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-long-arrow-alt-left"></i> Back
                    </a>
                    <button type="submit" 
                        class="btn btn-info"
                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">
                        <i class="fas fa-save"></i> Save Outcome
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
