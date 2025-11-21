@extends('layouts.app')

@section('title', 'Claim Assessments')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    /* 🧩 Table Styling */
    #assessments thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        text-align: center;
    }
    .table td, .table th {
        vertical-align: middle !important;
    }
    table.dataTable tbody tr:hover {
        background-color: #f9fbfd;
    }

    /* 🔍 DataTables Inputs */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px;
        padding: 4px 12px;
        border: 1px solid #ced4da;
    }
    .dataTables_wrapper .dataTables_length select {
        border-radius: 20px;
        padding: 3px 10px;
        border: 1px solid #ced4da;
    }

    /* 🎨 Button Group & Badge Styling */
    .btn-group .btn {
        margin-right: 4px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    .badge {
        font-size: 0.85rem;
    }
    .btn i {
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- ✅ Header / Action --}}
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('bancassurance.claims.index') }}"
           class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> New Assessment
        </a>
    </div>

    <p class="text-muted small mb-3">
        <i class="bi bi-file-earmark-text me-2 text-primary"></i>
        List of all claims assessed.
    </p>

    {{-- ✅ Assessment Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="assessments" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Claim ID</th>
                            <th>Assessed By</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Decision</th>
                            <th>Comments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assessments as $assessment)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $assessment->ClaimId ?? '-' }} _ {{ $assessment->claim->policy->PolicyNumber ?? '-' }}</td>
                            <td>{{ $assessment->assessedby->Name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($assessment->AssessmentDate)->format('d M Y') }}</td>                           <td>{{ number_format($assessment->AssessmentAmount, 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">
                                    {{ $assessment->decision->Description ?? '-' }}
                                </span>
                            </td>
                            <td>{{ Str::limit($assessment->AssessmentComments, 40) ?? '-' }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('bancassurance.claims.assessment_show', $assessment->Id) }}"
                                       class="btn btn-outline-info rounded-pill px-2" title="View">
                                        <i class="bi bi-eye"></i> View
                                    </a>

                                    @if($assessment->claimpaiyments()->exists())
                                        <button class="btn btn-secondary rounded-pill px-2" disabled>Paid</button>
                                    @else
                                        <a href="{{ route('bancassurance.claims.assessment_edit', $assessment->Id) }}"
                                           class="btn btn-outline-primary rounded-pill px-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                No assessments available.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#assessments').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search assessments..."
            },
            columnDefs: [
                { orderable: false, targets: [7] } // Disable sorting on Actions
            ]
        });
    });
</script>
@endsection
