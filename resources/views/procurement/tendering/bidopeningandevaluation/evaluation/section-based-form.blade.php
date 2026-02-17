@extends('layouts.app')
@section('title', 'Section-Based Evaluation - ' . $bid->SupplierName)

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4><i class="fas fa-clipboard-check"></i> Section-Based Evaluation</h4>
            <p class="text-muted mb-0">
                <strong>{{ $tender->TenderNo }}</strong> - {{ $tender->Title }}<br>
                <strong>Supplier:</strong> {{ $bid->SupplierName }} | 
                <strong>Bid Amount:</strong> {{ $bid->Currency }} {{ number_format($bid->BidAmount, 2) }}
            </p>
        </div>
        <div class="text-end">
            <div class="small text-muted">
                <strong>Your Role:</strong> {{ $committeeMember->Role }}<br>
                <strong>Progress:</strong> 
                <span class="badge bg-info">
                    {{ $evaluationProgress['completed'] }}/{{ $evaluationProgress['total'] }} criteria
                </span>
                ({{ $evaluationProgress['percentage'] }}%)
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Evaluation Form -->
    <form id="evaluationForm" method="POST" action="{{ route('evaluation.submit', $bid->Id) }}">
        @csrf
        
        <!-- Progress Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Evaluation Progress</h6>
                    <span id="progress-text">{{ $evaluationProgress['percentage'] }}% Complete</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div id="progress-bar" class="progress-bar bg-success" 
                         style="width: {{ $evaluationProgress['percentage'] }}%"></div>
                </div>
                <div class="small text-muted mt-1">
                    <span id="completed-count">{{ $evaluationProgress['completed'] }}</span> of 
                    <span id="total-count">{{ $evaluationProgress['total'] }}</span> criteria completed
                </div>
            </div>
        </div>

        <!-- Evaluation Sections -->
        @foreach($tenderSections as $tenderSection)
            @php
                $section = $tenderSection->sections; // Access actual Section model with criteria
                $sectionWeight = $tenderSection->Weight; // Get weight from pivot table
            @endphp
            <div class="card mb-4" id="section-{{ $section->Id }}">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-folder"></i> {{ $section->SectionName }}
                            <span class="badge bg-primary ms-2">{{ $sectionWeight }}% Weight</span>
                        </h5>
                        <div class="section-progress">
                            @php
                                // Criteria list is already filtered server-side to only tender-selected ones
                                $sectionCriteriaCount = $section->criteria->count();
                                $sectionCompletedCount = $existingScores->filter(function($score) use ($section) {
                                    return (int)$score->SectionID === (int)$section->Id;
                                })->count();
                                $sectionPercentage = $sectionCriteriaCount > 0 ? round(($sectionCompletedCount / $sectionCriteriaCount) * 100) : 0;
                            @endphp
                            <span class="badge {{ $sectionPercentage == 100 ? 'bg-success' : 'bg-warning' }}">
                                {{ $sectionCompletedCount }}/{{ $sectionCriteriaCount }}
                            </span>
                        </div>
                    </div>
                    @if($section->Description)
                        <p class="text-muted small mb-0 mt-2">{{ $section->Description }}</p>
                    @endif
                </div>
                <div class="card-body">
                    @if($section->criteria->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            No criteria defined for this section. Please contact administrator.
                        </div>
                    @else
                        <div class="row">
                            @foreach($section->criteria as $criteria)
                                @php
                                    $scoreKey = $section->Id . '_' . $criteria->Id;
                                    $existingScore = $existingScores->get($scoreKey);
                                    $currentScore = $existingScore ? $existingScore->Score : '';
                                @endphp
                                <div class="col-md-6 mb-4">
                                    <div class="criteria-item border rounded p-3 
                                        {{ $currentScore !== '' ? 'border-success bg-light' : 'border-secondary' }}">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <label for="criteria_{{ $criteria->Id }}" 
                                                       class="form-label fw-bold">
                                                    {{ $criteria->CriteriaName }}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                @if($criteria->Description)
                                                    <p class="text-muted small mb-2">
                                                        {{ $criteria->Description }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-info">Max: 10</span>
                                            </div>
                                        </div>
                                        
                                        <div class="score-input-group">
                                            <div class="input-group">
                                                <span class="input-group-text">Score</span>
                                                <input type="number" 
                                                       class="form-control criteria-score" 
                                                       id="criteria_{{ $criteria->Id }}"
                                                       name="scores[{{ $loop->parent->index . '_' . $loop->index }}][score]"
                                                       value="{{ $currentScore }}"
                                                       min="0" 
                                                       max="10" 
                                                       step="0.1"
                                                       placeholder="0.0"
                                                       oninput="updateProgress()">
                                                <span class="input-group-text">/ 10</span>
                                            </div>
                                            
                                            <!-- Hidden fields for section and criteria IDs -->
                                            <input type="hidden" 
                                                   name="scores[{{ $loop->parent->index . '_' . $loop->index }}][section_id]" 
                                                   value="{{ $section->Id }}">
                                            <input type="hidden" 
                                                   name="scores[{{ $loop->parent->index . '_' . $loop->index }}][criteria_id]" 
                                                   value="{{ $criteria->Id }}">
                                            
                                            <!-- Score visualization -->
                                            <div class="mt-2">
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar score-bar" 
                                                         data-criteria="{{ $criteria->Id }}"
                                                         style="width: {{ $currentScore ? ($currentScore / 10) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <!-- Evaluation Notes -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Evaluation Notes</h5>
            </div>
            <div class="card-body">
                <textarea class="form-control" 
                          name="evaluation_notes" 
                          rows="4" 
                          placeholder="Add any additional comments or observations about this bid evaluation...">{{ old('evaluation_notes', $bid->EvaluationNotes) }}</textarea>
                <div class="form-text">
                    Optional: Provide additional context, observations, or justifications for your evaluation.
                </div>
            </div>
        </div>

        <!-- Summary and Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calculator"></i> Evaluation Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div id="section-scores-summary">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Weighted Total Score</h6>
                                <h3 class="text-primary mb-0" id="total-weighted-score">0.0</h3>
                                <div class="small text-muted">out of 100</div>
                                <div class="mt-2">
                                    <div class="progress" style="height: 10px;">
                                        <div id="total-score-bar" 
                                             class="progress-bar bg-success" 
                                             style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="{{ route('evaluationdashboard.index') }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                    <div>
                        <button type="button" 
                                class="btn btn-outline-primary me-2" 
                                onclick="saveDraft()">
                            <i class="fas fa-save"></i> Save Draft
                        </button>
                        <button type="button" 
                                class="btn btn-success" 
                                onclick="submitFinalEvaluation()"
                                id="submit-final-btn"
                                disabled>
                            <i class="fas fa-check-circle"></i> Submit Final Evaluation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Committee Members Modal -->
<div class="modal fade" id="committeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Evaluation Committee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Committee Members:</h6>
                <ul class="list-group">
                    @foreach($allMembers as $member)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ optional($member->user)->employee->full_name ?? optional($member->userByEmployee)->employee->full_name ?? 'Unknown' }}
                            <div>
                                <span class="badge bg-primary">{{ $member->Role }}</span>
                                @if($member->HasEvaluated)
                                    <span class="badge bg-success">✅ Completed</span>
                                @else
                                    <span class="badge bg-warning">⏳ Pending</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Section weights for calculation
const sectionWeights = {
    @foreach($tenderSections as $tenderSection)
        {{ $tenderSection->sections->Id }}: {{ $tenderSection->Weight }},
    @endforeach
};

// Update progress and calculations
function updateProgress() {
    const scoreInputs = document.querySelectorAll('.criteria-score');
    let completedCount = 0;
    const totalCount = scoreInputs.length;
    
    scoreInputs.forEach(function(input) {
        if (input.value && input.value !== '') {
            completedCount++;
            // Update individual score bar
            const scoreBar = document.querySelector(`[data-criteria="${input.id.split('_')[1]}"]`);
            if (scoreBar) {
                const percentage = (parseFloat(input.value) / 10) * 100;
                scoreBar.style.width = percentage + '%';
                scoreBar.className = 'progress-bar score-bar ' + getScoreColorClass(percentage);
            }
        }
    });
    
    // Update progress bar
    const percentage = totalCount > 0 ? (completedCount / totalCount) * 100 : 0;
    document.getElementById('progress-bar').style.width = percentage + '%';
    document.getElementById('progress-text').textContent = Math.round(percentage) + '% Complete';
    document.getElementById('completed-count').textContent = completedCount;
    
    // Enable/disable final submit button
    const submitBtn = document.getElementById('submit-final-btn');
    submitBtn.disabled = completedCount !== totalCount;
    
    // Update section scores and total
    updateSectionScores();
}

function updateSectionScores() {
    let totalWeightedScore = 0;
    let summaryHtml = '';
    
    // Calculate scores for each section
    Object.keys(sectionWeights).forEach(function(sectionId) {
        const sectionInputs = document.querySelectorAll(`input[name*="[section_id]"][value="${sectionId}"]`);
        let sectionTotal = 0;
        let sectionCount = 0;
        
        sectionInputs.forEach(function(hiddenInput) {
            const scoreInput = hiddenInput.parentElement.querySelector('input[name*="[score]"]');
            if (scoreInput && scoreInput.value) {
                sectionTotal += parseFloat(scoreInput.value);
                sectionCount++;
            }
        });
        
        if (sectionCount > 0) {
            const sectionAverage = sectionTotal / sectionCount;
            const sectionWeight = sectionWeights[sectionId];
            // Correct formula: (Average Score × 10 × Weight) / 100
            const weightedScore = (sectionAverage * 10 * sectionWeight) / 100;
            totalWeightedScore += weightedScore;
            
            summaryHtml += `
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Section ${sectionId}:</span>
                        <span><strong>${sectionAverage.toFixed(1)}/10 × 10 × ${sectionWeight}% = ${weightedScore.toFixed(1)}</strong></span>
                    </div>
                </div>
            `;
        }
    });
    
    // Update summary display
    document.getElementById('section-scores-summary').innerHTML = summaryHtml;
    document.getElementById('total-weighted-score').textContent = totalWeightedScore.toFixed(1);
    
    // Update total score bar
    const totalScoreBar = document.getElementById('total-score-bar');
    totalScoreBar.style.width = totalWeightedScore + '%';
    totalScoreBar.className = 'progress-bar ' + getScoreColorClass(totalWeightedScore);
}

function getScoreColorClass(percentage) {
    if (percentage >= 80) return 'bg-success';
    if (percentage >= 60) return 'bg-info';
    if (percentage >= 40) return 'bg-warning';
    return 'bg-danger';
}

function saveDraft() {
    submitEvaluation('draft');
}

function submitFinalEvaluation() {
    if (confirm('Are you sure you want to submit your final evaluation? This action cannot be undone.')) {
        submitEvaluation('final');
    }
}

function submitEvaluation(type) {
    const form = document.getElementById('evaluationForm');
    const formData = new FormData(form);
    formData.append('evaluation_type', type);
    
    // Show loading state
    const submitBtn = document.getElementById('submit-final-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (type === 'final' && data.redirect) {
                window.location.href = data.redirect;
            } else {
                alert(data.message + (data.total_score ? ' Total Score: ' + data.total_score : ''));
                if (type === 'draft') {
                    // Keep form open for continued editing
                    submitBtn.innerHTML = originalText;
                    updateProgress(); // Refresh progress
                }
            }
        } else {
            alert('Error: ' + (data.error || 'Failed to submit evaluation'));
            submitBtn.innerHTML = originalText;
            updateProgress();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the evaluation.');
        submitBtn.innerHTML = originalText;
        updateProgress();
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add CSRF token to page
    const csrfMeta = document.createElement('meta');
    csrfMeta.name = 'csrf-token';
    csrfMeta.content = '{{ csrf_token() }}';
    document.head.appendChild(csrfMeta);
    
    // Initialize progress calculation
    updateProgress();
    
    // Add change listeners to all score inputs
    document.querySelectorAll('.criteria-score').forEach(function(input) {
        input.addEventListener('input', updateProgress);
    });
});
</script>

<style>
.criteria-item {
    transition: all 0.3s ease;
}

.criteria-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.score-input-group .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.progress-bar {
    transition: width 0.3s ease, background-color 0.3s ease;
}

.section-progress .badge {
    font-size: 0.75em;
}

#total-weighted-score {
    font-size: 2.5rem;
    font-weight: bold;
}
</style>
@endsection
