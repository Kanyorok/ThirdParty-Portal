@extends('layouts.app')

@section('title', 'Prequalification Results')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Prequalification Report</h4>
        </div>
        <div class="card-body">
            <h5 class="card-title">Application Details</h5>
            <p><strong>Application ID:</strong> {{ $application->ApplicationID }}</p>
            <p><strong>Supplier:</strong> {{ optional($application->supplier)->SupplierName ?? 'N/A' }}</p>
        </div>
    </div>

    @foreach ($sections as $section)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-muted">{{ $section['name'] }}</h5>
            <span class="badge bg-secondary p-2">
                Section Score: {{ number_format($section['sectionScore'], 2) }} / {{ number_format($section['sectionMaxScore'], 2) }}
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Criteria</th>
                            <th class="text-center">Score</th>
                            <th class="text-center">Max Score</th>
                            <th class="text-center">Weight</th>
                            <th class="text-center">Weighted Score</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($section['criteria'] as $criteria)
                        <tr>
                            <td>{{ $criteria['name'] }}</td>
                            <td class="text-center">{{ $criteria['score'] }}</td>
                            <td class="text-center">{{ $criteria['maxScore'] }}</td>
                            <td class="text-center">{{ $criteria['weight'] }}%</td>
                            <td class="text-center">{{ number_format($criteria['weightedScore'], 2) }}</td>
                            <td>{{ $criteria['remarks'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach

    <div class="card shadow-sm">
        <div class="card-body bg-dark text-white rounded-lg">
            <h5 class="card-title text-center text-white">Final Prequalification Decision</h5>
            <div class="text-center fs-2 fw-bold">
                @if ($result->Decision === 'Passed')
                <span class="text-success"><i class="fas fa-check-circle me-2"></i>Passed</span>
                @else
                <span class="text-danger"><i class="fas fa-times-circle me-2"></i>Failed</span>
                @endif
            </div>
            <div class="text-center mt-2">
                <p class="text-muted">Final score: {{ number_format($result->TotalScore, 2) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection