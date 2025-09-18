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
                    {{ count($scores) > 0 ? number_format($scores[0]['total_score'], 1) . '%' : 'N/A' }}
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
                        $avgScore = count($scores) > 0 ? collect($scores)->avg('total_score') : 0;
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
        <thead class="table-primary text-center">
            <tr>
                <th width="8%">Rank</th>
                <th width="25%">Bidder Name</th>
                <th width="12%">Technical</th>
                <th width="12%">Financial</th>
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
                    $grade = 'F';
                    $gradeClass = 'bg-danger';
                    if ($score['total_score'] >= 90) { $grade = 'A+'; $gradeClass = 'bg-success'; }
                    elseif ($score['total_score'] >= 85) { $grade = 'A'; $gradeClass = 'bg-success'; }
                    elseif ($score['total_score'] >= 80) { $grade = 'A-'; $gradeClass = 'bg-info'; }
                    elseif ($score['total_score'] >= 75) { $grade = 'B+'; $gradeClass = 'bg-info'; }
                    elseif ($score['total_score'] >= 70) { $grade = 'B'; $gradeClass = 'bg-warning text-dark'; }
                    elseif ($score['total_score'] >= 65) { $grade = 'B-'; $gradeClass = 'bg-warning text-dark'; }
                    elseif ($score['total_score'] >= 60) { $grade = 'C'; $gradeClass = 'bg-secondary'; }
                @endphp
                
                <tr class="{{ $index === 0 ? 'table-success' : ($score['total_score'] < 70 ? 'table-light' : '') }}">
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
                                {{ strtoupper(substr($score['supplier']->SupplierName, 0, 2)) }}
                            </div>
                            <div>
                                <strong>{{ $score['supplier']->SupplierName }}</strong>
                                @if($index === 0)
                                    <br><small class="badge bg-success">Top Performer</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="progress mb-1" style="height: 20px;">
                            <div class="progress-bar bg-info" style="width: {{ $score['technical_score'] }}%;">
                                {{ number_format($score['technical_score'], 1) }}%
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="progress mb-1" style="height: 20px;">
                            <div class="progress-bar bg-warning" style="width: {{ $score['financial_score'] }}%;">
                                {{ number_format($score['financial_score'], 1) }}%
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-flex flex-column">
                            <strong class="fs-5">{{ number_format($score['total_score'], 1) }}%</strong>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: {{ $score['total_score'] }}%;"></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $gradeClass }} fs-6">{{ $grade }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $score['is_responsive'] ? 'bg-success' : 'bg-danger' }}">
                            {{ $score['is_responsive'] ? '✅ Yes' : '❌ No' }}
                        </span>
                    </td>
                    @if(!isset($existingAward))
                        <td class="text-center">
                            @if($score['is_responsive'])
                                <input type="radio" name="winning_supplier_id" 
                                       value="{{ $score['supplier']->Id }}" 
                                       {{ $index === 0 ? 'checked' : '' }}
                                       class="form-check-input"
                                       style="transform: scale(1.2);"
                                       onchange="updateHiddenScores({{ $score['technical_score'] }}, {{ $score['financial_score'] }}, {{ $score['total_score'] }})">
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
    <input type="hidden" name="technical_score" id="technical_score" value="{{ $scores[0]['technical_score'] ?? 0 }}">
    <input type="hidden" name="financial_score" id="financial_score" value="{{ $scores[0]['financial_score'] ?? 0 }}">
    <input type="hidden" name="total_score" id="total_score" value="{{ $scores[0]['total_score'] ?? 0 }}">
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
