@extends('layouts.app')

@section('title', 'New KPI Appraisal')

@section('content')
<div class="container-fluid py-4">
    <style>
        .actual-input {
            min-width: 120px;
        }
        .score-input {
            min-width: 120px;
        }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create KPI Appraisal</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.kpi.appraisals.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" class="card shadow-sm mb-3">
        <div class="card-body">
            <label class="form-label">Filter Approved Goal Set</label>
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Period</label>
                    <select name="period_id" id="periodSelect" class="form-select">
                        <option value="">Select</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->Id }}" @selected((string)$periodId === (string)$period->Id)>
                                {{ $period->Name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Year</label>
                    <select name="year" class="form-select">
                        <option value="">Select</option>
                        @foreach($yearOptions as $yr)
                            <option value="{{ $yr }}" @selected((string)$year === (string)$yr)>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Segment</label>
                    <select name="segment" id="segmentSelect" class="form-select" data-selected="{{ $segment }}">
                        <option value="">Select Period First</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Approved Goal Set</label>
                    <select name="goal_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($goals as $g)
                            <option value="{{ $g->Id }}" @selected(request('goal_id') == $g->Id)>
                                {{ $g->employee?->FirstName }} {{ $g->employee?->LastName }} - {{ $g->period?->Name }} {{ $g->PeriodYear }}@if($g->PeriodSegment) ({{ $g->PeriodSegment }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-outline-primary w-100">Load</button>
                </div>
            </div>
        </div>
    </form>

    @if($goal)
        <form method="POST" action="{{ route('hr.kpi.appraisals.store') }}">
            @csrf
            <input type="hidden" name="GoalID" value="{{ $goal->Id }}">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Employee</label>
                            <input type="text" class="form-control" value="{{ $goal->employee?->FirstName }} {{ $goal->employee?->LastName }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Period</label>
                            <input type="text" class="form-control" value="{{ $goal->period?->Name }} {{ $goal->PeriodYear }}@if($goal->PeriodSegment) ({{ $goal->PeriodSegment }})@endif" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Overall Rating *</label>
                            <select name="OverallRatingID" class="form-select" required>
                                <option value="">Select</option>
                                @foreach($scales as $scale)
                                    <option value="{{ $scale->Id }}">{{ $scale->Name }} ({{ $scale->MinScore }} - {{ $scale->MaxScore }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Comments</label>
                            <textarea name="Comments" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Appraisal Items</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>KPI</th>
                                <th class="text-end">Annual Target</th>
                                <th class="text-end">Period Target</th>
                                <th class="text-end">Actual</th>
                                <th class="text-end">% Ach</th>
                                <th class="text-end">Weight</th>
                                <th>Self Rating</th>
                                <th class="text-end">Self Score</th>
                                <th>Supervisor Rating</th>
                                <th class="text-end">Final Score</th>
                                <th>Appraisee Comments</th>
                                <th>Appraiser Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowIndex = 0; @endphp
                            @forelse($perspectiveGroups as $perspectiveId => $groupItems)
                                @php
                                    $perspectiveName = $perspectiveId && $perspectives->has($perspectiveId)
                                        ? $perspectives[$perspectiveId]->Name
                                        : 'Unassigned';
                                @endphp
                                <tr class="table-light">
                                    <td colspan="12"><strong>{{ $perspectiveName }}</strong></td>
                                </tr>
                                @foreach($groupItems as $item)
                                    @php
                                        $periodTarget = $item->PeriodTarget ?? $item->TargetValue;
                                        $percent = ($periodTarget && $item->ActualValue !== null)
                                            ? ((float)$item->ActualValue / (float)$periodTarget) * 100
                                            : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $item->kpiItem?->Name }}
                                            <input type="hidden" name="Items[{{ $rowIndex }}][GoalItemID]" value="{{ $item->Id }}">
                                        </td>
                                        <td class="text-end">{{ $item->AnnualTarget !== null ? number_format((float)$item->AnnualTarget, 2) : '-' }}</td>
                                        <td class="text-end">{{ $periodTarget !== null ? number_format((float)$periodTarget, 2) : '-' }}</td>
                                        <td>
                                            <input type="number" step="0.01" name="Items[{{ $rowIndex }}][ActualValue]" class="form-control actual-input"
                                                   data-period-target="{{ $periodTarget ?? '' }}">
                                        </td>
                                        <td class="text-end percent-cell">{{ $percent !== null ? number_format((float)$percent, 2) . '%' : '-' }}</td>
                                        <td class="text-end weight-cell" data-weight="{{ (float)$item->Weight }}">{{ number_format((float)$item->Weight, 2) }}</td>
                                        <td>
                                            <input type="number" step="1" name="Items[{{ $rowIndex }}][SelfRatingValue]" class="form-control rating-input self-rating">
                                        </td>
                                        <td><input type="text" class="form-control score-input self-score" readonly></td>
                                        <td>
                                            <input type="number" step="1" name="Items[{{ $rowIndex }}][SupervisorRatingValue]" class="form-control rating-input supervisor-rating" disabled>
                                        </td>
                                        <td><input type="text" class="form-control score-input final-score" readonly></td>
                                        <td><input type="text" name="Items[{{ $rowIndex }}][AppraiseeComments]" class="form-control"></td>
                                        <td><input type="text" name="Items[{{ $rowIndex }}][AppraiserComments]" class="form-control"></td>
                                    </tr>
                                    @php $rowIndex++; @endphp
                                @endforeach
                            @empty
                                <tr><td colspan="12" class="text-center text-muted py-3">No goal items available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="submit" name="Action" value="draft" class="btn btn-outline-secondary">Save Draft</button>
                <button type="submit" name="Action" value="submit" class="btn btn-primary">Submit for Approval</button>
            </div>
        </form>
    @endif
</div>

<script>
    (function () {
        const ratingScales = @json($ratingScalePayload);
        const periods = @json($periodsPayload);
        const periodSelect = document.getElementById('periodSelect');
        const segmentSelect = document.getElementById('segmentSelect');
        const overallRatingSelect = document.querySelector('select[name="OverallRatingID"]');

        function buildSegmentLabel(count, index) {
            if (count === 4) {
                return `Q${index}`;
            }
            if (count === 2) {
                return `H${index}`;
            }
            if (count === 12) {
                return `M${index}`;
            }
            if (count === 1) {
                return 'Full Year';
            }
            return `Segment ${index}`;
        }

        function updateSegmentOptions() {
            const selectedPeriod = periodSelect?.value ? parseInt(periodSelect.value, 10) : null;
            const selectedValue = segmentSelect?.dataset?.selected ? parseInt(segmentSelect.dataset.selected, 10) : null;
            segmentSelect.innerHTML = '';
            if (!selectedPeriod) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Select Period First';
                segmentSelect.appendChild(option);
                return;
            }
            const period = periods.find(p => p.Id === selectedPeriod);
            const count = period?.SegmentCount ?? 1;
            for (let i = 1; i <= count; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = buildSegmentLabel(count, i);
                if (selectedValue && i === selectedValue) {
                    option.selected = true;
                }
                segmentSelect.appendChild(option);
            }
            if (!segmentSelect.value && count >= 1) {
                segmentSelect.value = '1';
            }
        }

        function getSelectedScale() {
            const selected = overallRatingSelect?.value;
            if (!selected) {
                return null;
            }
            const id = parseInt(selected, 10);
            return ratingScales.find(scale => scale.Id === id) || null;
        }

        function normalizeWeight(weight) {
            if (!Number.isFinite(weight)) {
                return 0;
            }
            return weight > 1 ? weight / 100 : weight;
        }

        function updateRatingBounds() {
            const scale = getSelectedScale();
            const min = scale?.Min ?? 1;
            const max = scale?.Max ?? 5;
            document.querySelectorAll('.rating-input').forEach(input => {
                input.min = min;
                input.max = max;
                input.placeholder = `${min} - ${max}`;
            });
        }

        function updatePercentForRow(row) {
            const actualInput = row.querySelector('.actual-input');
            const percentCell = row.querySelector('.percent-cell');
            if (!actualInput || !percentCell) {
                return;
            }
            const target = parseFloat(actualInput.dataset.periodTarget);
            const actual = parseFloat(actualInput.value);
            if (!Number.isFinite(target) || target === 0 || !Number.isFinite(actual)) {
                percentCell.textContent = '-';
                return;
            }
            const percent = (actual / target) * 100;
            percentCell.textContent = `${percent.toFixed(2)}%`;
        }

        function updateScoresForRow(row) {
            const weightCell = row.querySelector('.weight-cell');
            const weightValue = weightCell ? parseFloat(weightCell.dataset.weight) : 0;
            const normalizedWeight = normalizeWeight(weightValue);
            const selfRating = parseFloat(row.querySelector('.self-rating')?.value);
            const supervisorRating = parseFloat(row.querySelector('.supervisor-rating')?.value);
            const selfScoreInput = row.querySelector('.self-score');
            const finalScoreInput = row.querySelector('.final-score');
            if (selfScoreInput) {
                selfScoreInput.value = Number.isFinite(selfRating)
                    ? (normalizedWeight * selfRating).toFixed(2)
                    : '';
            }
            if (finalScoreInput) {
                finalScoreInput.value = Number.isFinite(supervisorRating)
                    ? (normalizedWeight * supervisorRating).toFixed(2)
                    : '';
            }
        }

        function bindRowEvents(row) {
            const actualInput = row.querySelector('.actual-input');
            const selfRating = row.querySelector('.self-rating');
            const supervisorRating = row.querySelector('.supervisor-rating');
            actualInput?.addEventListener('input', () => updatePercentForRow(row));
            selfRating?.addEventListener('input', () => updateScoresForRow(row));
            supervisorRating?.addEventListener('input', () => updateScoresForRow(row));
            updatePercentForRow(row);
            updateScoresForRow(row);
        }

        updateSegmentOptions();
        periodSelect?.addEventListener('change', () => {
            segmentSelect.dataset.selected = '';
            updateSegmentOptions();
        });

        overallRatingSelect?.addEventListener('change', () => {
            updateRatingBounds();
        });
        updateRatingBounds();
        document.querySelectorAll('tbody tr').forEach(bindRowEvents);
    })();
</script>
@endsection
