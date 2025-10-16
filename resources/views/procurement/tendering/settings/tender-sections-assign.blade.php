@extends('layouts.app')
@section('title', 'Assign Evaluation Sections - ' . $tender->TenderNo)

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4><i class="fas fa-clipboard-list"></i> Assign Evaluation Sections</h4>
            <p class="text-muted mb-0">
                <strong>{{ $tender->TenderNo }}</strong> - {{ $tender->Title }}
            </p>
        </div>
        <a href="{{ route('tender-sections.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Overview
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('tender-sections.store') }}">
        @csrf
        <input type="hidden" name="tender_id" value="{{ $tender->Id }}">

        <!-- Current Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> Current Configuration Status
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Assigned Sections</h6>
                                <h3 class="text-primary">{{ $assignedSections->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Total Weight</h6>
                                <h3 class="{{ abs($totalWeight - 100) < 0.01 ? 'text-success' : 'text-danger' }}">
                                    {{ $totalWeight }}%
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Status</h6>
                                <h5>
                                    @if(abs($totalWeight - 100) < 0.01 && $assignedSections->count() > 0)
                                        <span class="badge bg-success">✅ Valid</span>
                                    @elseif($assignedSections->count() === 0)
                                        <span class="badge bg-warning">⚠️ Not Configured</span>
                                    @else
                                        <span class="badge bg-danger">❌ Invalid</span>
                                    @endif
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Assignment -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cogs"></i> Configure Evaluation Sections
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Available Sections</h6>
                        <div class="border rounded p-3" style="max-height: 600px; overflow-y: auto;">
                            @foreach($availableSections as $section)
                                <div class="form-check mb-3">
                                    <input class="form-check-input section-checkbox" 
                                           type="checkbox" 
                                           value="{{ $section->Id }}" 
                                           id="section_{{ $section->Id }}"
                                           name="sections[]"
                                           {{ $assignedSections->contains('Id', $section->Id) ? 'checked' : '' }}
                                           onchange="toggleWeightInput({{ $section->Id }})">
                                    <label class="form-check-label w-100" for="section_{{ $section->Id }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <strong>{{ $section->SectionName }}</strong>
                                                <p class="text-muted small mb-1">
                                                    {{ $section->Description ?? 'No description' }}
                                                </p>
                                                <div class="small text-info">
                                                    <i class="fas fa-list"></i> 
                                                    {{ $section->criteria->count() }} criteria available
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Section Weights</h6>
                        <div class="border rounded p-3" style="max-height: 600px; overflow-y: auto;">
                            <div class="alert alert-info small mb-3">
                                <i class="fas fa-info-circle"></i> 
                                Section weights must sum to exactly <strong>100%</strong>. 
                                Each criteria within a section will be scored out of <strong>10 points</strong>.
                            </div>

                            @foreach($availableSections as $section)
                                <div class="mb-3 weight-input-group" 
                                     id="weight_group_{{ $section->Id }}"
                                     style="display: {{ $assignedSections->contains('Id', $section->Id) ? 'block' : 'none' }};">
                                    <label for="weight_{{ $section->Id }}" class="form-label">
                                        <strong>{{ $section->SectionName }}</strong> Weight (%)
                                    </label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control weight-input" 
                                               id="weight_{{ $section->Id }}"
                                               name="weights[{{ $section->Id }}]"
                                               value="{{ $sectionWeights->get($section->Id, '') }}"
                                               min="0" 
                                               max="100" 
                                               step="0.01"
                                               placeholder="0.00"
                                               oninput="updateWeightTotal()">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="small text-muted">
                                        Contains {{ $section->criteria->count() }} evaluation criteria
                                    </div>
                                </div>
                            @endforeach

                            <!-- Weight Total Display -->
                            <div class="mt-4 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Total Weight:</strong>
                                    <span id="total-weight" class="h5 mb-0">0.00%</span>
                                </div>
                                <div class="progress mt-2" style="height: 10px;">
                                    <div id="weight-progress" 
                                         class="progress-bar" 
                                         role="progressbar" 
                                         style="width: 0%"></div>
                                </div>
                                <div id="weight-status" class="small mt-1 text-center"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" onclick="selectAllSections()">
                            <i class="fas fa-check-double"></i> Select All Sections
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearAllSections()">
                            <i class="fas fa-times"></i> Clear All
                        </button>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-success" id="submit-btn">
                            <i class="fas fa-save"></i> Save Section Configuration
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Toggle weight input visibility based on section selection
function toggleWeightInput(sectionId) {
    const checkbox = document.getElementById(`section_${sectionId}`);
    const weightGroup = document.getElementById(`weight_group_${sectionId}`);
    const weightInput = document.getElementById(`weight_${sectionId}`);
    
    if (checkbox.checked) {
        weightGroup.style.display = 'block';
        weightInput.focus();
    } else {
        weightGroup.style.display = 'none';
        weightInput.value = '';
    }
    
    updateWeightTotal();
}

// Update total weight calculation
function updateWeightTotal() {
    const weightInputs = document.querySelectorAll('.weight-input');
    let total = 0;
    
    weightInputs.forEach(input => {
        const checkbox = document.querySelector(`input[name="sections[]"][value="${input.name.match(/\[(\d+)\]/)[1]}"]`);
        if (checkbox && checkbox.checked && input.value) {
            total += parseFloat(input.value) || 0;
        }
    });
    
    // Update display
    const totalDisplay = document.getElementById('total-weight');
    const progressBar = document.getElementById('weight-progress');
    const statusDisplay = document.getElementById('weight-status');
    const submitBtn = document.getElementById('submit-btn');
    
    totalDisplay.textContent = total.toFixed(2) + '%';
    progressBar.style.width = Math.min(total, 100) + '%';
    
    // Update progress bar color and status
    if (Math.abs(total - 100) < 0.01) {
        progressBar.className = 'progress-bar bg-success';
        statusDisplay.textContent = '✅ Perfect! Weights sum to 100%';
        statusDisplay.className = 'small mt-1 text-center text-success';
        submitBtn.disabled = false;
    } else if (total === 0) {
        progressBar.className = 'progress-bar bg-secondary';
        statusDisplay.textContent = '⚪ No sections selected';
        statusDisplay.className = 'small mt-1 text-center text-muted';
        submitBtn.disabled = true;
    } else if (total < 100) {
        progressBar.className = 'progress-bar bg-warning';
        statusDisplay.textContent = `⚠️ Need ${(100 - total).toFixed(2)}% more to reach 100%`;
        statusDisplay.className = 'small mt-1 text-center text-warning';
        submitBtn.disabled = true;
    } else {
        progressBar.className = 'progress-bar bg-danger';
        statusDisplay.textContent = `❌ Exceeds 100% by ${(total - 100).toFixed(2)}%`;
        statusDisplay.className = 'small mt-1 text-center text-danger';
        submitBtn.disabled = true;
    }
}

// Select all sections
function selectAllSections() {
    const checkboxes = document.querySelectorAll('.section-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        toggleWeightInput(checkbox.value);
    });
}

// Clear all selections
function clearAllSections() {
    const checkboxes = document.querySelectorAll('.section-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        toggleWeightInput(checkbox.value);
    });
}

// Auto-distribute weights evenly
function distributeWeightsEvenly() {
    const checkedBoxes = document.querySelectorAll('.section-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    const weightPerSection = 100 / checkedBoxes.length;
    
    checkedBoxes.forEach(checkbox => {
        const weightInput = document.getElementById(`weight_${checkbox.value}`);
        weightInput.value = weightPerSection.toFixed(2);
    });
    
    updateWeightTotal();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize weight total calculation
    updateWeightTotal();
    
    // Add auto-distribute button if needed
    if (document.querySelectorAll('.section-checkbox:checked').length > 0) {
        const actionsDiv = document.querySelector('.card-body .d-flex div:first-child');
        const distributeBtn = document.createElement('button');
        distributeBtn.type = 'button';
        distributeBtn.className = 'btn btn-outline-info ms-2';
        distributeBtn.innerHTML = '<i class="fas fa-balance-scale"></i> Distribute Evenly';
        distributeBtn.onclick = distributeWeightsEvenly;
        actionsDiv.appendChild(distributeBtn);
    }
});
</script>
@endsection
