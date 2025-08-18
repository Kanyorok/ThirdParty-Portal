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
        <a href="{{ route('prequalification.applications.index') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Applications
        </a>
    </div>

    <div class="border-0 mb-5">
        <div class="card-body">
            <h5 class="fw-semibold text-primary mb-4">
                <i class="bi bi-info-circle me-2"></i> Application & Supplier Details
            </h5>
            <div class="table-responsive">
                <table id="detailsTable" class="table table-sm table-hover align-middle w-100">
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
                            <td class="fw-semibold text-muted">Phone</td>
                            <td>
                                <a href="tel:{{ $application->supplier->Phone }}" class="text-decoration-none">
                                    {{ $application->supplier->Phone ?? 'N/A' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Physical Address</td>
                            <td>{{ $application->supplier->PhysicalAddress ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($application->round && $application->round->masterSections->count())
    <div class="card border-0 rounded-3">
        <div class="card-body">
            <h5 class="fw-semibold text-primary mb-4">
                <i class="bi bi-list-check me-2"></i> Application Responses
            </h5>
            @foreach ($application->round->masterSections as $section)
            <div class="mb-5">
                <h6 class="fw-bold text-dark mb-3">{{ $section->Title }}</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle datatable w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Criteria</th>
                                <th>Response</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($section->criteria as $criteria)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $criteria->Question }}</td>
                                <td class="text-muted">
                                    {{ $application->responses->firstWhere('CriteriaID', $criteria->CriteriaID)?->Response ?? 'N/A' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="alert alert-light text-center">
        <i class="bi bi-info-circle me-2"></i> No responses available for this application.
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        // Remove DataTable from detailsTable
        // Keep DataTable only for response tables
        function getPageSize() {
            const w = window.innerWidth;
            return w < 768 ? 5 : (w < 1200 ? 10 : 15);
        }

        $('.datatable').DataTable({
            responsive: true,
            pageLength: getPageSize(),
            lengthMenu: [
                [5, 10, 15, 25, 50, -1],
                [5, 10, 15, 25, 50, "All"]
            ],
            ordering: true,
            autoWidth: false,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search responses...",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                zeroRecords: "No matching records found",
                emptyTable: "No data available"
            },
            dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-3"<"col-sm-6"i><"col-sm-6"p>>'
        });
    });
</script>
@endpush