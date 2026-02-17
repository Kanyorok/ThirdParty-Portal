@extends('layouts.app')

@section('title', 'KPI Appraisals')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">KPI Appraisals</h2>
        <a class="btn btn-primary" href="{{ route('hr.kpi.appraisals.create') }}">+ New Appraisal</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->Id }}" @selected(request('employee_id') == $emp->Id)>
                                {{ $emp->FirstName }} {{ $emp->LastName }} ({{ $emp->EmployeeNo }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="">All</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['Draft','Submitted','Approved','Rejected'] as $st)
                            <option value="{{ $st }}" @selected(request('status') == $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Period</th>
                        <th>Year</th>
                        <th>Segment</th>
                        <th>Status</th>
                        <th class="text-end">Total Score</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appraisals as $row)
                        @php
                            $period = $row->period;
                            $segment = $row->goal?->PeriodSegment;
                            $start = (int)($period?->StartMonth ?? 1);
                            $end = (int)($period?->EndMonth ?? 12);
                            $length = $end - $start + 1;
                            $segmentCount = ($length > 0 && 12 % $length === 0) ? (int)(12 / $length) : 1;
                            $segmentLabel = $segment
                                ? ($segmentCount === 4 ? "Q{$segment}"
                                    : ($segmentCount === 2 ? "H{$segment}"
                                        : ($segmentCount === 12 ? "M{$segment}"
                                            : ($segmentCount === 1 ? 'Full Year' : "Segment {$segment}"))))
                                : '-';
                            $overallMax = $ratingScaleMap[$row->OverallRatingID] ?? null;
                            $rawTotalScore = $row->items->sum(fn($item) => (float)($item->FinalScore ?? $item->Score));
                            $totalScorePercent = $overallMax ? ($rawTotalScore / (float)$overallMax) * 100 : (float)$row->TotalScore;
                        @endphp
                        <tr>
                            <td>{{ $row->employee?->FirstName }} {{ $row->employee?->LastName }}</td>
                            <td>{{ $row->period?->Name }}</td>
                            <td>{{ $row->goal?->PeriodYear ?? '-' }}</td>
                            <td>{{ $segmentLabel }}</td>
                            <td>{{ $row->Status }}</td>
                            <td class="text-end">{{ number_format((float)$totalScorePercent, 2) }}%</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.kpi.appraisals.show', $row->Id) }}">View</a>
                                @if(in_array($row->Status, ['Draft','Returned','Rejected'], true))
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.kpi.appraisals.edit', $row->Id) }}">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No appraisals found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {{ $appraisals->links() }}
    </div>
</div>
@endsection
