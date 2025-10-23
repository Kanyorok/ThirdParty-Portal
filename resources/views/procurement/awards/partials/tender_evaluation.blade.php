<!-- Tender Evaluation Results -->
<h6 class="fw-bold mb-3">📊 Consolidated Evaluation Results</h6>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted">Total Bidders</h6>
                <h4 class="text-primary mb-0">{{ count($scores) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted">Highest Score</h6>
                <h4 class="text-success mb-0">
                    {{ count($scores) > 0 ? number_format($scores[0]['total_weighted_score'] ?? $scores[0]['total_score'] ?? 0, 1) . '%' : 'N/A' }}
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted">Avg Score</h6>
                <h4 class="text-info mb-0">
                    @php
                        $avgScore = count($scores) > 0 ? collect($scores)->avg(function($score) {
                            return $score['total_weighted_score'] ?? $score['total_score'] ?? 0;
                        }) : 0;
                    @endphp
                    {{ number_format($avgScore, 1) }}%
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted">Passing Grade</h6>
                <h4 class="text-warning mb-0">70%</h4>
            </div>
        </div>
    </div>
</div>

<!-- Evaluation Results Table -->
<div class="table-responsive mb-4">
    <table class="table table-bordered align-middle">
        @php
            // Build dynamic section columns from the first score row
            $sectionColumns = [];
            if (!empty($scores)) {
                $firstSections = $scores[0]['section_scores'] ?? [];
                foreach ($firstSections as $s) {
                    $sectionColumns[] = [
                        'id' => $s['section_id'] ?? null,
                        'name' => $s['section_name'] ?? 'Section',
                        'weight' => $s['weight'] ?? 0,
                    ];
                }
            }
        @endphp
        <thead class="table-primary text-center">
            <tr>
                <th width="8%">Rank</th>
                <th width="25%">Bidder Name</th>
                @foreach($sectionColumns as $col)
                    <th>{{ $col['name'] }} ({{ number_format($col['weight'] ?? 0, 0) }}%)</th>
                @endforeach
                <th width="12%">Total Score</th>
                <th width="10%">Grade</th>
                <th width="10%">Responsive?</th>
                @if(!isset($existingAward))
                    <th width="11%">Select Winner</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($scores as $index => $score)
                @php
                    $totalScore = $score['total_weighted_score'] ?? $score['total_score'] ?? 0;
                    $supplierName = $score['bidder_name'] ?? ($score['supplier']->SupplierName ?? 'Unknown');
                    $supplierId = $score['bidder_id'] ?? ($score['supplier']->Id ?? null);
                    $technicalScore = $score['technical_score'] ?? 0;
                    $financialScore = $score['financial_score'] ?? 0;
                    $isResponsive = $score['is_responsive'] ?? true; // Default to true for evaluated bids
                    $sectionScoresMap = collect($score['section_scores'] ?? [])->keyBy('section_id');
                    
                    $grade = 'F';
                    $gradeClass = 'bg-danger';
                    if ($totalScore >= 90) { $grade = 'A+'; $gradeClass = 'bg-success'; }
                    elseif ($totalScore >= 85) { $grade = 'A'; $gradeClass = 'bg-success'; }
                    elseif ($totalScore >= 80) { $grade = 'A-'; $gradeClass = 'bg-info'; }
                    elseif ($totalScore >= 75) { $grade = 'B+'; $gradeClass = 'bg-info'; }
                    elseif ($totalScore >= 70) { $grade = 'B'; $gradeClass = 'bg-warning text-dark'; }
                    elseif ($totalScore >= 65) { $grade = 'B-'; $gradeClass = 'bg-warning text-dark'; }
                    elseif ($totalScore >= 60) { $grade = 'C'; $gradeClass = 'bg-secondary'; }
                @endphp
                
                <tr class="{{ $index === 0 ? 'table-success' : ($totalScore < 70 ? 'table-light' : '') }}">
                    <td class="text-center">
                        @if($index === 0)
                            <span class="badge bg-warning text-dark fs-6">🥇 1st</span>
                        @elseif($index === 1)
                            <span class="badge bg-info fs-6">🥈 2nd</span>
                        @elseif($index === 2)
                            <span class="badge bg-secondary fs-6">🥉 3rd</span>
                        @else
                            <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-primary text-white me-2" style="width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                {{ strtoupper(substr($supplierName, 0, 2)) }}
                            </div>
                            <div>
                                <strong>{{ $supplierName }}</strong>
                                @if($index === 0)
                                    <br><small class="badge bg-success">Top Performer</small>
                                @endif
                                @if(isset($score['recommendation']))
                                    <br><small class="badge bg-{{ $score['recommendation']['class'] }}">{{ $score['recommendation']['status'] }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    @foreach($sectionColumns as $col)
                        @php $sec = $sectionScoresMap->get($col['id']); $val = $sec['score'] ?? 0; @endphp
                        <td class="text-center">
                            <div class="progress mb-1" style="height: 20px;">
                                <div class="progress-bar bg-info" style="width: {{ $val }}%;">
                                    {{ number_format($val, 1) }}%
                                </div>
                            </div>
                        </td>
                    @endforeach
                    <td class="text-center">
                        <div class="d-flex flex-column">
                            <strong class="fs-5">{{ number_format($totalScore, 1) }}%</strong>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: {{ $totalScore }}%;"></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $gradeClass }} fs-6">{{ $grade }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $isResponsive ? 'bg-success' : 'bg-danger' }}">
                            {{ $isResponsive ? '✅ Yes' : '❌ No' }}
                        </span>
                    </td>
                    @if(!isset($existingAward))
                        <td class="text-center">
                            @if($isResponsive && $supplierId)
                                <input type="radio" name="winning_supplier_id" 
                                       value="{{ $supplierId }}" 
                                       {{ $index === 0 ? 'checked' : '' }}
                                       class="form-check-input"
                                       style="transform: scale(1.2);"
                                       onchange="updateHiddenScores({{ $technicalScore }}, {{ $financialScore }}, {{ $totalScore }})">
                            @else
                                <input type="radio" disabled title="Non-responsive bidder" class="form-check-input">
                                <small class="text-muted d-block">Non-responsive</small>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if(!isset($existingAward) && count($scores) > 0)
    <!-- Hidden fields for selected supplier scores -->
    @php
        $firstScore = $scores[0];
        $firstTechnical = $firstScore['technical_score'] ?? 0;
        $firstFinancial = $firstScore['financial_score'] ?? 0;
        $firstTotal = $firstScore['total_weighted_score'] ?? $firstScore['total_score'] ?? 0;
    @endphp
    <input type="hidden" name="technical_score" id="technical_score" value="{{ $firstTechnical }}">
    <input type="hidden" name="financial_score" id="financial_score" value="{{ $firstFinancial }}">
    <input type="hidden" name="total_score" id="total_score" value="{{ $firstTotal }}">
@endif

@if(empty($scores))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>No evaluation results found.</strong> 
        Please ensure bid evaluations have been completed and scores have been consolidated.
    </div>
@endif

<script>
function updateHiddenScores(technicalScore, financialScore, totalScore) {
    const technicalInput = document.getElementById('technical_score');
    const financialInput = document.getElementById('financial_score');
    const totalInput = document.getElementById('total_score');
    
    if (technicalInput) technicalInput.value = technicalScore;
    if (financialInput) financialInput.value = financialScore;
    if (totalInput) totalInput.value = totalScore;
}
</script>
