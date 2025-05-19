@extends('layouts.app')
@section('title', '')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Manual Bid Submissions</h4>
        <a href="{{ route('tendersubmission.create') }}" class="btn btn-sm btn-success">+ Record Manual Submission</a>
    </div>

    <div class="table-responsive">
        <table id="bidsubmissionTable" class="table table-bordered table-striped align-middle">
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
                            <a href="{{ route('tendersubmission.view', $submission->Id) }}" class="btn btn-sm btn-outline-info">View</a>
                            <a href="{{ route('tendersubmission.edit', $submission->Id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#bidsubmissionTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
