@extends('layouts.app')
@section('title', 'Evaluator-wise Score Analysis')
@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4><i class="fas fa-user-friends"></i> Evaluator-wise Score Analysis</h4>
                <p class="text-muted mb-0">
                    <strong>{{ $tender->TenderNo ?? 'N/A' }}</strong> - {{ $tender->Title ?? 'N/A' }}
                </p>
            </div>
            <div>
                <a href="{{ route('bidscores.index', ['tender_id' => $tender->Id]) }}"
                   class="btn btn-outline-secondary">
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

        @if(empty($evaluatorDetails))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> No evaluator data found for this tender.
            </div>
        @else
            <div class="row">
                @foreach($evaluatorDetails as $evaluator)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">
                                            <i class="fas fa-user"></i> {{ $evaluator['evaluator_name'] }}
                                        </h6>
                                        <small class="text-muted">{{ $evaluator['role'] }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="h5 mb-0 text-primary">{{ $evaluator['total_weighted_score'] }}%
                                        </div>
                                        <small class="text-muted">Overall Score</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(empty($evaluator['section_scores']))
                                    <div class="text-center py-3">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                        <p class="text-muted small">No section scores recorded.</p>
                                    </div>
                                @else
                                    <!-- Section Scores -->
                                    <div class="section-scores">
                                        @foreach($evaluator['section_scores'] as $sectionScore)
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="fw-bold">{{ $sectionScore['section_name'] }}</small>
                                                    <small class="text-muted">{{ $sectionScore['section_weight'] }}
                                                        %</small>
                                                </div>

                                                <div class="mb-1">
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar bg-info"
                                                             style="width: {{ min(100, $sectionScore['percentage_score']) }}%;">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between small text-muted">
                                                    <span>Raw: {{ $sectionScore['raw_score'] }}/10</span>
                                                    <span>%: {{ $sectionScore['percentage_score'] }}%</span>
                                                    <span>Weighted: {{ $sectionScore['weighted_score'] }}%</span>
                                                </div>

                                                @if($sectionScore['criteria_count'] > 0)
                                                    <div class="text-end">
                                                        <small class="badge bg-light text-dark">
                                                            {{ $sectionScore['criteria_count'] }} criteria
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Evaluator Performance Indicators -->
                                    @php
                                        $sectionCount = count($evaluator['section_scores']);
                                        $avgPercentage = collect($evaluator['section_scores'])->avg('percentage_score');
                                        $consistency = collect($evaluator['section_scores'])->pluck('percentage_score')->pipe(function($scores) {
                                            if ($scores->count() <= 1) return 100;
                                            $mean = $scores->avg();
                                            $variance = $scores->map(function($score) use ($mean) {
                                                return pow($score - $mean, 2);
                                            })->avg();
                                            $stdDev = sqrt($variance);
                                            return max(0, 100 - ($stdDev * 2)); // Consistency score
                                        });
                                    @endphp

                                    <div class="mt-3 p-2 bg-light rounded">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div
                                                    class="small fw-bold text-primary">{{ number_format($avgPercentage, 1) }}
                                                    %
                                                </div>
                                                <div class="small text-muted">Avg Score</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="small fw-bold text-success">{{ $sectionCount }}</div>
                                                <div class="small text-muted">Sections</div>
                                            </div>
                                            <div class="col-4">
                                                <div
                                                    class="small fw-bold text-info">{{ number_format($consistency, 0) }}
                                                    %
                                                </div>
                                                <div class="small text-muted">Consistency</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Member ID: {{ $evaluator['member_id'] }}
                                    </small>
                                    @php
                                        $performanceClass = $evaluator['total_weighted_score'] >= 80 ? 'success' :
                                                          ($evaluator['total_weighted_score'] >= 60 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $performanceClass }}">
                                    @if($evaluator['total_weighted_score'] >= 80)
                                            Excellent
                                        @elseif($evaluator['total_weighted_score'] >= 60)
                                            Good
                                        @else
                                            Needs Review
                                        @endif
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Evaluator Comparison Chart -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">📊 Evaluator Performance Comparison</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="table-light">
                            <tr>
                                <th>Evaluator</th>
                                <th>Role</th>
                                @if(!empty($evaluatorDetails))
                                    @foreach($evaluatorDetails[0]['section_scores'] as $section)
                                        <th class="text-center">{{ $section['section_name'] }}
                                            ({{ $section['section_weight'] }}%)
                                        </th>
                                    @endforeach
                                @endif
                                <th class="text-center fw-bold">Total Score</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($evaluatorDetails as $evaluator)
                                <tr>
                                    <td class="fw-bold">{{ $evaluator['evaluator_name'] }}</td>
                                    <td>{{ $evaluator['role'] }}</td>
                                    @php $sectionsByName = collect($evaluator['section_scores'])->keyBy('section_name'); @endphp
                                    @foreach($evaluatorDetails[0]['section_scores'] as $expectedSection)
                                        @php $sectionScore = $sectionsByName->get($expectedSection['section_name']); @endphp
                                        <td class="text-center">
                                            @if($sectionScore)
                                                <span class="d-inline-block" style="width: 60px;">
                                                    {{ $sectionScore['percentage_score'] }}%
                                                </span>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-primary"
                                                         style="width: {{ min(100, $sectionScore['percentage_score']) }}%;">
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center fw-bold text-primary">
                                        {{ $evaluator['total_weighted_score'] }}%
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Statistics -->
                    @php
                        $allTotalScores = collect($evaluatorDetails)->pluck('total_weighted_score');
                        $avgTotal = $allTotalScores->avg();
                        $maxTotal = $allTotalScores->max();
                        $minTotal = $allTotalScores->min();
                        $totalStdDev = $allTotalScores->count() > 1 ? sqrt($allTotalScores->map(function($score) use ($avgTotal) {
                            return pow($score - $avgTotal, 2);
                        })->sum() / ($allTotalScores->count() - 1)) : 0;
                    @endphp

                    <div class="mt-3 p-3 bg-light rounded">
                        <h6 class="fw-bold mb-3">🎯 Overall Statistics</h6>
                        <div class="row text-center">
                            <div class="col-6 col-md-3">
                                <div class="h5 mb-1 text-primary">{{ number_format($avgTotal, 2) }}%</div>
                                <small class="text-muted">Average Score</small>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="h5 mb-1 text-success">{{ number_format($maxTotal, 2) }}%</div>
                                <small class="text-muted">Highest Score</small>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="h5 mb-1 text-warning">{{ number_format($minTotal, 2) }}%</div>
                                <small class="text-muted">Lowest Score</small>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="h5 mb-1 text-info">{{ number_format($totalStdDev, 2) }}%</div>
                                <small class="text-muted">Score Variance</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Navigation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Quick Navigation</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('bidscores.section-drilldown', $tender->Id) }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-chart-bar"></i> Section-wise Analysis
                            </a>
                            <a href="{{ route('bidscores.index', ['tender_id' => $tender->Id]) }}"
                               class="btn btn-outline-info btn-sm">
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
            .section-scores {
                max-height: 300px;
                overflow-y: auto;
            }

            .card {
                transition: transform 0.2s;
            }

            .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            @media print {
                .card {
                    break-inside: avoid;
                    page-break-inside: avoid;
                }

                .btn {
                    display: none !important;
                }

                .card:hover {
                    transform: none;
                    box-shadow: none;
                }
            }
        </style>
    @endpush
@endsection
