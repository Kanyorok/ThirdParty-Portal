@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Manual Bid Submissions</h4>
        <a href="{{ route('tendersubmission.create') }}" class="btn btn-sm btn-success">+ Record Manual Submission</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Ref</th>
                    <th>Supplier</th>
                    <th>Submission Mode</th>
                    <th>Received At</th>
                    <th>Recorded By</th>
                    <th>Remarks</th>
                    <th>Documents</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($submissions as $index => $submission)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $submission->TenderRef }}</td>
                        <td>{{ $submission->SupplierName }}</td>
                        <td>{{ $submission->SubmissionMode }}</td>
                        <td>{{ $submission->ReceivedAt->format('Y-m-d h:i A') }}</td>
                        <td>{{ $submission->RecordedBy }}</td>
                        <td>{{ $submission->Remarks ?? 'N/A' }}</td>
                        <td>
                            @if ($submission->Documents)
                                <a href="#" class="btn btn-sm btn-link">Download</a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('tendersubmission.view', $submission->id) }}" class="btn btn-sm btn-outline-info">View</a>
                            <a href="{{ route('tendersubmission.edit', $submission->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No submissions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection