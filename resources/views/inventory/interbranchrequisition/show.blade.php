@extends('layouts.app')

@section('title', 'Inter-Branch Requisition')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Inter-Branch Requisition Details</h4>

    <div class="mb-3">
        <a href="{{ route('interbranchrequisition.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>

        {{-- Edit button restriction --}}
        <a href="{{ $item->Status === 'su' ? route('interbranchrequisition.edit', $item->Id) : '#' }}"
           class="btn btn-warning btn-sm {{ $item->Status !== 'su' ? 'disabled' : '' }}"
           data-bs-toggle="tooltip"
           title="{{ $item->Status !== 'su' ? 'Cannot edit - decision already made' : 'Edit Requisition' }}"
           onclick="@if($item->Status !== 'su') return showCustomError('You cannot edit this requisition because a decision has already been made.'); @endif">
            <i class="bi bi-pencil"></i> Edit
        </a>

        {{-- Delete button restriction --}}
        <button type="button"
                class="btn btn-danger btn-sm {{ $item->Status !== 'su' ? 'disabled' : '' }}"
                data-bs-toggle="tooltip"
                title="{{ $item->Status !== 'su' ? 'Cannot delete - decision already made' : 'Delete Requisition' }}"
                @if($item->Status === 'su')
                    onclick="confirmDelete('{{ $item->Id }}', '{{ $item->ReqNo }}')"
                @else
                    onclick="return showCustomError('You cannot delete this requisition because a decision has already been made.');"
                @endif>
            <i class="bi bi-trash"></i> Delete
        </button>

        <form id="delete-form-{{ $item->Id }}" action="{{ route('interbranchrequisition.destroy', $item->Id) }}" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    </div>

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
                    <div>{{ \Carbon\Carbon::parse($item->CreatedOn)->format('d/m/Y') }}</div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Status:</strong>
                    @php
                        $statusEnum = \App\Enums\Inventory\InterBranchRequisitionEnum::tryFrom($item->Status);
                    @endphp
                    <div>
                        @if($statusEnum)
                            <span class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                        @else
                            <span class="badge bg-warning">{{ $item->Status }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <strong>Created By:</strong>
                    <div>{{ $item->creator->Name ?? '-' }}</div>
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
                                <th>Qty</th>
                                <th>Approved Qty</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item->items as $index => $requisitionItem)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $requisitionItem->item->category->parent->Name ?? '-' }}</td>
                                    <td>{{ $requisitionItem->item->category->Name ?? '-' }}</td>
                                    <td>{{ $requisitionItem->item->ItemName ?? '-' }}</td>
                                    <td>{{ $requisitionItem->RequestedQty ?? '-' }}</td>
                                    <td>{{ $requisitionItem->ApprovedQty ?? '-' }}</td>
                                    <td>{{ $requisitionItem->Remarks ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p>No items found for this requisition.</p>
            @endif
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(Id, reqNo) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete requisition: " + reqNo + ". This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + Id).submit();
            }
        });
    }

    function showCustomError(message) {
        Swal.fire({
            title: 'Action Not Allowed',
            text: message,
            icon: 'warning',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
        return false;
    }
</script>

<style>
.btn {
    border-radius: 0.375rem;
    margin-right: 0.25rem;
    border: none;
    transition: all 0.2s ease-in-out;
}

.btn:last-child {
    margin-right: 0;
}

.btn-warning {
    background-color: #e58a00 !important;
    color: white !important;
}

.btn-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

.btn-warning:hover:not(.disabled) {
    background-color: #e0a800 !important;
}

.btn-danger:hover:not(.disabled) {
    background-color: #c82333 !important;
}

.btn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    box-shadow: none !important;
}

.bi {
    color: white;
}
</style>
@endsection
