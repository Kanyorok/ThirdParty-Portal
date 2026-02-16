@extends('layouts.app')
@section('title', 'Tender Item Details')
@section('content')
<div class="container mt-4">
    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <h4 class="mb-4">📄 Tender Item Details – {{$tender->TenderNo}}</h4>

    <!-- Tender Summary Info -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('initiatetender.update', $tender->Id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value='editTenderInfo'>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tender Title</label>
                        <input type="text" class="form-control" value="{{$tender->Title}}" name="title"
                            placeholder="Enter Tender Title" required>
                        @error('title')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tender Type</label>
                        <select class="form-select" id="tenderType" name="TenderType" required>
                            <option value="">-- Select Tender Type --</option>
                            <option value="op" {{$tender->TenderType->value=='op'?'selected':''}}>Open Tender</option>
                            <option value="rs" {{$tender->TenderType->value=='rs'?'selected':''}}>Restricted Tender</option>
                        </select>
                        @error('TenderType')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Initiated By</label>
                        <input type="text" class="form-control" value="{{auth()->user()->Name}}" readonly>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tender Category</label>
                        <select class="form-select" id="tenderCategory" name="tender_category_id" required>
                            <option>-- Select Category --</option>
                            @foreach ($tenderCategories as $item)
                            <option value="{{$item->Id}}" {{$item->Id==$tenderCategory?'selected':''}}>
                                {{$item->TenderCategory}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Item Category</label>
                        <input type="text" class="form-control" value="{{$itemCategory}}" readonly>
                        <input type="hidden" name="item_category_id" value="{{$itemCategoryID}}">
                        <small class="text-muted">Item category is determined by tender category</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Currency</label>
                        <select class="form-select" id="currencyType" name="currency_id" required>
                            <option selected disabled>-- Select Your Currency --</option>
                            @foreach ($allCurrency as $item)
                            <option value="{{$item->Id}}" {{$item->Id==$currency?'selected':''}}>
                                {{$item->Name}} ({{$item->Code}})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-4 mb-3">
                        <label for="submissionDeadline" class="form-label fw-bold">Submission Deadline:</label>
                        <input type="date"
                            value="{{ \Carbon\Carbon::parse($tender->SubmissionDeadline)->format('Y-m-d') }}"
                            min="{{ date('Y-m-d') }}"
                            class="form-control" id="submissionDeadline" name="submission_deadline" required>
                        @error('submission_deadline')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="openingDate" class="form-label fw-bold">Opening Date:</label>
                        <input type="date"
                            value="{{ \Carbon\Carbon::parse($tender->OpeningDate)->format('Y-m-d') }}"
                            min="{{ date('Y-m-d') }}"
                            class="form-control" id="openingDate" name="opening_date" required>
                        @error('opening_date')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="tenderDocuments" class="form-label fw-bold">Attach Tender Document:</label>
                        <input class="form-control" type="file" id="tenderDocuments" name="documents[]" multiple>
                        <small class="text-muted">Upload additional documents (max 10MB each)</small>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    @canUpdate('tender')
                    <button type="submit" class="btn btn-primary"
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        <i class="fas fa-save"></i> Update Tender Info
                    </button>
                    @endcanUpdate
                </div>
            </form>
        </div>
    </div>

    <!-- Attached Documents Section -->
    @if(isset($documents) && $documents->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">📎 Attached Documents</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 40%">File Name</th>
                            <th style="width: 15%">File Type</th>
                            <th style="width: 15%">Size</th>
                            <th style="width: 15%">Uploaded</th>
                            <th style="width: 10%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <i class="fas fa-file-{{ $doc->getFileIcon() }} text-primary me-2"></i>
                                {{ $doc->FileName ?? $doc->Name ?? 'Document' }}
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ strtoupper($doc->FileExtension ?? 'N/A') }}
                                </span>
                            </td>
                            <td>{{ $doc->getFormattedSize() }}</td>
                            <td>
                                <small class="text-muted">
                                    {{ $doc->CreatedOn ? \Carbon\Carbon::parse($doc->CreatedOn)->format('M d, Y H:i') : 'N/A' }}
                                </small>
                            </td>
                            <td class="text-center">
                                @if($doc->canView())
                                {{-- View Document - Uses the DocumentActionsController preview --}}
                                <a href="{{ route('file.preview', ['document' => $doc->Id]) }}"
                                    class="btn btn-sm btn-outline-primary"
                                    title="View Document"
                                    target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>

                                {{-- Download Document - Need to find the repository first --}}
                                @php
                                // Get the repository ID from document relation
                                $repositoryId = $doc->RepositoryId ?? $doc->repository?->Id ?? null;
                                @endphp

                                @if($repositoryId)
                                <a href="{{ route('file-download.store', ['repository' => $repositoryId, 'document' => $doc->Id]) }}"
                                    class="btn btn-sm btn-outline-success"
                                    title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                @else
                                <span class="text-muted small" title="Repository not found">
                                    <i class="fas fa-download"></i>
                                </span>
                                @endif
                                @else
                                <span class="text-muted small">No access</span>
                                @endif

                                @canDelete('tender')
                                @if($tender->Status === \App\Enums\TenderStatusEnum::Draft)
                                {{-- Delete Document - Uses repository-based route --}}
                                @php
                                $repositoryId = $doc->RepositoryId ?? $doc->repository?->Id ?? null;
                                @endphp

                                @if($repositoryId)
                                <form action="{{ route('files.destroy', ['repository' => $repositoryId, 'document' => $doc->Id]) }}"
                                    method="POST"
                                    style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete \'{{ addslashes($doc->FileName ?? $doc->Name ?? 'this document') }}\'? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Delete Document">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-muted small" title="Cannot delete - repository not found">
                                    <i class="fas fa-trash-alt"></i>
                                </span>
                                @endif
                                @endif
                                @endcanDelete
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">📎 Attached Documents</h5>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i> No documents attached yet. Use the form above to upload documents.
            </div>
        </div>
    </div>
    @endif



    <!-- Items Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-3">📦 Items in this Tender</h5>
                @canUpdate('tender')
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                    data-bs-target="#addItemModal">
                    + Add Item
                </button>
                @endcanUpdate
            </div>

            @if($items->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No items added yet. Click "Add Item" to get started.
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Item Description</th>
                            <th>Category</th>
                            <th>Source</th>
                            <th>Quantity</th>
                            <th>PR Ref</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{ $item->item?->ItemName ?? 'N/A' }}</td>
                            <td>{{ $item->category?->Name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $item->SourceType === 'PLAN' ? 'primary' : 'secondary' }}">
                                    {{ $item->SourceType ?? 'N/A' }}
                                </span>
                            </td>
                            <td>{{ number_format($item->QtyToTender, 2) }}</td>
                            <td>{{ $item->RelatedPRID ?? '—' }}</td>
                            <td>
                                @if(strtoupper($item->SourceType) === 'MANUAL')
                                @canUpdate('tender')
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editItemModal-{{$item->id}}"
                                    title="Edit Quantity">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcanUpdate
                                @else
                                <span class="text-muted small">Plan items cannot be edited</span>
                                @endif

                                @canDelete('tender')
                                <form action="{{ route('initiatetender.update', $tender->Id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="type" value='crudItem'>
                                    <input type="hidden" name="crudType" value='deleteItem'>
                                    <input type="hidden" name="item_id" value="{{$item->id}}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                        onclick="return confirm('Are you sure you want to delete item \'{{ $item->item?->ItemName }}\'? This action cannot be undone.')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcanDelete
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    @if ($tender->TenderType?->name == 'Restricted')
    <!-- Selected Suppliers Section -->
    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-3">🏷️ Selected Suppliers (Restricted Tender)</h5>
                @canUpdate('tender')
                <button type="button" class="btn btn-sm btn-success"
                    data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                    + Add Supplier
                </button>
                @endcanUpdate
            </div>

            @if($suppliers->isEmpty())
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> No suppliers selected. Restricted tenders require at least one supplier.
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Supplier Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($suppliers as $item)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{ $item->supplier->party->ThirdPartyName ?? $item->supplier->party->TradingName ?? '—' }}</td>
                            <td>{{ $item->supplier->party->Email ?? '—' }}</td>
                            <td>{{ $item->supplier->party->Phone ?? '—' }}</td>
                            <td class="text-center">
                                @canDelete('tender')
                                <form action="{{ route('initiatetender.update', $tender->Id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="type" value='crudSupplier'>
                                    <input type="hidden" name="crudType" value='deleteSupplier'>
                                    <input type="hidden" name="tender_supplier_id" value="{{$item->id}}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                        onclick="return confirm('Remove supplier \'{{ $item->supplier->party->ThirdPartyName ?? $item->supplier->party->TradingName }}\'?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcanDelete
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addItemModalLabel">
                    <i class="fas fa-plus-circle"></i> Add New Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('initiatetender.update', $tender->Id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="hidden" name="type" value='crudItem'>
                <input type="hidden" name="crudType" value='addItem'>
                <input type="hidden" name="itemCategoryID" value="{{$itemCategoryID}}">

                <div class="modal-body">
                    @if(!empty($otherItemsForThatTender) && count($otherItemsForThatTender) > 0)
                    <div class="mb-3">
                        <label for="item_id" class="form-label fw-bold">
                            Item <span class="text-danger">*</span>
                        </label>
                        <select name="item_id" id="item_id" class="form-select" required>
                            <option value="" selected disabled>-- Select Item --</option>
                            @foreach($otherItemsForThatTender as $availableItem)
                            <option value="{{ $availableItem['Id'] }}">
                                {{ $availableItem['ItemName'] }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            Only items matching tender category "{{ $itemCategory }}" are shown
                        </small>
                        @error('item_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="QtyToTender" class="form-label fw-bold">
                            Quantity to Tender <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0.01" name="QtyToTender"
                            id="QtyToTender" class="form-control"
                            placeholder="Enter quantity" required>
                        @error('QtyToTender')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>PR Reference will be auto-generated</strong><br>
                        Format: <code>PR/{{ $tender->TenderNo }}/MAN-###</code>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>No items available</strong><br>
                        No items match the selected tender category "{{ $itemCategory }}".
                        Items must:
                        <ul class="mb-0 mt-2">
                            <li>Belong to the correct item category</li>
                            <li>Have the correct item type for this tender category</li>
                            <li>Have a valid price set</li>
                            <li>Not be deleted</li>
                        </ul>
                    </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    @if(!empty($otherItemsForThatTender) && count($otherItemsForThatTender) > 0)
                    <button type="submit" class="btn btn-success"
                        onclick="this.disabled=true; this.innerText='Adding...'; this.form.submit();">
                        <i class="fas fa-check"></i> Add Item
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Edit Item Modal (Only for MANUAL items) -->
@foreach ($items as $item)
@if(strtoupper(string: $item->SourceType) === 'MANUAL')
<div class="modal fade" id="editItemModal-{{$item->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Edit Item Quantity
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('initiatetender.update', $tender->Id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="type" value="crudItem">
                <input type="hidden" name="crudType" value="updateQty">
                <input type="hidden" name="item_id" value="{{ $item->id }}">

                <div class="modal-body">
                    <div class="alert alert-info">
                        <div class="mb-2">
                            <strong>Item:</strong> {{ $item->item?->ItemName ?? 'N/A' }}
                        </div>
                        <div class="mb-2">
                            <strong>Current Quantity:</strong> {{ number_format($item->QtyToTender, 2) }}
                        </div>
                        @if($item->RelatedPRID)
                        <div class="mb-0">
                            <strong>PR Reference:</strong>
                            <span class="badge bg-success">
                                <i class="fas fa-lock"></i> {{ $item->RelatedPRID }}
                            </span>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Auto-generated and locked
                            </small>
                        </div>
                        @else
                        <div class="mb-0">
                            <span class="text-warning">
                                <i class="fas fa-exclamation-triangle"></i> No PR reference assigned
                            </span>
                        </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="QtyToTender-{{$item->id}}" class="form-label fw-bold">
                            New Quantity <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0.01"
                            name="QtyToTender"
                            value="{{$item->QtyToTender}}"
                            id="QtyToTender-{{$item->id}}"
                            class="form-control"
                            required>
                        <small class="text-muted">Only quantity can be edited. PR reference is locked after creation.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Quantity
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Add Supplier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('initiatetender.update', $tender->Id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="type" value='crudSupplier'>
                <input type="hidden" name="crudType" value='addSupplier'>

                <div class="modal-body">
                    @if(!empty($otherSuppliers) && count($otherSuppliers) > 0)
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label fw-bold">
                            Select Supplier <span class="text-danger">*</span>
                        </label>
                        <select name="supplier_id" id="supplier_id" class="form-select" required>
                            <option value="" selected disabled>-- Select Supplier --</option>
                            @foreach($otherSuppliers as $supplier)
                            <option value="{{ $supplier->Id }}">
                                {{ $supplier->SupplierName }}
                                @if($supplier->ContactPhone)
                                | {{ $supplier->ContactPhone }}
                                @endif
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            Only prequalified suppliers for "{{ $itemCategory }}" category
                        </small>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No additional suppliers available for the "{{ $itemCategory }}" category.
                    </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    @if(!empty($otherSuppliers) && count($otherSuppliers) > 0)
                    @canUpdate('tender')
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Add Supplier
                    </button>
                    @endcanUpdate
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function() {
        const tenderCatSel = document.getElementById('tenderCategory');

        if (tenderCatSel) {
            tenderCatSel.addEventListener('change', function() {
                if (this.value) {
                    alert('Warning: Changing the tender category will require you to re-select items that match the new category.');
                }
            });
        }
    })();
</script>
@endpush