@extends('layouts.app')
@section('title', 'Bid Scoring Consolidation')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📊 Bid Scoring Consolidation – {{ $tender->TenderNo ?? 'N/A' }}</h4>

    <!-- Tender Info Summary -->
    <div class="row mb-3">
        <div class="col-md-6">
            <strong>Item:</strong> {{ $tender->Title ?? 'N/A' }}
        </div>
        <div class="col-md-6">
            <strong>Evaluators:</strong> {{ $evaluatorCount }} Committee Members
        </div>
    </div>

    @if($consolidatedScores->isEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> No evaluation data found for this tender. Please ensure evaluations have been completed.
        </div>
    @else
        <!-- Consolidated Scoring Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center align-middle">
                    <tr>
                        <th rowspan="2">#</th>
                        <th rowspan="2">Bidder</th>
                        @php
                            $sectionHeaders = $sections->take(5); // Limit display sections for table width
                        @endphp
                        @foreach($sectionHeaders as $section)
                            <th>{{ $section['name'] }} ({{ $section['weight'] }}%)</th>
                        @endforeach
                        <th rowspan="2">Total Weighted Score (%)</th>
                        <th rowspan="2">Rank</th>
                        <th rowspan="2">Recommendation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($consolidatedScores as $score)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $score['bidder_name'] }}</td>
                            
                            @foreach($score['section_scores'] as $sectionScore)
                                @if($loop->iteration <= 5) <!-- Limit to 5 sections for display -->
                                    <td>
                                        <div class="d-flex flex-column text-center">
                                            <small>{{ number_format($sectionScore['score'], 1) }}%</small>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-info" style="width: {{ min(100, $sectionScore['score']) }}%;"></div>
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            @endforeach
                            
                            <!-- Fill remaining columns if fewer than 5 sections -->
                            @for($i = count($score['section_scores']); $i < 5; $i++)
                                <td>
                                    <div class="d-flex flex-column text-center">
                                        <small>N/A</small>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-secondary" style="width: 0%;"></div>
                                        </div>
                                    </div>
                                </td>
                            @endfor
                            
                            <td><strong>{{ number_format($score['total_weighted_score'], 1) }}%</strong></td>
                            <td>{{ $score['rank'] }}</td>
                            <td>
                                <span class="badge {{ $score['recommendation']['class'] }}">
                                    {{ $score['recommendation']['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Detailed Section Breakdown -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="fw-bold">📋 Detailed Section Breakdown</h5>
                <div class="accordion" id="sectionBreakdown">
                    @foreach($consolidatedScores->take(3) as $score) <!-- Show breakdown for top 3 bidders -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->iteration }}" 
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $score['bidder_name'] }} - {{ number_format($score['total_weighted_score'], 1) }}%
                                </button>
                            </h2>
                            <div id="collapse{{ $loop->iteration }}" 
                                 class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                                 data-bs-parent="#sectionBreakdown">
                                <div class="accordion-body">
                                    <div class="row">
                                        @foreach($score['section_scores'] as $sectionScore)
                                            <div class="col-md-4 mb-3">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <h6 class="card-title">{{ $sectionScore['section_name'] }}</h6>
                                                        <div class="progress mb-2">
                                                            <div class="progress-bar bg-primary" 
                                                                 style="width: {{ min(100, $sectionScore['score']) }}%;">
                                                            </div>
                                                        </div>
                                                        <p class="card-text">
                                                            <strong>{{ number_format($sectionScore['score'], 1) }}%</strong><br>
                                                            <small class="text-muted">Weight: {{ $sectionScore['weight'] }}%</small>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="d-flex gap-2 justify-content-end mb-5">
        @if(!$consolidatedScores->isEmpty())
            <button class="btn btn-success" onclick="forwardForAward()">
                <i class="fas fa-award"></i> Forward for Award
            </button>
            <button class="btn btn-danger" onclick="rejectAllBids()">
                <i class="fas fa-times-circle"></i> Reject All Bids
            </button>
        @endif
        <a href="{{ route('bidscores.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Select Different Tender
        </a>
        <button class="btn btn-outline-dark" onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<script>
function forwardForAward() {
    if (confirm('Are you sure you want to forward the top bidder for award?')) {
        // Add your forward for award logic here
        alert('Feature coming soon: Forward for award functionality');
    }
}

function rejectAllBids() {
    if (confirm('Are you sure you want to reject all bids? This action cannot be undone.')) {
        // Add your reject all bids logic here
        alert('Feature coming soon: Reject all bids functionality');
    }
}
</script>
@endsection

@push('styles')
<style>
    .table thead th {
        font-size: 0.85rem;
        font-weight: 600;
    }
    .badge.fs-6 {
        font-size: 1rem !important;
        padding: 0.5rem;
        border-radius: 50%;
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush
