@extends('layouts.app')

@section('title', 'Edit Evaluation')

@section('content')
<div class="container py-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Edit Evaluation - RFQ #{{ $evaluation->rfq->RFQNumber ?? 'N/A' }}</h4>
        <a href="{{ route('evaluations.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light">
            <strong>Evaluation Details</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Committee Member:</strong> {{ $evaluation->CommitteeMemberName }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>RFQ Comment:</strong> {{ $evaluation->RFQComment ?? 'None' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Confirmed:</strong> {{ $evaluation->Confirmation ? '✅ Yes' : '❌ No' }}</p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('evaluations.update', $evaluation->Id) }}" id="editEvaluationForm">
        @csrf
        @method('PUT')

        @foreach($groupedEvaluations as $sectionName => $evaluations)
            @php
                $firstEval = $evaluations->first();
                $sectionId = $firstEval->rfqCriteriaUnscoped?->SectionID;
                $weight = $sectionId ? ($sectionWeights[$sectionId] ?? 0) : 0;
            @endphp
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    {{ $sectionName }} <span class="text-light">(Weight: {{ $weight }}%)</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Supplier</th>
                                <th>Criteria</th>
                                <th>Max Score</th>
                                <th>Score (1-10) <span class="text-danger">*</span></th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($evaluations as $eval)
                                <tr>
                                    <td>{{ $eval->supplier?->thirdParty?->ThirdPartyName ?? $eval->supplier?->thirdParty?->TradingName ?? 'N/A' }}</td>
                                    <td>{{ $eval->rfqCriteriaUnscoped?->criteria?->CriteriaName ?? 'N/A' }}</td>
                                    <td>10</td>
                                    <td>
                                        <input type="number" 
                                               name="Evaluations[{{ $eval->Id }}][Score]" 
                                               value="{{ $eval->Score }}" 
                                               class="form-control score-input" 
                                               min="1" max="10" required>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="Evaluations[{{ $eval->Id }}][Comments]" 
                                               value="{{ $eval->Comments }}" 
                                               class="form-control">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">Update Evaluation</button>
            <a href="{{ route('evaluations.index') }}" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editEvaluationForm');
    
    form.addEventListener('submit', function(event) {
        let isValid = true;
        const scoreInputs = document.querySelectorAll('.score-input');
        
        scoreInputs.forEach(input => {
            const value = parseInt(input.value);
            if (isNaN(value) || value < 1 || value > 10) {
                isValid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            event.preventDefault();
            alert('Please ensure all scores are between 1 and 10.');
        }
    });
});
</script>
@endsection
