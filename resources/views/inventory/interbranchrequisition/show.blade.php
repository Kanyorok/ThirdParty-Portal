@extends('layouts.app')

@section('title', 'Inter-Branch Requisition')

@section('content')

    <div id="customErrorContainer" style="display:none;">
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="customErrorMessage">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                    onclick="hideCustomError()"></button>
        </div>
    </div>

    <div class="container mt-4">
        <h4 class="mb-3">Inter-Branch Requisition Details</h4>

        <a href="{{ route('interbranchrequisition.index', $item->Id) }}" class="btn btn-secondary btn-sm">Back To
            List</a>
        <a href="{{ route('interbranchrequisition.edit', $item->Id) }}"
           class="btn btn-warning btn-sm"
           onclick="@if($item->Status !== 'su') return showCustomError('You cannot edit this requisition because a decision has already been made.'); @endif">
            Edit
        </a>
        <a href="#"
           class="btn btn-danger btn-sm"
           onclick="@if($item->Status !== 'su') return showCustomError('You cannot delete this requisition because a decision has already been made.'); @else confirmDelete('{{ $item->Id }}'); return false; @endif">
            Delete
        </a>
        <form id="delete-form-{{ $item->Id }}" action="{{ route('interbranchrequisition.destroy', $item->Id) }}"
              method="POST" style="display:none;">
            @csrf
            @method('DELETE')
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
                            @php
                                $statusEnum = \App\Enums\Inventory\InterBranchRequisitionEnum::tryFrom($item->Status);
                            @endphp
                            @if($statusEnum)
                                <span class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                            @else
                                <span class="badge bg-warning">{{ $item->Status }}</span>
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
        function confirmDelete(Id) {
            if (confirm('Are you sure you want to delete this requisition?')) {
                document.getElementById('delete-form-' + Id).submit();
            }
        }

        function showCustomError(message) {
            document.getElementById('customErrorMessage').childNodes[0].nodeValue = message;
            document.getElementById('customErrorContainer').style.display = 'block';
            window.scrollTo({top: 0, behavior: 'smooth'});
            return false;
        }

        function hideCustomError() {
            document.getElementById('customErrorContainer').style.display = 'none';
        }
    </script>
@endsection
