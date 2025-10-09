@extends('layouts.app')

@section('content')
<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Prequalification Application</h1>
            <p class="text-muted mb-0">Detailed view for application
                <span class="fw-semibold">#{{ $application->ApplicationID }}</span>
            </p>
        </div>
        <div class="d-flex align-items-center">
            {{-- Button to trigger the evaluation form --}}
            <a href="{{ route('prequalification.prequalification-evaluation.show', $application->ApplicationID) }}" class="btn btn-primary me-2">
                <i class="fa fa-clipboard-check me-1"></i> Evaluate
            </a>

            {{-- Button to view already generated results --}}
            <a href="{{ route('prequalification.prequalification-evaluation.results', $application->ApplicationID) }}" class="btn btn-info me-2">
                <i class="fa fa-chart-bar me-1"></i> View Results
            </a>

            <a href="{{ route('prequalification.applications.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back to Applications
            </a>
        </div>
    </div>

    <div class="border-0 mb-5">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle w-100">
                    <tbody>
                        <tr>
                            <td class="fw-semibold text-muted">Application ID</td>
                            <td><span class="fw-bold text-dark">APP-{{ $application->ApplicationID }}</span></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Status</td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 {{ $application->Status->getColor() }} fw-bold">
                                    {{ $application->Status->getLabel() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Date Submitted</td>
                            <td>{{ $application->SubmittedOn?->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Round</td>
                            <td>{{ $application->round->Title ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Supplier Name</td>
                            <td>{{ $application->supplier->ThirdPartyName ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Trading Name</td>
                            <td>{{ $application->supplier->TradingName ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Email</td>
                            <td>
                                <a href="mailto:{{ $application->supplier->Email }}" class="text-decoration-none">
                                    {{ $application->supplier->Email ?? 'N/A' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Phone Number</td>
                            <td>
                                <a href="tel:{{ $application->supplier->Phone }}" class="text-decoration-none">
                                    {{ $application->supplier->Phone ?? 'N/A' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Supplier Physical Address</td>
                            <td>{{ $application->supplier->PhysicalAddress ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Categories Applied</td>
                            <td>
                                @if($application->category)
                                <span class="badge bg-secondary">{{ $application->category->CategoryName }}</span>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection