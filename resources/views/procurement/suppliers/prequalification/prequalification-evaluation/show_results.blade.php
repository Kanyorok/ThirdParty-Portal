@extends('layouts.app')

@section('title', 'Prequalification Results')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-primary">Prequalification Results for: App-{{ $application->applicationNo }}</h4>
        <a href="{{ route('prequalification.applications.show', $application->ApplicationID) }}" class="btn btn-light" hx-get="{{ route('prequalification.applications.show', $application->ApplicationID) }}" hx-target="#mainBodyContent" hx-swap="innerHTML" hx-push-url="true">
            <i class="fas fa-arrow-circle-left me-2"></i>Back to Application
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-8">
                <p class="mb-1"><strong>Supplier:</strong> {{ $application->supplier->thirdParty->ThirdPartyName ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Total (Computed):</strong> {{ number_format($grandTotal ?? $result->TotalScore, 2) }}%</p>
                <p class="mb-1"><strong>Stored Total:</strong> {{ number_format($result->TotalScore, 2) }}%</p>
                <p class="mb-1"><strong>Decision:</strong>
                    @if ($result->Decision === 'Passed')
                        <span class="badge bg-success">Passed</span>
                    @else
                        <span class="badge bg-danger">Failed</span>
                    @endif
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="h5 mb-0">Overall Score: <span class="text-primary">{{ number_format($grandTotal ?? $result->TotalScore, 2) }}%</span></div>
            </div>
        </div>
        <hr>

        @foreach ($sections as $section)
            <div class="mb-4">
                <h5 class="text-secondary mb-1">{{ $section['name'] }}
                    <small class="text-muted ms-2">(Section Weight: {{ number_format($section['sectionWeight'],2) }}% | Achieved: {{ number_format($section['sectionScore'],2) }}%)</small>
                </h5>
                <div class="progress" style="height:6px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($section['sectionWeight'] > 0 ? ($section['sectionScore'] / $section['sectionWeight']) * 100 : 0) }}%" aria-valuenow="{{ $section['sectionScore'] }}" aria-valuemin="0" aria-valuemax="{{ $section['sectionWeight'] }}"></div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>CRITERIA</th>
                                <th class="text-center" style="width:15%">RAW (0-10)</th>
                                <th class="text-center" style="width:20%">CRITERION SHARE (%)</th>
                                <th class="text-center" style="width:20%">WEIGHTED (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($section['criteria'] as $criteria)
                                <tr>
                                    <td>{{ $criteria['name'] }}</td>
                                    <td class="text-center">{{ number_format($criteria['score'], 2) }}</td>
                                    <td class="text-center">{{ number_format($criteria['criterionWeightShare'], 2) }}%</td>
                                    <td class="text-center fw-semibold">{{ number_format($criteria['weightedScore'], 2) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="3" class="text-end">Section Total</th>
                                <th class="text-center">{{ number_format($section['sectionScore'], 2) }}%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach

        <hr>
        <div class="d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Grand Total Weighted Score</div>
            <div class="h4 mb-0 text-primary">{{ number_format($grandTotal ?? $result->TotalScore, 2) }}%</div>
        </div>
    </div>
 </div>
@endsection