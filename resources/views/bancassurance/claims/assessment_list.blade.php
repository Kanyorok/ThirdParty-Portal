@extends('layouts.app')
@section('title', 'Claim Assessments')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('bancassurance.claims.index') }}" class="btn btn-primary">New Assessment</a>
    </div>
    <p><small>This is a list of all claims assessed</small></p>

    <table class="table table-bordered table-striped" id="assessments">
        <thead class="table-light">
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
                <td>{{ $loop->iteration }}</td>
                <td>{{ $assessment->ClaimId ?? '-' }} _ {{$assessment->claim->policy->PolicyNumber ?? '-'}}</td>
                <td>{{ $assessment->assessedby->Name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($assessment->AssessmentDate)->format('d/m/Y') }}</td>
                <td>{{ number_format($assessment->AssessmentAmount, 2) }}</td>
                <td>{{ $assessment->decision->Description ?? '-' }}</td>
                <td>{{ Str::limit($assessment->AssessmentComments, 40) ?? '-' }}</td>
                <td>
                    <a href="{{ route('bancassurance.claims.assessment_show', $assessment->Id) }}" class="btn btn-sm btn-outline-info">View</a>
                    <a href="{{ route('bancassurance.claims.assessment_edit', $assessment->Id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center text-muted">No assessments available.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
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
                lengthChange: true
            });
        });
    </script>
@endsection
