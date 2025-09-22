@extends('layouts.app')

@section('title', 'Prequalification Results')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-primary">Prequalification Results for {{ $application->applicationNo }}</h4>
                <a href="{{ route('prequalification.applications.show', $application->ApplicationID) }}"
                   class="btn btn-light">
                    <i class="fas fa-arrow-circle-left me-2"></i>Back to Application
                </a>
            </div>
            <div class="card-body">
                <p>
                    <strong>Supplier:</strong> {{ $application->supplier->ThirdPartyName }}<br>
                    <strong>Total Score:</strong> {{ number_format($result->TotalScore, 2) }}%<br>
                    <strong>Decision:</strong>
                    @if ($result->Decision === 'Passed')
                        <span class="badge bg-success">Passed</span>
                    @else
                        <span class="badge bg-danger">Failed</span>
                    @endif
                </p>

                <hr>

                @foreach ($sections as $section)
                    <div class="mb-4">
                        <h5 class="text-secondary">{{ $section['name'] }}
                            <small class="text-muted ms-2">
                                (Score: {{ number_format($section['sectionScore'], 2) }}% /
                                Max: {{ $section['sectionMaxScore'] }}%)
                            </small>
                        </h5>
                        <hr class="mt-1">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead class="table-light">
                                <tr>
                                    <th style="width: 50%;">CRITERIA</th>
                                    <th style="width: 10%;">WEIGHT (%)</th>
                                    <th style="width: 10%;">AWARDED SCORE</th>
                                    <th style="width: 10%;">MAX SCORE</th>
                                    <th style="width: 10%;">WEIGHTED SCORE (%)</th>
                                    <th style="width: 10%;">REMARKS</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($section['criteria'] as $criteria)
                                    <tr>
                                        <td>{{ $criteria['name'] }}</td>
                                        <td>{{ $criteria['weight'] }}%</td>
                                        <td>{{ $criteria['score'] ?? 'N/A' }}</td>
                                        <td>{{ $criteria['maxScore'] }}</td>
                                        <td>{{ number_format($criteria['weightedScore'], 2) }}%</td>
                                        <td>{{ $criteria['remarks'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
