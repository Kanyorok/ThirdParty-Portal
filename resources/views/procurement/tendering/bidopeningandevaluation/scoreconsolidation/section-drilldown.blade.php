@extends('layouts.app')
@section('title', 'Section-wise Score Analysis')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4><i class="fas fa-chart-bar"></i> Section-wise Score Analysis</h4>
            <p class="text-muted mb-0">
                <strong>{{ $tender->TenderNo ?? 'N/A' }}</strong> - {{ $tender->Title ?? 'N/A' }}
            </p>
        </div>
        <div>
            <a href="{{ route('bidscores.index', ['tender_id' => $tender->Id]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Consolidated Scores
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(empty($sectionDetails))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> No section evaluation data found for this tender.
        </div>
    @else
        @foreach($sectionDetails as $sectionData)
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-list-alt"></i> {{ $sectionData['section']['name'] }}
                            </h5>
                            <small class="text-muted">Weight: {{ $sectionData['section']['weight'] }}%</small>
                        </div>
                        <div>
                            <span class="badge bg-info">{{ count($sectionData['evaluator_scores']) }} Evaluators</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(empty($sectionData['evaluator_scores']))
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No evaluator scores recorded for this section.</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach($sectionData['evaluator_scores'] as $evaluatorScore)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-user"></i> {{ $evaluatorScore['evaluator_name'] }}
                                            </h6>
                                            <p class="card-text small text-muted mb-2">
                                                Role: {{ $evaluatorScore['role'] }}
                                            </p>
                                            
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small>Section Average:</small>
                                                    <strong>{{ $evaluatorScore['average_score'] }}/10</strong>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-primary" 
                                                         style="width: {{ min(100, ($evaluatorScore['average_score'] / 10) * 100) }}%;">
                                                    </div>
                                                </div>
                                            </div>

                                            @if(!empty($evaluatorScore['criteria_scores']))
                                                <div class="criteria-details">
                                                    <small class="text-muted d-block mb-2">Individual Criteria Scores:</small>
                                                    @foreach($evaluatorScore['criteria_scores'] as $criteriaScore)
                                                        <div class="d-flex justify-content-between small mb-1">
                                                            <span>Criteria {{ $loop->iteration }}:</span>
                                                            <span class="fw-bold">{{ $criteriaScore['score'] }}/10</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Section Summary Statistics -->
                        @php
                            $allScores = collect($sectionData['evaluator_scores'])->pluck('average_score');
                            $avgScore = $allScores->avg();
                            $maxScore = $allScores->max();
                            $minScore = $allScores->min();
                            $stdDev = $allScores->count() > 1 ? sqrt($allScores->map(function($score) use ($avgScore) {
                                return pow($score - $avgScore, 2);
                            })->sum() / ($allScores->count() - 1)) : 0;
                        @endphp

                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3">📊 Section Statistics</h6>
                            <div class="row">
                                <div class="col-6 col-md-3 text-center">
                                    <div class="h5 mb-1 text-primary">{{ number_format($avgScore, 2) }}</div>
                                    <small class="text-muted">Average Score</small>
                                </div>
                                <div class="col-6 col-md-3 text-center">
                                    <div class="h5 mb-1 text-success">{{ number_format($maxScore, 2) }}</div>
                                    <small class="text-muted">Highest Score</small>
                                </div>
                                <div class="col-6 col-md-3 text-center">
                                    <div class="h5 mb-1 text-warning">{{ number_format($minScore, 2) }}</div>
                                    <small class="text-muted">Lowest Score</small>
                                </div>
                                <div class="col-6 col-md-3 text-center">
                                    <div class="h5 mb-1 text-info">{{ number_format($stdDev, 2) }}</div>
                                    <small class="text-muted">Std Deviation</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    <!-- Quick Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Quick Navigation</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('bidscores.evaluator-drilldown', $tender->Id) }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-user-friends"></i> Evaluator-wise Analysis
                        </a>
                        <a href="{{ route('bidscores.index', ['tender_id' => $tender->Id]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-chart-pie"></i> Consolidated Scores
                        </a>
                        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .criteria-details {
        border-top: 1px solid #dee2e6;
        padding-top: 0.5rem;
        margin-top: 0.5rem;
    }
    
    @media print {
        .card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .btn {
            display: none !important;
        }
    }
</style>
@endpush
@endsection
