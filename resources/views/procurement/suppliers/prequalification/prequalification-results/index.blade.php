@extends('layouts.app')

@section('title', 'Prequalification Evaluations')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0 text-primary">My Prequalification Evaluations</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Application No</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th>Total Score</th>
                            <th>Decision</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($evaluations as $evaluation)
                        <tr>
                            @php
                            $app = $evaluation->application;
                            $hasRound = (bool) optional($app)->round;
                            @endphp
                            <td>{{ optional($app)->applicationNo ?? 'N/A' }}</td>
                            <td>{{ optional($app->supplier)->ThirdPartyName ?? 'N/A' }}</td>
                            <td>{{ optional($app)->Status ?? 'N/A' }}</td>
                            <td>{{ optional($app)->SubmissionDate?->format('Y-m-d') ?? 'N/A' }}</td>
                            <td>
                                @if ($evaluation->application->result)
                                {{ number_format($evaluation->application->result->TotalScore, 2) }}%
                                @else
                                N/A
                                @endif
                            </td>
                            <td>
                                @if ($evaluation->application->result)
                                <span class="badge {{ $evaluation->application->result->Decision === 'Passed' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $evaluation->application->result->Decision }}
                                </span>
                                @else
                                N/A
                                @endif
                            </td>
                            <td>
                                @if (!$hasRound)
                                {{-- Prevent actions that require a configured round to avoid 404 from controller --}}
                                @if (optional($app)->result)
                                {{-- Result exists but round not configured: no view button shown --}}
                                @else
                                <button class="btn btn-sm btn-secondary" disabled title="Prequalification round not configured for this application">
                                    <i class="fas fa-edit me-1"></i> Evaluate
                                </button>
                                @endif
                                @else
                                @if (optional($app)->result)
                                {{-- View button removed by request --}}
                                @else
                                <a href="{{ route('prequalification.prequalification-evaluation.show', $evaluation->ApplicationID) }}" class="btn btn-sm btn-warning text-dark">
                                    <i class="fas fa-edit me-1"></i> Evaluate
                                </a>
                                @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No prequalification evaluations found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $evaluations->links() }}
        </div>
    </div>
</div>
@endsection