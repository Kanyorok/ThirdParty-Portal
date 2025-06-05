@extends('layouts.app')

@section('title', 'Inter-Branch Requisition')

@section('content')
<!-- Custom error alert for client-side (JS) errors -->
<div id="customErrorContainer" style="display:none;">
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="customErrorMessage">
        <!-- Error message will be injected here -->
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
            onclick="hideCustomError()"></button>
    </div>
</div>

<div class="container mt-4">
    <h4 class="mb-3">Inter-Branch Requisition Details</h4>

    <a href="{{ route('interbranchrequisition.index') }}" class="btn btn-sm btn-secondary mb-3">Back to List</a>
    <a href="{{ route('interbranchrequisition.edit', $item->Id) }}" class="btn btn-sm btn-warning mb-3"
       onclick="@if($item->Status !== 'Pending Approval' && $item->Status !== 'Submitted') return showCustomError('You cannot edit this requisition because a decision has already been made.'); @endif">
       Edit Requisition
    </a>
    <form action="{{ route('interbranchrequisition.destroy', $item->Id) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-danger mb-3"
            onclick="@if($item->Status !== 'Pending Approval' && $item->Status !== 'Submitted') return showCustomError('You cannot delete this requisition because a decision has already been made.'); @else return confirm('Are you sure you want to delete this requisition?'); @endif">
            Delete Requisition
        </button>
    </form>

    <div class="card shadow">
        <div class="card-header bg-light fw-bold">
            Requisition #{{ $item->ReqNo ?? '-' }}
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Requesting Branch:</strong>
                    <div>{{ $item->fromBranch->Name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>To Branch:</strong>
                    <div>{{ $item->toBranch->Name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Date:</strong>
                    <div>{{ \Carbon\Carbon::parse($item->CreatedOn)->format('Y-m-d') }}</div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Status:</strong>
                    <div>
                        @if($item->Status === 'Approved')
                            <span class="badge text-bg-success">Approved</span>
                        @elseif($item->Status === 'Pending Approval' || $item->Status === 'Submitted')
                            <span class="badge bg-warning">Pending Approval</span>
                        @elseif($item->Status === 'Rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @else
                            <span class="badge bg-secondary">{{ $item->Status }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <strong>Created By:</strong>
                    <div>{{ $item->creator->Name?? '-' }}</div>
                </div>
            </div>
            <hr>
            <h6 class="fw-bold">Requested Item(s)</h6>
            @if(isset($item->items) && count($item->items))
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Parent Category</th>
                                <th>Category</th>
                                <th>Item</th>
                                <th>UOM</th>
                                <th>Qty</th>
                                <th>ApprovedQty</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item->items as $index => $requisitionItem)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $requisitionItem->item->category->parent->Name ?? '-' }}</td>
                                    <td>{{ $requisitionItem->item->category->Name ?? '-' }}</td>
                                    <td>{{ $requisitionItem->item->ItemName ?? '-' }}</td>
                                    <td>{{ $requisitionItem->uom->Code ?? '-' }}</td>
                                    <td>{{ $requisitionItem->RequestedQty ?? '-' }}</td>
                                    <td>{{ $requisitionItem->ApprovedQty ?? '-' }}</td>
                                    <td>{{ $requisitionItem->Remarks ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('interbranchrequisition.edit', $item->Id) }}" class="btn btn-sm btn-warning" title="Edit Requisition"
                                           onclick="@if($item->Status !== 'Pending Approval' && $item->Status !== 'Submitted') return showCustomError('You cannot edit this requisition because a decision has already been made.'); @endif">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('interbranchrequisition.destroy', $item->Id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" title="Delete Requisition"
                                                onclick="@if($item->Status !== 'Pending Approval' && $item->Status !== 'Submitted') return showCustomError('You cannot delete this requisition because a decision has already been made.'); @else return confirm('Are you sure you want to delete this requisition?'); @endif">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="row mb-3">
                    <div class="col-md-2">
                        <strong>Category:</strong>
                        <div>{{ $item->item->category->parent->Name ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                        <strong>Subcategory:</strong>
                        <div>{{ $item->item->category->Name ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <strong>Item:</strong>
                        <div>{{ $item->item->ItemName ?? '-' }}</div>
                    </div>
                    <div class="col-md-1">
                        <strong>UOM:</strong>
                        <div>{{ $item->uom->Code ?? '-' }}</div>
                    </div>
                    <div class="col-md-1">
                        <strong>Qty:</strong>
                        <div>{{ $item->RequestedQty ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                        <strong>Remarks:</strong>
                        <div>{{ $item->Remarks ?? '-' }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
<script>
function showCustomError(message) {
    document.getElementById('customErrorMessage').childNodes[0].nodeValue = message;
    document.getElementById('customErrorContainer').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return false;
}
function hideCustomError() {
    document.getElementById('customErrorContainer').style.display = 'none';
}
</script>
@endsection