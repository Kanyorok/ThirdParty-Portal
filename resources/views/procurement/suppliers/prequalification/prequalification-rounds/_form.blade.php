@php
$isEdit = isset($prequalificationRound) && $prequalificationRound->exists;
$readOnly = $readOnly ?? false;
$action = $isEdit
? ($readOnly ? '#' : route('prequalification.prequalification-rounds.update', $prequalificationRound))
: route('prequalification.prequalification-rounds.store');
$method = $isEdit ? 'PUT' : 'POST';
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
        <textarea name="Description" id="Description" class="form-control" {{ $readOnly ? 'readonly' : '' }}>{{ old('Description', $prequalificationRound->Description ?? '') }}</textarea>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="StartDate" class="form-label">Start Date</label>
            <input type="date" name="StartDate" id="StartDate"
                value="{{ old('StartDate', isset($prequalificationRound->StartDate) ? $prequalificationRound->StartDate->format('Y-m-d') : '') }}"
                class="form-control @error('StartDate') is-invalid @enderror"
                {{ $readOnly ? 'readonly' : '' }}>
            @error('StartDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="EndDate" class="form-label">End Date</label>
            <input type="date" name="EndDate" id="EndDate"
                value="{{ old('EndDate', isset($prequalificationRound->EndDate) ? $prequalificationRound->EndDate->format('Y-m-d') : '') }}"
                class="form-control @error('EndDate') is-invalid @enderror"
                {{ $readOnly ? 'readonly' : '' }}>
            @error('EndDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="MaxVendors" class="form-label">Max Vendors</label>
            <input type="number" name="MaxVendors" id="MaxVendors"
                value="{{ old('MaxVendors', $prequalificationRound->MaxVendors ?? '') }}"
                min="1" class="form-control @error('MaxVendors') is-invalid @enderror"
                {{ $readOnly ? 'readonly' : '' }}>
            @error('MaxVendors') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <hr>

    <h3>Available Sections</h3>
    <div class="accordion" id="sectionsAccordion">
        @foreach($masterSections as $index => $section)
        @php
        $linkedSection = $isEdit
        ? $prequalificationRound->prequalificationSections->firstWhere('SectionId', $section->id)
        : null;
        $linkedCriteriaIds = $isEdit
        ? $prequalificationRound->prequalificationCriteria->where('SectionId', $section->id)->pluck('CriteriaId')->toArray()
        : [];
        $isIncluded = (bool) old("sections.$index.included", $linkedSection ? true : false);
        @endphp

        <div class="accordion-item mb-2">
            <h2 class="accordion-header" id="heading{{ $section->id }}">
                <button class="accordion-button {{ $readOnly || $isIncluded ? '' : 'collapsed' }}" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapse{{ $section->id }}"
                    aria-expanded="{{ $readOnly || $isIncluded ? 'true' : 'false' }}">
                    {{ $section->SectionName }} (Weight: {{ $linkedSection->Weight ?? 0 }}%)
                </button>
            </h2>
            <div id="collapse{{ $section->id }}" class="accordion-collapse collapse {{ $readOnly || $isIncluded ? 'show' : '' }}" aria-labelledby="heading{{ $section->id }}" data-bs-parent="#sectionsAccordion">
                <div class="accordion-body">
                    @if(!$readOnly)
                    <!-- Hidden input to ensure unchecked checkbox still posts 0 -->
                    <input type="hidden" name="sections[{{ $index }}][included]" value="0">
                    <div class="form-check mb-2">
                        <input type="checkbox"
                            name="sections[{{ $index }}][included]"
                            value="1"
                            id="section_included_{{ $section->id }}"
                            data-section-id="{{ $section->id }}"
                            {{ $isIncluded ? 'checked' : '' }}>
                        <label for="section_included_{{ $section->id }}" class="mb-0">Include this section</label>
                    </div>
                    <input type="hidden" name="sections[{{ $index }}][section_id]" value="{{ $section->id }}">
                    <div class="mb-2">
                        <label>Weight</label>
                        <input type="number" name="sections[{{ $index }}][weight]"
                            value="{{ old("sections.$index.weight", $linkedSection->Weight ?? 0) }}"
                            class="form-control form-control-sm"
                            placeholder="Weight %"
                            min="0" max="100">
                    </div>
                    @else
                    <p><strong>Included:</strong> {{ $isIncluded ? 'Yes' : 'No' }}</p>
                    <p><strong>Weight:</strong> {{ $linkedSection->Weight ?? '-' }}%</p>
                    @endif

                    <div id="criteria_container_{{ $section->id }}">
                        @if($readOnly)
                        @if($section->criteria->count())
                        <ul>
                            @foreach($section->criteria as $criteria)
                            @if(in_array($criteria->id, $linkedCriteriaIds))
                            <li>{{ $criteria->CriteriaName }}</li>
                            @endif
                            @endforeach
                        </ul>
                        @else
                        <em>No criteria defined for this section.</em>
                        @endif
                        @else
                        <p><strong>Criteria:</strong></p>
                        <div class="criteria-list" data-section-id="{{ $section->id }}">
                            @foreach($section->criteria as $criteria)
                            <div class="form-check">
                                <input type="checkbox"
                                    name="sections[{{ $index }}][criteria_ids][]"
                                    value="{{ $criteria->CriteriaID }}"
                                    id="criteria_{{ $section->id }}_{{ $criteria->CriteriaID }}"
                                    {{ in_array($criteria->CriteriaID, old("sections.$index.criteria_ids", $linkedCriteriaIds)) ? 'checked' : '' }}>
                                <label for="criteria_{{ $section->id }}_{{ $criteria->CriteriaID }}">
                                    {{ $criteria->CriteriaName }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(!$readOnly)
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Round' : 'Create Round' }}</button>
    </div>
    @endif
</form>

@if(!$readOnly)
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[type=checkbox][id^="section_included_"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var sectionId = this.dataset.sectionId;
                var collapseEl = document.getElementById('collapse' + sectionId);
                var bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl);

                if (this.checked) {
                    bsCollapse.show();
                    fetchCriteria(sectionId);
                } else {
                    bsCollapse.hide();
                    document.querySelectorAll('#criteria_container_' + sectionId + ' input[type=checkbox]').forEach(cb => cb.checked = false);
                }
            });
        });

        function fetchCriteria(sectionId) {
            let url = "{{ route('sections.criteria.fetch', ':id') }}".replace(':id', sectionId);
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    let container = document.querySelector('#criteria_container_' + sectionId + ' .criteria-list');
                    if (!container) return;
                    container.innerHTML = '';
                    data.forEach(criteria => {
                        container.innerHTML += `
                                    <div class="form-check">
        <input type="checkbox" name="sections[][criteria_ids][]" value="${criteria.CriteriaId}" id="criteria_${sectionId}_${criteria.CriteriaId}">
        <label for="criteria_${sectionId}_${criteria.CriteriaId}">${criteria.CriteriaName}</label>
    </div>`;
                    });
                });
        }
    });
</script>
@endpush
@endif