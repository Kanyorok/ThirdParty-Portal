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
                            <td>{{ optional($evaluation->application)->applicationNo ?? 'N/A' }}</td>
                            <td>{{ optional($evaluation->application->supplier)->ThirdPartyName ?? 'N/A' }}</td>
                            <td>{{ optional($evaluation->application)->Status ?? 'N/A' }}</td>
                            <td>{{ optional($evaluation->application)->SubmissionDate->format('Y-m-d') ?? 'N/A' }}</td>
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
                                @if ($evaluation->application->result)
                                <a href="{{ route('prequalification.prequalification-evaluation.results', $evaluation->ApplicationID) }}" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-eye me-1"></i> View Results
                                </a>
                                @else
                                <a href="{{ route('prequalification.prequalification-evaluation.show', $evaluation->ApplicationID) }}" class="btn btn-sm btn-warning text-dark">
                                    <i class="fas fa-edit me-1"></i> Evaluate
                                </a>
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