@extends('layouts.app')
@section('title', 'RFQ Details')
@section('content')
<div class="container">
    <h3>RFQ Details</h3>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>RFQ Number:</strong> {{ $rfq->RFQNumber }}</p>
            <p><strong>RFQ Comments:</strong> {{ $rfq->Comments }}</p>
            <p><strong>Item Category:</strong> {{ $rfq->category->Name }}</p>
        </div>
    </div>

    <h5>Requisition Items Details:</h5>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>UOM</th>
                <th>Submission Deadline</th>
                <th>Description</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rfq->RequisitionItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ $item['unit'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($rfq->SubmissionDeadline)->format('d M Y') }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $rfq->Status}}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-center">
                    <form action="{{ route('rfqs.approve', $rfq->Id) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>
                </td>
                <td colspan="3" class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        Reject
                    </button>
                </td>
            </tr>
        </tfoot>
    </table>
    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('rfqs.reject', $rfq->Id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject RFQ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="RejectionReason" class="form-label">Reason for Rejection</label>
                            <textarea name="RejectionReason" id="RejectionReason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
