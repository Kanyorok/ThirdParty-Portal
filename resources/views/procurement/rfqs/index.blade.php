@php use Carbon\Carbon; @endphp
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
                    <th>Requisition No</th>
                    <th>Quotation Status</th>
                    <th>Submission Deadline</th>
                    <th>Created On</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rfqs as $rfq)
                    <tr>
                        {{-- pagination-aware index: first item on current page + loop index --}}
                        <td>{{ ($rfqs->currentPage() - 1) * $rfqs->perPage() + $loop->iteration }}</td>
                        <td>{{ $rfq->RFQNumber ?? '-' }}</td>
                        <td>{{ $rfq->requisition->RequisitionNo ?? '-' }}</td>
                        <td>{{ $rfq->Status ?? '-' }}</td>
                        <td>{{ $rfq->SubmissionDeadline ? Carbon::parse($rfq->SubmissionDeadline)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $rfq->CreatedOn ? Carbon::parse($rfq->CreatedOn)->format('d/m/Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('rfqs.show', $rfq->Id) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $rfqs->links() }}
            </div>
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
                    <div class="mb-3">
                        <label for="RequisitionId" class="form-label">Select Requisition <span
                                class="text-danger">*</span></label>
                        <select name="RequisitionId" id="RequisitionId" class="form-select" required>
                            <option value="">-- Choose Requisition --</option>
                            @foreach($requisitions as $requisition)
                                <option value="{{ $requisition->Id }}">{{ $requisition->RequisitionNo }} @if(!empty($requisition->PlanTitle)) - {{ $requisition->PlanTitle }} @endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="Comments" class="form-label">Comments <span class="text-danger">*</span></label>
                        <textarea name="Comments" id="Comments" rows="3" class="form-control" required></textarea>
                    </div>

                    <!-- Submission Deadline -->
                    <div class="mb-3">
                        <label for="SubmissionDeadline" class="form-label">Submission Deadline <span
                                class="text-danger">*</span></label>
                        <input type="date" name="SubmissionDeadline" id="SubmissionDeadline" class="form-control"
                               required
                               min="{{ Carbon::now()->toDateString() }}">

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save RFQ</button>
                </div>
            </form>
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
    <script>
        @if($errors->any())
        var createRFQModal = new bootstrap.Modal(document.getElementById('createRFQModal'));
        createRFQModal.show();
        @endif
    </script>

@endsection
