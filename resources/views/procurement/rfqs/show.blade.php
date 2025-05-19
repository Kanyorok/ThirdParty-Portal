@extends('layouts.app')
@section('title', 'RFQ Details')
@section('content')
<div class="container">
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createRFQModal">
        + New RFQ Line
    </button>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>RFQ Number:</strong> {{ $rfq->RFQNumber }}</p>
            <p><strong>RFQ Comments:</strong> {{ $rfq->Comments }}</p>
            
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
            
        </tbody>
        <tfoot>
            <tr>
            @if ($rfq->Status === 'Approved')
                <td colspan="4" class="text-center">
                    <button type="button" class="btn btn-primary btn-sm">Save</button>
                </td>
                <td colspan="3" class="text-center">
                    <button type="button" class="btn btn-secondary btn-sm">Print</button>
                </td>
            @else
                <td colspan="4" class="text-center">
                    <form id="approveForm" action="{{ route('rfqs.approve', $rfq->Id) }}" method="POST">
                        @csrf
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                            Approve
                        </button>
                    </form>
                </td>
                <td colspan="3" class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        Reject
                    </button>
                </td>
            @endif
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
    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="supplierSelectionForm" action="{{ route('rfqs.approve', $rfq->Id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Select Suppliers</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="suppliers" class="form-label">Suppliers</label>
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Send Emails & Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="createRFQModal" tabindex="-1" aria-labelledby="createRFQModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('linecategories.store') }}" class="modal-content">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title" id="createRFQModalLabel">Create RFQ Line</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <!-- Comments -->
            <div class="mb-3">
                <label>Item Category</label>
                <select name="ItemCategoryId" id="categoryDropdown" class="form-control" required>
                    <option value="">-- Select Category --</option>
                </select>
            </div>
        </div>
        <input type="hidden" name="RFQId" id="rfq-number" value="{{ $rfq->RFQNumber }}">
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save RFQ Line</button>
        </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categoryDropdown = document.getElementById('categoryDropdown');
    // Clear existing options except the placeholder
    categoryDropdown.length = 1;
    fetch(`/requisitionlines/categories`)
        .then(response => response.json())
        .then(data => {
            data.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.Id;
                option.textContent = cat.Name;
                categoryDropdown.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error fetching categories:', error);
        });
});
</script>

@endsection
