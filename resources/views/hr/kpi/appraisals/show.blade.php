@extends('layouts.app')

@section('title', 'KPI Appraisal')

@section('content')
<div class="container-fluid py-4">
    @php
        $employee = $appraisal->employee;
        $supervisor = $employee?->supervisor;
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">KPI Appraisal</h2>
            <div class="text-muted">{{ $appraisal->employee?->FirstName }} {{ $appraisal->employee?->LastName }} • {{ $appraisal->period?->Name }}</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('hr.kpi.appraisals.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap gap-4">
            <div><strong>Status:</strong> {{ $appraisal->Status }}</div>
            <div><strong>Total Score:</strong> {{ number_format((float)$totalScorePercent, 2) }}%</div>
            <div><strong>Overall Rating:</strong> {{ $scales->firstWhere('Id', $appraisal->OverallRatingID)?->Name ?? '-' }}</div>
            <div>
                <strong>Final Rating:</strong>
                {{ $finalRating ? (number_format((float)$finalRating->RatingValue, 2) . ' ' . $finalRating->RatingLabel) : '-' }}
            </div>
            <div><strong>Submitted:</strong> {{ $appraisal->SubmittedOn ? $appraisal->SubmittedOn->format('Y-m-d') : '-' }}</div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0">Employee Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div><strong>Name:</strong> {{ $employee?->FirstName }} {{ $employee?->LastName }}</div>
                    <div><strong>Department:</strong> {{ $employee?->department?->Name ?? '-' }}</div>
                    <div><strong>Position:</strong> {{ $employee?->role?->Name ?? '-' }}</div>
                    <div><strong>Location/Branch:</strong> {{ $employee?->branch?->Name ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Supervisor:</strong> {{ $supervisor ? $supervisor->FirstName . ' ' . $supervisor->LastName : '-' }}</div>
                    <div><strong>Position:</strong> {{ $supervisor?->role?->Name ?? '-' }}</div>
                    <div><strong>Department:</strong> {{ $supervisor?->department?->Name ?? '-' }}</div>
                    <div><strong>Location/Branch:</strong> {{ $supervisor?->branch?->Name ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($perspectiveGroups->isNotEmpty())
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Perspective Summary</h5>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Perspective</th>
                            <th class="text-end">Configured Weight</th>
                            <th class="text-end">Score Total</th>
                            <th class="text-end">Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($perspectiveGroups as $perspectiveId => $items)
                            @php
                                $perspectiveName = $perspectiveId && $perspectives->has($perspectiveId)
                                    ? $perspectives[$perspectiveId]->Name
                                    : 'Unassigned';
                                $configWeight = $weightMap->get($perspectiveId)?->Weight;
                                $configWeightPercent = $configWeight !== null
                                    ? ((float)$configWeight <= 1 ? (float)$configWeight * 100 : (float)$configWeight)
                                    : null;
                                $scoreTotal = $items->sum(fn($item) => (float)($item->FinalScore ?? $item->Score));
                                $scorePercent = $overallMax ? ($scoreTotal / $overallMax) * 100 : $scoreTotal;
                            @endphp
                            <tr>
                                <td>{{ $perspectiveName }}</td>
                                <td class="text-end">{{ $configWeightPercent !== null ? number_format((float)$configWeightPercent, 2) . '%' : '-' }}</td>
                                <td class="text-end">{{ number_format((float)$scorePercent, 2) }}%</td>
                                <td class="text-end">{{ $items->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
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
                    @forelse($perspectiveGroups as $perspectiveId => $items)
                        @php
                            $perspectiveName = $perspectiveId && $perspectives->has($perspectiveId)
                                ? $perspectives[$perspectiveId]->Name
                                : 'Unassigned';
                        @endphp
                        <tr class="table-light">
                            <td colspan="12"><strong>{{ $perspectiveName }}</strong></td>
                        </tr>
                        @foreach($items as $item)
                            @php
                                $periodTarget = $item->goalItem?->PeriodTarget ?? $item->goalItem?->TargetValue;
                                $percent = ($periodTarget && $item->ActualValue !== null)
                                    ? ((float)$item->ActualValue / (float)$periodTarget) * 100
                                    : null;
                                $finalScore = $item->FinalScore ?? $item->Score;
                            @endphp
                            <tr>
                                <td>{{ $item->goalItem?->kpiItem?->Name }}</td>
                                <td class="text-end">{{ $item->goalItem?->AnnualTarget !== null ? number_format((float)$item->goalItem->AnnualTarget, 2) : '-' }}</td>
                                <td class="text-end">{{ $periodTarget !== null ? number_format((float)$periodTarget, 2) : '-' }}</td>
                                <td class="text-end">{{ $item->ActualValue !== null ? number_format((float)$item->ActualValue, 2) : '-' }}</td>
                                <td class="text-end">{{ $percent !== null ? number_format((float)$percent, 2) . '%' : '-' }}</td>
                                <td class="text-end">{{ number_format((float)$item->goalItem?->Weight, 2) }}</td>
                                <td>{{ $item->SelfRatingValue !== null ? number_format((float)$item->SelfRatingValue, 2) : '-' }}</td>
                                <td class="text-end">{{ $item->SelfScore !== null ? number_format((float)$item->SelfScore, 2) : '-' }}</td>
                                <td>{{ $item->SupervisorRatingValue !== null ? number_format((float)$item->SupervisorRatingValue, 2) : '-' }}</td>
                                <td class="text-end">{{ $finalScore !== null ? number_format((float)$finalScore, 2) : '-' }}</td>
                                <td>{{ $item->AppraiseeComments }}</td>
                                <td>{{ $item->AppraiserComments ?? $item->Comments }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="12" class="text-center text-muted py-3">No appraisal items.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        @if(in_array($appraisal->Status, ['Draft','Returned','Rejected'], true))
            <a class="btn btn-outline-secondary" href="{{ route('hr.kpi.appraisals.edit', $appraisal->Id) }}">Edit</a>
            <form method="POST" action="{{ route('hr.kpi.appraisals.submit', $appraisal->Id) }}">
                @csrf
                <button class="btn btn-primary" type="submit">Submit</button>
            </form>
        @endif
        @if($appraisal->Status === 'Submitted')
            <a class="btn btn-outline-secondary" href="{{ route('hr.kpi.appraisals.edit', $appraisal->Id) }}">Appraise</a>
        @endif
        @if($appraisal->Status === 'SupervisorSubmitted')
            <form method="POST" action="{{ route('hr.kpi.appraisals.approve', $appraisal->Id) }}">
                @csrf
                <button class="btn btn-success" type="submit">Approve</button>
            </form>
            <form method="POST" action="{{ route('hr.kpi.appraisals.reject', $appraisal->Id) }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="RejectionReason" class="form-control form-control-sm" placeholder="Reject reason">
                <button class="btn btn-outline-danger" type="submit">Reject</button>
            </form>
        @endif
    </div>
</div>
@endsection
