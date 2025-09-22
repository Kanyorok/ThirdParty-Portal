@extends('layouts.app')

@section('title', 'Evaluate Application')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-primary">Evaluate Application: {{ $application->applicationNo }}</h4>
                <a href="{{ route('prequalification.applications.show', $application->ApplicationID) }}"
                   class="btn btn-light">
                    <i class="fas fa-arrow-circle-left me-2"></i>Back to Application
                </a>
            </div>
            <div class="card-body">
                <p class="mb-4">
                    <strong>Round:</strong> {{ $application->round->Title }}<br>
                    <strong>Supplier:</strong> {{ $application->supplier->ThirdPartyName }}
                </p>

                <form
                    action="{{ route('prequalification.prequalification-evaluation.submit', $application->ApplicationID) }}"
                    method="POST">
                    @csrf

                    @foreach ($sections as $section)
                        <div class="mb-4">
                            <h5 class="text-secondary">{{ $section->masterSection->SectionName }} <small
                                    class="text-muted ms-2">({{ $section->Weight }}%)</small></h5>
                            <hr class="mt-1">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50%;">CRITERIA</th>
                                        <th style="width: 15%;">MAX SCORE</th>
                                        <th style="width: 15%;">SCORE AWARDED</th>
                                        <th style="width: 20%;">COMMENTS</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($section->criteria as $criteria)
                                        @php
                                            $existing = $existingEvaluations->get($criteria->CriteriaId);
                                            $score = old("criteria_scores.{$criteria->CriteriaId}.score", $existing->Score ?? '');
                                            $comments = old("criteria_scores.{$criteria->CriteriaId}.comments", $existing->Remarks ?? '');
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $criteria->masterCriteria->CriteriaName }}</strong>
                                                <!-- <small class="d-block text-muted">{{ $criteria->masterCriteria->Description }}</small> -->
                                            </td>
                                            <td>{{ $criteria->Weight }}</td>
                                            <td>
                                                <input type="number"
                                                       name="criteria_scores[{{ $criteria->CriteriaId }}][score]"
                                                       class="form-control form-control-sm @error(" criteria_scores.{$criteria->CriteriaId}.score") is-invalid @enderror"
                                                       value="{{ $score }}"
                                                       step="0.01" min="0" max="{{ $criteria->Weight }}">
                                                <input type="hidden"
                                                       name="criteria_scores[{{ $criteria->CriteriaId }}][criteria_id]"
                                                       value="{{ $criteria->CriteriaId }}">
                                                <input type="hidden"
                                                       name="criteria_scores[{{ $criteria->CriteriaId }}][max_score]"
                                                       value="{{ $criteria->Weight }}">
                                                <input type="hidden"
                                                       name="criteria_scores[{{ $criteria->CriteriaId }}][section_id]"
                                                       value="{{ $section->SectionID }}">
                                                @error("criteria_scores.{$criteria->CriteriaId}.score")
                                                <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </td>
                                            <td>
                                        <textarea name="criteria_scores[{{ $criteria->CriteriaId }}][comments]"
                                                  class="form-control form-control-sm"
                                                  rows="1">{{ $comments }}</textarea>
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
                        <textarea name="general_comments" id="general_comments" class="form-control"
                                  rows="4">{{ old('general_comments', $application->GeneralComments ?? '') }}</textarea>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Evaluation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
