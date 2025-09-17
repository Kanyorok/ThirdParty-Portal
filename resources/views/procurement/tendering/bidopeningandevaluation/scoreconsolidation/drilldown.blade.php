@extends('layouts.app')
@section('title', 'Evaluation Drill-Down')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🔍 Evaluation Drill-Down</h4>
        <a href="{{ route('bidscores.index', ['tender_id' => $tender->Id]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Consolidation
        </a>
    </div>

    <!-- Supplier & Tender Info -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Tender:</strong> {{ $tender->TenderNo }} - {{ $tender->Title }}
                </div>
                <div class="col-md-4">
                    <strong>Supplier:</strong> {{ $supplier->supplier->SupplierName }}
                </div>
                <div class="col-md-4">
                    <strong>Total Evaluators:</strong> {{ $evaluations->count() }}
                </div>
            </div>
        </div>
    </div>

    @if($evaluations->isEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>No Evaluation Data Found!</strong>
            <br>No committee member has evaluated this supplier yet.
        </div>
    @else
        <!-- Individual Evaluator Scores -->
        <div class="row">
            @foreach($evaluations as $memberId => $memberEvaluations)
                @php
                    $evaluator = $memberEvaluations->first()->tenderCommitteeMember->user ?? null;
                    $groupedBySection = $memberEvaluations->groupBy('SectionID');
                @endphp
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-user"></i> 
                                {{ $evaluator->name ?? 'Unknown Evaluator' }}
                                <small class="text-muted">({{ $evaluator->email ?? 'N/A' }})</small>
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach($groupedBySection as $sectionId => $sectionEvaluations)
                                @php
                                    $sectionName = $sectionEvaluations->first()->section->SectionName ?? 'Unknown Section';
                                    $sectionTotal = 0;
                                    $criteriaCount = $sectionEvaluations->count();
                                @endphp
                                
                                <div class="mb-3">
                                    <h6 class="text-primary border-bottom pb-1">{{ $sectionName }}</h6>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="width: 60%;">Criteria</th>
                                                    <th style="width: 25%;">Score</th>
                                                    <th style="width: 15%;">Visual</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sectionEvaluations as $evaluation)
                                                    @php
                                                        $score = $evaluation->MaxScore;
                                                        $sectionTotal += $score;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <small>{{ $evaluation->criteria->CriteriaName ?? 'Unknown Criteria' }}</small>
                                                        </td>
                                                        <td>
                                                            <strong>{{ number_format($score, 1) }}/10</strong>
                                                        </td>
                                                        <td>
                                                            <div class="progress" style="height: 8px;">
                                                                <div class="progress-bar 
                                                                    @if($score >= 8) bg-success 
                                                                    @elseif($score >= 6) bg-warning 
                                                                    @else bg-danger 
                                                                    @endif" 
                                                                    style="width: {{ ($score/10) * 100 }}%;">
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-secondary">
                                                    <td><strong>Section Average</strong></td>
                                                    <td><strong>{{ $criteriaCount > 0 ? number_format($sectionTotal / $criteriaCount, 2) : 0 }}/10</strong></td>
                                                    <td>
                                                        @php $avgScore = $criteriaCount > 0 ? $sectionTotal / $criteriaCount : 0; @endphp
                                                        <div class="progress" style="height: 10px;">
                                                            <div class="progress-bar 
                                                                @if($avgScore >= 8) bg-success 
                                                                @elseif($avgScore >= 6) bg-warning 
                                                                @else bg-danger 
                                                                @endif" 
                                                                style="width: {{ ($avgScore/10) * 100 }}%;">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                            
                            <!-- Overall Score for this Evaluator -->
                            @php
                                $totalScores = $memberEvaluations->sum('MaxScore');
                                $totalCriteria = $memberEvaluations->count();
                                $overallAverage = $totalCriteria > 0 ? $totalScores / $totalCriteria : 0;
                            @endphp
                            
                            <div class="alert alert-info">
                                <strong>Overall Average Score:</strong> 
                                <span class="badge bg-primary fs-6">{{ number_format($overallAverage, 2) }}/10</span>
                                <br>
                                <small>Based on {{ $totalCriteria }} criteria evaluated</small>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>
                                <i class="fas fa-clock"></i> 
                                Evaluated on: {{ $memberEvaluations->first()->CreatedOn->format('M d, Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Comparative Summary -->
        <div class="card mt-4">
            <div class="card-header">
                <h6>📊 Evaluator Comparison Summary</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Evaluator</th>
                                <th>Overall Average</th>
                                <th>Total Criteria Evaluated</th>
                                <th>Evaluation Date</th>
                                <th>Score Distribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($evaluations as $memberId => $memberEvaluations)
                                @php
                                    $evaluator = $memberEvaluations->first()->tenderCommitteeMember->user ?? null;
                                    $totalScores = $memberEvaluations->sum('MaxScore');
                                    $totalCriteria = $memberEvaluations->count();
                                    $overallAverage = $totalCriteria > 0 ? $totalScores / $totalCriteria : 0;
                                    
                                    // Score distribution
                                    $excellent = $memberEvaluations->where('MaxScore', '>=', 8)->count();
                                    $good = $memberEvaluations->whereBetween('MaxScore', [6, 7.9])->count();
                                    $poor = $memberEvaluations->where('MaxScore', '<', 6)->count();
                                @endphp
                                <tr>
                                    <td>{{ $evaluator->name ?? 'Unknown' }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($overallAverage >= 8) bg-success 
                                            @elseif($overallAverage >= 6) bg-warning 
                                            @else bg-danger 
                                            @endif fs-6">
                                            {{ number_format($overallAverage, 2) }}/10
                                        </span>
                                    </td>
                                    <td>{{ $totalCriteria }}</td>
                                    <td>{{ $memberEvaluations->first()->CreatedOn->format('M d, Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <span class="badge bg-success">{{ $excellent }} Excellent</span>
                                            <span class="badge bg-warning">{{ $good }} Good</span>
                                            <span class="badge bg-danger">{{ $poor }} Poor</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .progress {
        background-color: #f0f0f0;
    }
    .card {
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .table th {
        font-size: 0.85rem;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
</style>
@endpush
