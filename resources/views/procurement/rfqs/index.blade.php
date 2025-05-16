@extends('layouts.app')

@section('title', 'RFQs List')

@section('content')
<div class="container">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Button to trigger modal -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createRFQModal">
        + New RFQ
    </button>

    @if($rfqs->count())
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Quotation Number</th>
                    <th>Quotation Status</th>
                    <th>RFQ Category</th>
                    <th>Submission Deadline</th>
                    <th>Created On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rfqs as $rfq)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $rfq->RFQNumber ?? '-' }}</td>
                        <td>{{ $rfq->Status ?? '-' }}</td>
                        <td>{{ $rfq->category->Name ?? '-' }}</td>
                        <td>{{ $rfq->SubmissionDeadline ? \Carbon\Carbon::parse($rfq->SubmissionDeadline)->format('d M Y') : '-' }}</td>
                        <td>{{ $rfq->CreatedOn ? \Carbon\Carbon::parse($rfq->CreatedAt)->format('d M Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('rfqs.show', $rfq->Id) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No RFQs created yet.</p>
    @endif
</div>

<!-- Modal -->
<div class="modal fade" id="createRFQModal" tabindex="-1" aria-labelledby="createRFQModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('rfqs.store') }}" class="modal-content">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title" id="createRFQModalLabel">Create RFQ</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <!-- Comments -->
            <div class="mb-3">
                <label for="Comments" class="form-label">Comments</label>
                <textarea name="Comments" id="Comments" rows="3" class="form-control"></textarea>
            </div>

            <!-- Submission Deadline -->
            <div class="mb-3">
                <label for="SubmissionDeadline" class="form-label">Submission Deadline</label>
                <input type="datetime-local" name="SubmissionDeadline" id="SubmissionDeadline" class="form-control">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save RFQ</button>
        </div>
    </form>
  </div>
</div>
@endsection
