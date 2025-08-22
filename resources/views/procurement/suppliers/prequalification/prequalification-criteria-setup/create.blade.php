@extends('layouts.app')

@section('title', 'Evaluate Prequalification Application')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h4 class="mb-0 text-primary">Evaluate Application</h4>
                <p class="mb-0 text-muted">Supplier: {{ $application->supplier->SupplierName ?? 'N/A' }}</p>
                <p class="mb-0 text-muted"><small>Prequalification Round: {{ $round->RoundName }}</small></p>
            </div>
            <a href="{{ route('prequalification.evaluation.index', $application->RoundId) }}" class="btn btn-secondary d-flex align-items-center">
                <i class="bi bi-arrow-left me-2"></i> Back to Applications
            </a>
        </div>
        <form method="POST" action="{{ route('prequalification.evaluation.store', $application->ApplicationID) }}">
            @csrf
            <div class="card-body">
                @forelse($sections as $section)
                <div class="accordion-item shadow-sm mb-3">
                    <h2 class="accordion-header" id="heading{{ $section->SectionID }}">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $section->SectionID }}" aria-expanded="true" aria-controls="collapse{{ $section->SectionID }}">
                            <h5 class="mb-0">{{ $section->masterSection->Name }} <small class="text-muted ms-2">({{ $section->Weight }} points)</small></h5>
                        </button>
                    </h2>
                    <div id="collapse{{ $section->SectionID }}" class="accordion-collapse collapse show" aria-labelledby="heading{{ $section->SectionID }}">
                        <div class="accordion-body">
                            @foreach($section->criteria as $criteria)
                            @php
                            $evaluation = $evaluations->get($criteria->CriteriaID);
                            @endphp
                            <div class="card mb-3 shadow-sm border-left-primary">
                                <div class="card-body">
                                    <h6 class="fw-bold">{{ $criteria->masterCriteria->Name }}</h6>
                                    <p class="text-muted small">{{ $criteria->masterCriteria->Description }}</p>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="evaluations[{{ $criteria->CriteriaID }}][Score]" class="form-label">Score (Max: 10)</label>
                                            <input type="number" name="evaluations[{{ $criteria->CriteriaID }}][Score]" id="evaluations[{{ $criteria->CriteriaID }}][Score]" class="form-control" value="{{ old("evaluations.{$criteria->CriteriaID}.Score", $evaluation->Score ?? '') }}" min="0" max="10" required>
                                        </div>
                                        <div class="col-md-9">
                                            <label for="evaluations[{{ $criteria->CriteriaID }}][Remarks]" class="form-label">Remarks</label>
                                            <textarea name="evaluations[{{ $criteria->CriteriaID }}][Remarks]" id="evaluations[{{ $criteria->CriteriaID }}][Remarks]" class="form-control" rows="2">{{ old("evaluations.{$criteria->CriteriaID}.Remarks", $evaluation->Remarks ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <input type="hidden" name="evaluations[{{ $criteria->CriteriaID }}][CriteriaID]" value="{{ $criteria->CriteriaID }}">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="alert alert-info text-center" role="alert">
                    No criteria have been configured for this prequalification round.
                </div>
                @endforelse
            </div>
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center ms-auto">
                    <i class="bi bi-save me-2"></i> Submit Evaluation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection