@extends('layouts.app')

@section('title', 'Evaluate Application')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-primary">
                @if($isReadonly)
                    <i class="fas fa-lock me-2 text-warning"></i>View Evaluation Results: {{ $application->applicationNo }}
                @else
                    Evaluate Application: {{ $application->applicationNo }}
                @endif
            </h4>
            <a href="{{ route('prequalification.applications.show', $application->ApplicationID) }}" class="btn btn-light">
                <i class="fas fa-arrow-circle-left me-2"></i>Back to Application
            </a>
        </div>
        <div class="card-body">
            <p class="mb-4">
                <strong>Round:</strong> {{ $application->round->Title }}<br>
                <strong>Supplier:</strong> {{ $application->supplier->ThirdPartyName }}
            </p>

            @if($isReadonly)
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Read-only Mode:</strong> This evaluation has already been completed and cannot be modified.
                        @if($result)
                            <br><strong>Decision:</strong> 
                            <span class="badge {{ $result->Decision === 'Passed' ? 'bg-success' : 'bg-danger' }}">
                                {{ $result->Decision }}
                            </span>
                            @if($result->TotalScore)
                                | <strong>Total Score:</strong> {{ number_format($result->TotalScore, 2) }}%
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            <form action="{{ route('prequalification.prequalification-evaluation.submit', $application->ApplicationID) }}" method="POST">
                @csrf

                @foreach ($sections as $section)
                <div class="mb-4">
                    <h5 class="text-secondary">{{ $section->masterSection->SectionName }} <small class="text-muted ms-2">({{ $section->Weight }}%)</small></h5>
                    <hr class="mt-1">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60%;">CRITERIA</th>
                                    <th style="width: 20%;">MAX SCORE (10)</th>
                                    <th style="width: 20%;">SCORE AWARDED</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Ensure criteria list is unique by CriteriaId to avoid duplicates due to joins
                                    $criteriaList = ($section->criteria instanceof \Illuminate\Support\Collection)
                                        ? $section->criteria->unique('CriteriaId')->values()
                                        : collect($section->criteria)->unique('CriteriaId')->values();
                                @endphp
                                @foreach ($criteriaList as $criteria)
                                @php
                                $existing = $existingEvaluations->get($criteria->CriteriaId);
                                $score = old("criteria_scores.{$criteria->CriteriaId}.score", $existing->Score ?? '');
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $criteria->masterCriteria->CriteriaName }}</strong>
                                        <!-- <small class="d-block text-muted">{{ $criteria->masterCriteria->Description }}</small> -->
                                    </td>
                                    <td>10</td>
                                    <td>
                                        <input type="number"
                                            name="criteria_scores[{{ $criteria->CriteriaId }}][score]"
                                            class="form-control form-control-sm @error('criteria_scores.'.$criteria->CriteriaId.'.score') is-invalid @enderror"
                                            value="{{ $score }}"
                                            step="0.01" min="0" max="10"
                                            @if($isReadonly) readonly @endif>
                                        <input type="hidden" name="criteria_scores[{{ $criteria->CriteriaId }}][criteria_id]" value="{{ $criteria->CriteriaId }}">
                                        <input type="hidden" name="criteria_scores[{{ $criteria->CriteriaId }}][max_score]" value="10">
                                        <input type="hidden" name="criteria_scores[{{ $criteria->CriteriaId }}][section_id]" value="{{ $section->SectionID }}">
                                        @error('criteria_scores.'.$criteria->CriteriaId.'.score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach

                <div class="mb-3">
                    <label for="general_comments" class="form-label">General Comments</label>
                    <textarea name="general_comments" id="general_comments" class="form-control" rows="4" @if($isReadonly) readonly @endif>{{ old('general_comments', $application->GeneralComments ?? '') }}</textarea>
                </div>

                @if(!$isReadonly)
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Evaluation
                        </button>
                    </div>
                @else
                    <div class="mt-4 text-end">
                        <a href="{{ route('prequalification.prequalification-evaluation.results', $application->ApplicationID) }}" class="btn btn-info">
                            <i class="fas fa-eye me-2"></i>View Results
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection