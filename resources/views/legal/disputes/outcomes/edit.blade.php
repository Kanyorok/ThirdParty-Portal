@extends('layouts.app')
@section('title', 'Edit Case Outcome')

@section('content')
<div class="container">
    <div class="card p-2 shadow rounded-4 mb-0">
        <div class="card-body mb-0">
            <p class="text-muted">
                Update the outcome details for case: 
                <strong class="text-dark">{{ $case->CaseTitle }}</strong>
            </p>

            <form method="POST" action="{{ route('legal.disputes.outcomes.update', [$case->Id, $outcome->Id]) }}">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Outcome</label>
                        <input type="text" name="Outcome" 
                            value="{{ old('Outcome', $outcome->Outcome) }}" 
                            class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Judge Name</label>
                        <input type="text" name="JudgeName" 
                            value="{{ old('JudgeName', $outcome->JudgeName) }}" 
                            class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Judgment Date</label>
                        <input type="date" name="JudgmentDate" 
                            value="{{ old('JudgmentDate', $outcome->JudgmentDate) }}" 
                            class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Penalty Amount</label>
                        <input type="number" step="0.01" name="PenaltyAmount" 
                            value="{{ old('PenaltyAmount', $outcome->PenaltyAmount) }}" 
                            class="form-control" placeholder="0.00" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Court Decision</label>
                    <textarea name="CourtDecision" class="form-control" rows="3" required>{{ old('CourtDecision', $outcome->CourtDecision) }}</textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="Remarks" class="form-control" rows="3" required>{{ old('Remarks', $outcome->Remarks) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3">
                    <a href="{{ route('legal.disputes.outcomes.index', $case->Id) }}" 
                        class="btn btn-outline-secondary">
                        <i class="fas fa-long-arrow-alt-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-info"
                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit();}">
                        <i class="fas fa-save"></i> Update Outcome
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
