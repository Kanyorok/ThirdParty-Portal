@php
$isEdit = isset($prequalificationRound) && $prequalificationRound->exists;
$readOnly = $readOnly ?? false;
$action = $isEdit
? ($readOnly ? '#' : route('prequalification.prequalification-rounds.update', $prequalificationRound))
: route('prequalification.prequalification-rounds.store');
$method = $isEdit ? 'PUT' : 'POST';
$currentStatus = old('Status', $prequalificationRound->Status->value ?? 'D');
@endphp

<form action="{{ $action }}" method="POST">
    @csrf
    @if($isEdit && !$readOnly)
    @method('PUT')
    @endif

    <div class="mb-3">
        <label for="Title" class="form-label">Title</label>
        <input type="text" name="Title" id="Title"
            value="{{ old('Title', $prequalificationRound->Title ?? '') }}"
            class="form-control @error('Title') is-invalid @enderror"
            {{ $readOnly ? 'readonly' : '' }}>
        @error('Title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="Description" class="form-label">Description</label>
        <textarea name="Description" id="Description" class="form-control" rows="3" {{ $readOnly ? 'readonly' : '' }}>{{ old('Description', $prequalificationRound->Description ?? '') }}</textarea>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <label for="StartDate" class="form-label">Start Date</label>
            <input type="date" name="StartDate" id="StartDate"
                value="{{ old('StartDate', optional($prequalificationRound->StartDate)->format('Y-m-d')) }}"
                class="form-control @error('StartDate') is-invalid @enderror"
                {{ $readOnly ? 'readonly' : '' }}>
            @error('StartDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label for="EndDate" class="form-label">End Date</label>
            <input type="date" name="EndDate" id="EndDate"
                value="{{ old('EndDate', optional($prequalificationRound->EndDate)->format('Y-m-d')) }}"
                class="form-control @error('EndDate') is-invalid @enderror"
                {{ $readOnly ? 'readonly' : '' }}>
            @error('EndDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label for="MaxVendors" class="form-label">Max Vendors</label>
            <input type="number" name="MaxVendors" id="MaxVendors"
                value="{{ old('MaxVendors', $prequalificationRound->MaxVendors ?? '') }}"
                min="1" class="form-control @error('MaxVendors') is-invalid @enderror"
                {{ $readOnly ? 'readonly' : '' }}>
            @error('MaxVendors') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label for="Status" class="form-label">Status</label>
            <select name="Status" id="Status" class="form-select @error('Status') is-invalid @enderror" {{ $readOnly ? 'disabled' : '' }}>
                <option value="D" @selected($currentStatus==='D' )>Draft Round</option>
                <option value="O" @selected($currentStatus==='O' )>Open for Application</option>
                <option value="CL" @selected($currentStatus==='CL' )>Closed - Applications not Allowed</option>
            </select>
            @if($readOnly)
            <input type="hidden" name="Status" value="{{ $currentStatus }}">
            @endif
            @error('Status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Total Section Weight</label>
        <div class="progress mb-1">
            <div id="totalWeightProgress" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
        </div>
        <small id="weightWarning" class="text-danger" style="display:none;">Total section weight must equal 100%</small>
    </div>

    <hr>

    <h3>Available Sections</h3>
    @error('sections')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror

    <div class="accordion" id="sectionsAccordion">
        @foreach($masterSections as $index => $section)
        @php
        $linkedSection = $isEdit ? $prequalificationRound->prequalificationSections->firstWhere('SectionId', $section->Id) : null;
        $linkedCriteria = $isEdit ? $prequalificationRound->prequalificationCriteria->where('SectionId', $section->Id) : collect();
        $isIncluded = (bool) old("sections.$index.included", $linkedSection ? 1 : 0);
        @endphp
        <div class="accordion-item mb-2">
            <h2 class="accordion-header" id="heading{{ $section->Id }}">
                <button class="accordion-button {{ $isIncluded ? '' : 'collapsed' }}" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapse{{ $section->Id }}"
                    aria-expanded="{{ $isIncluded ? 'true' : 'false' }}">
                    {{ $section->SectionName }}
                    <span class="ms-auto me-2 section-weight-display">({{ $linkedSection->Weight ?? 0 }}%)</span>
                </button>
            </h2>
            <div id="collapse{{ $section->Id }}" class="accordion-collapse collapse {{ $isIncluded ? 'show' : '' }}" aria-labelledby="heading{{ $section->Id }}" data-bs-parent="#sectionsAccordion">
                <div class="accordion-body">
                    @if(!$readOnly)
                    <input type="hidden" name="sections[{{ $index }}][section_id]" value="{{ $section->Id }}">
                    <div class="form-check mb-2">
                        <input type="hidden" name="sections[{{ $index }}][included]" value="0">
                        <input type="checkbox"
                            name="sections[{{ $index }}][included]"
                            value="1"
                            id="section_included_{{ $section->Id }}"
                            data-section-id="{{ $section->Id }}"
                            data-section-index="{{ $index }}"
                            {{ $isIncluded ? 'checked' : '' }}>
                        <label for="section_included_{{ $section->Id }}" class="mb-0">Include this section</label>
                    </div>

                    <div class="mb-2">
                        <label class="d-block">Section Weight</label>
                        <input type="number" name="sections[{{ $index }}][weight]"
                            value="{{ old("sections.$index.weight", $linkedSection->Weight ?? 0) }}"
                            class="form-control form-control-sm section-weight-input"
                            placeholder="Weight %"
                            min="0" max="100">
                    </div>
                    @else
                    <p><strong>Included:</strong> {{ $isIncluded ? 'Yes' : 'No' }}</p>
                    <p><strong>Section Weight:</strong> {{ $linkedSection->Weight ?? '-' }}%</p>
                    @endif

                    <hr>
                    <h4>Criteria</h4>
                    @if(!$readOnly)
                    <div class="criteria-list">
                        @foreach($section->criteria as $c_index => $criteria)
                        @php
                        $linkedCriterion = $linkedCriteria->firstWhere('CriteriaId', $criteria->Id);
                        $isCriterionIncluded = (bool) old("sections.$index.criteria.$c_index.included", $linkedCriterion ? 1 : 0);
                        @endphp
                        <div class="card mb-2">
                            <div class="card-body py-2">
                                <input type="hidden" name="sections[{{ $index }}][criteria][{{ $c_index }}][criteria_id]" value="{{ $criteria->Id }}">
                                <div class="form-check">
                                    <input type="hidden" name="sections[{{ $index }}][criteria][{{ $c_index }}][included]" value="0">
                                    <input type="checkbox"
                                        name="sections[{{ $index }}][criteria][{{ $c_index }}][included]"
                                        value="1"
                                        id="criteria_included_{{ $criteria->Id }}"
                                        data-section-id="{{ $section->Id }}"
                                        data-criterion-index="{{ $c_index }}"
                                        {{ $isCriterionIncluded ? 'checked' : '' }}>
                                    <label for="criteria_included_{{ $criteria->Id }}">{{ $criteria->CriteriaName }}</label>
                                </div>
                                <!-- Fixed per-criterion max score set by system: always 10 -->
                                <input type="hidden" name="sections[{{ $index }}][criteria][{{ $c_index }}][weight]" value="10">
                                <div class="text-muted small mt-1">Score for this criterion is fixed at 10.</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    @if($linkedCriteria->count())
                    <ul>
                        @foreach($linkedCriteria as $criterion)
                        <li>{{ $criterion->masterCriteria->CriteriaName }} (Weight: {{ $criterion->Weight }}%)</li>
                        @endforeach
                    </ul>
                    @else
                    <em>No criteria included for this section.</em>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(!$readOnly)
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            @if($isEdit)
            <i class="fas fa-save me-1"></i> Update Round
            @else
            <i class="fas fa-plus-circle me-1"></i> Create Round
            @endif
        </button>
    </div>
    @endif
</form>

@if(!$readOnly)
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const accordionItems = document.querySelectorAll('.accordion-item');
        const totalSectionWeightProgress = document.getElementById('totalWeightProgress');
        const sectionWeightWarning = document.getElementById('weightWarning');
        const submitBtn = document.querySelector('button[type="submit"]');

        function updateTotalWeights() {
            let totalSectionWeight = 0;
            let hasIncludedSections = false;

            accordionItems.forEach(item => {
                const sectionCheckbox = item.querySelector('input[id^="section_included_"]');
                const sectionIncluded = sectionCheckbox ? sectionCheckbox.checked : false;
                const sectionWeightInput = item.querySelector('.section-weight-input');
                const sectionWeight = parseInt(sectionWeightInput?.value) || 0;
                const sectionWeightDisplay = item.querySelector('.section-weight-display');

                if (sectionWeightDisplay) {
                    sectionWeightDisplay.textContent = `(${sectionIncluded ? sectionWeight : 0}%)`;
                }

                if (sectionIncluded) {
                    hasIncludedSections = true;
                    totalSectionWeight += sectionWeight;
                }
            });

            const progressPercentage = Math.min(totalSectionWeight, 100);
            totalSectionWeightProgress.style.width = progressPercentage + '%';
            totalSectionWeightProgress.textContent = totalSectionWeight + '%';

            const isSectionWeightValid = hasIncludedSections && totalSectionWeight === 100;

            if (sectionWeightWarning) {
                if (!hasIncludedSections) {
                    sectionWeightWarning.style.display = 'block';
                    sectionWeightWarning.textContent = 'Please select at least one section.';
                } else if (totalSectionWeight !== 100) {
                    sectionWeightWarning.style.display = 'block';
                    sectionWeightWarning.textContent = `Total section weight must equal 100%. Current total: ${totalSectionWeight}%`;
                } else {
                    sectionWeightWarning.style.display = 'none';
                }
            }

            if (submitBtn) {
                submitBtn.disabled = !isSectionWeightValid;
            }
        }

        document.querySelectorAll('input[id^="section_included_"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const sectionId = this.dataset.sectionId;
                const collapseEl = document.getElementById('collapse' + sectionId);

                if (collapseEl) {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl);

                    if (this.checked) {
                        bsCollapse.show();
                    } else {
                        bsCollapse.hide();
                        const sectionWeightInput = this.closest('.accordion-item').querySelector('.section-weight-input');
                        if (sectionWeightInput) {
                            sectionWeightInput.value = 0;
                        }
                        const criteriaCheckboxes = collapseEl.querySelectorAll('input[type=checkbox]');
                        const criteriaWeightContainers = collapseEl.querySelectorAll('.criteria-weight-container');
                        const criteriaWeightInputs = collapseEl.querySelectorAll('.criteria-weight-input');
                        criteriaCheckboxes.forEach(cb => cb.checked = false);
                        criteriaWeightContainers.forEach(container => container.style.display = 'none');
                        criteriaWeightInputs.forEach(input => input.value = 0);
                    }
                }
                updateTotalWeights();
            });
        });

        document.querySelectorAll('.section-weight-input').forEach(input => {
            input.addEventListener('input', function() {
                const value = parseInt(this.value) || 0;
                if (value < 0) this.value = 0;
                if (value > 100) this.value = 100;
                updateTotalWeights();
            });
        });

    // No criteria score inputs: score is fixed at 10 by the system.

        updateTotalWeights();
    });
</script>
@endpush
@endif