@extends('layouts.app')

@section('title', 'Edit KPI Appraisal')

@section('content')
<div class="container-fluid py-4">
    @php
        $isSupervisorStage = $appraisal->Status === 'Submitted';
        $isEmployeeStage = in_array($appraisal->Status, ['Draft','Returned','Rejected'], true);
    @endphp
    <style>
        .actual-input {
            min-width: 120px;
        }
        .score-input {
            min-width: 120px;
        }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit KPI Appraisal</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.kpi.appraisals.show', $appraisal->Id) }}">Back</a>
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

    <form method="POST" action="{{ route('hr.kpi.appraisals.update', $appraisal->Id) }}">
        @csrf
        @method('PUT')
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" value="{{ $appraisal->employee?->FirstName }} {{ $appraisal->employee?->LastName }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Period</label>
                        <input type="text" class="form-control" value="{{ $appraisal->period?->Name }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Overall Rating *</label>
                        <select name="OverallRatingID" class="form-select" @disabled($isSupervisorStage)>
                            <option value="">Select</option>
                            @foreach($scales as $scale)
                                <option value="{{ $scale->Id }}" @selected(old('OverallRatingID', $appraisal->OverallRatingID) == $scale->Id)>
                                    {{ $scale->Name }} ({{ $scale->MinScore }} - {{ $scale->MaxScore }})
                                </option>
                            @endforeach
                        </select>
                        @if($isSupervisorStage)
                            <input type="hidden" name="OverallRatingID" value="{{ $appraisal->OverallRatingID }}">
                        @endif
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Comments</label>
                        <textarea name="Comments" rows="2" class="form-control">{{ old('Comments', $appraisal->Comments) }}</textarea>
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
                                    $periodTarget = $item->goalItem?->PeriodTarget ?? $item->goalItem?->TargetValue;
                                    $percent = ($periodTarget && $item->ActualValue !== null)
                                        ? ((float)$item->ActualValue / (float)$periodTarget) * 100
                                        : null;
                                    $finalScore = $item->FinalScore ?? $item->Score;
                                    $supervisorScale = $item->SupervisorRatingScaleID ?? $item->RatingScaleID;
                                @endphp
                                <tr>
                                    <td>
                                        {{ $item->goalItem?->kpiItem?->Name }}
                                        <input type="hidden" name="Items[{{ $rowIndex }}][GoalItemID]" value="{{ $item->GoalItemID }}">
                                    </td>
                                    <td class="text-end">{{ $item->goalItem?->AnnualTarget !== null ? number_format((float)$item->goalItem->AnnualTarget, 2) : '-' }}</td>
                                    <td class="text-end">{{ $periodTarget !== null ? number_format((float)$periodTarget, 2) : '-' }}</td>
                                    <td>
                                        <input type="number" step="0.01" name="Items[{{ $rowIndex }}][ActualValue]" class="form-control actual-input"
                                               value="{{ $item->ActualValue }}" data-period-target="{{ $periodTarget ?? '' }}">
                                    </td>
                                    <td class="text-end percent-cell">{{ $percent !== null ? number_format((float)$percent, 2) . '%' : '-' }}</td>
                                    <td class="text-end weight-cell" data-weight="{{ (float)$item->goalItem?->Weight }}">{{ number_format((float)$item->goalItem?->Weight, 2) }}</td>
                                    <td>
                                        <input type="number" step="1" name="Items[{{ $rowIndex }}][SelfRatingValue]" class="form-control rating-input self-rating"
                                               value="{{ $item->SelfRatingValue }}" @disabled(!$isEmployeeStage)>
                                        @if(!$isEmployeeStage)
                                            <input type="hidden" name="Items[{{ $rowIndex }}][SelfRatingValue]" value="{{ $item->SelfRatingValue }}">
                                        @endif
                                    </td>
                                    <td><input type="text" class="form-control score-input self-score" value="{{ $item->SelfScore }}" readonly></td>
                                    <td>
                                        <input type="number" step="1" name="Items[{{ $rowIndex }}][SupervisorRatingValue]" class="form-control rating-input supervisor-rating"
                                               value="{{ $item->SupervisorRatingValue }}" @disabled(!$isSupervisorStage)>
                                        @if(!$isSupervisorStage)
                                            <input type="hidden" name="Items[{{ $rowIndex }}][SupervisorRatingValue]" value="{{ $item->SupervisorRatingValue }}">
                                        @endif
                                    </td>
                                    <td><input type="text" class="form-control score-input final-score" value="{{ $finalScore }}" readonly></td>
                                    <td><input type="text" name="Items[{{ $rowIndex }}][AppraiseeComments]" class="form-control" value="{{ $item->AppraiseeComments }}"></td>
                                    <td><input type="text" name="Items[{{ $rowIndex }}][AppraiserComments]" class="form-control" value="{{ $item->AppraiserComments ?? $item->Comments }}"></td>
                                </tr>
                                @php $rowIndex++; @endphp
                            @endforeach
                        @empty
                            <tr><td colspan="12" class="text-center text-muted py-3">No appraisal items.</td></tr>
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
</div>

<script>
    (function () {
        const ratingScales = @json($ratingScalePayload);
        const overallRatingSelect = document.querySelector('select[name="OverallRatingID"]');

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

        overallRatingSelect?.addEventListener('change', () => {
            updateRatingBounds();
        });
        updateRatingBounds();
        document.querySelectorAll('tbody tr').forEach(bindRowEvents);
    })();
</script>
@endsection
