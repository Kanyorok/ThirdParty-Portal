@extends('layouts.app')
@section('title', 'Tender Item Details')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📄 Tender Item Details – {{$tender->TenderNo}}</h4>


        <!-- Tender Summary Info -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="POST" action="{{ route('initiatetender.update', $tender->Id) }}">
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
                                <option value="op" id="openTender" {{$tender->TenderType->value=='op'?'selected':''}}>Open Tender</option>
                                <option value="rs" id="restrictedTender" {{$tender->TenderType->value=='rs'?'selected':''}}>Restricted Tender</option>
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
                                    <option
                                        value="{{$item->Id}}" {{$item->Id==$tenderCategory?'selected':''}}>{{$item->TenderCategory}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Item Category</label>
                            <select class="form-select" id="itemCategory" value="{{$itemCategory }}" required
                                    name="item_category_id">
                                <option selected disabled>{{$itemCategory }}</option>
                            </select>
                </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Currency</label>
                            <select class="form-select" id="currencyType" name="currency_id" required>
                                <option selected disabled>-- Select Your Currency --</option>
                                @foreach ($allCurrency as $item)
                                    <option value="{{$item->Id}}" {{$item->Id==$currency?'selected':''}}>{{$item->Name}}
                                        ({{$item->Code}})
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
                                   class="form-control" id="submissionDeadline" name="submission_deadline" required>
                            @error('submission_deadline')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="openingDate" class="form-label fw-bold">Opening Date:</label>
                            <input type="date"
                                   value="{{ \Carbon\Carbon::parse($tender->OpeningDate)->format('Y-m-d') }}"
                                   class="form-control" id="openingDate" name="opening_date" required>
                            @error('opening_date')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="tenderDocuments" class="form-label fw-bold">Attach Tender Document:</label>
                            <input class="form-control" type="file" id="tenderDocuments" name="documents[]" multiple>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        @canUpdate('tender')
                        <button type="submit" class="btn btn-primary"
                                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Edit
                            Tender Info
                        </button>
                        @endcanUpdate
                    </div>
            </div>
            </form>
        </div>
    </div>


    <!-- Items Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-3">📦 Items in this Tender</h5>
                <h4></h4>
                @canUpdate('tender')
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                        data-bs-target="#addItemModal">
                    + Add Item
                </button>
                @endcanUpdate
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Item Description</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Document</th>
                        <th>PR Ref</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{ $item->item?->ItemName }}</td>
                            <td>{{ $item->category?->Name }}</td>
                            <td>{{$item->QtyToTender}}</td>
                            <td>document.pdf
                                {{-- <a href=""><i class="fa fa-download"></i></a> --}}
                            </td>
                            <td>PR/2025/211</td>
                            <td>
                                @canUpdate('tender')
                                <a href="#" class="btn btn-sm btn-outline-primary" title="Edit"
                                   data-bs-toggle="modal" data-bs-target="#editItemModal-{{$item->id}}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcanUpdate
                                @canDelete('tender')
                                <form action="{{ route('initiatetender.update', $tender->Id) }}" method="POST"
                                      style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="type" value='crudItem'>
                                    <input type="hidden" name="crudType" value='deleteItem'>
                                    <input type="hidden" name="item_id" value="{{$item->id}}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                            onclick="return confirm('Are you sure you want to delete Item \'{{ $item->item?->ItemName }}\'? This action cannot be undone.')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcanDelete
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                    <tfoot class="table-light fw-bold text-end">
                    </tfoot>
                </table>
            </div>
        </div>
    </div>


    @if ($tender->TenderType?->name == 'Restricted')
        <!-- Selected Suppliers Section -->
        <div class="card shadow-sm mb-5">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-3">🏷️ Selected Suppliers (Restricted Tender)</h5>
                    <h4></h4>
                    @canUpdate('tender')
                    <a href="#" class="btn btn-sm btn-success"
                       data-bs-toggle="modal" data-bs-target="#addSupplierModal">+ Add Supplier</a>
                    @endcanUpdate
                </div>
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
                                <td>{{$loop->index+1}}</td>
                <td>{{ $item->supplier->thirdParty->TradingName ?? $item->supplier->thirdParty->ThirdPartyName ?? '—' }}</td>
                <td>{{ $item->supplier->thirdParty->Email ?? '—' }}</td>
                <td>{{ $item->supplier->thirdParty->Phone ?? '—' }}</td>
                                <td class="text-center">
                                    @canDelete('tender')
                                    <form action="{{ route('initiatetender.update', $tender->Id) }}" method="POST"
                                          style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                    <input type="hidden" name="type" value='crudSupplier'>
                                        <input type="hidden" name="crudType" value='deleteSupplier'>
                    <input type="hidden" name="supplier_id" value="{{$item->Id}}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                        onclick="return confirm('Are you sure you want to delete Supplier name: \'{{ $item->supplier->thirdParty->TradingName ?? $item->supplier->thirdParty->ThirdPartyName ?? 'Supplier' }}\'? This action cannot be undone.')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @endcanDelete
                                </td>
                            </tr>
                        @endforeach
                        <!-- More suppliers -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @endif

        </div>


        <!-- Add Item Modal -->
        <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content rounded-3 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemModalLabel">Add New Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('initiatetender.update', $tender->Id) }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="type" value='crudItem'>
                        <input type="hidden" name="crudType" value='addItem'>
                        <input type="hidden" name="itemCategoryID" value="{{$itemCategoryID}}">

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="item_id" class="form-label fw-bold">Item</label>
                                <select name="item_id" id="item_id" class="form-select" required>
                                    <option selected>-- Select Item --</option>
                                    @foreach($otherItemsForThatTender as $item)
                                        <option value="{{ $item->Id }}">{{ $item->ItemName }}</option>
                            @endforeach
                        </select>
                                @error('item_id')
                                <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                            <div class="mb-3">
                                <label for="QtyToTender" class="form-label fw-bold">Quantity to Tender</label>
                                <input type="number" name="QtyToTender" id="QtyToTender" class="form-control" required>
                                @error('QtyToTender')
                                <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                            <div class="mb-3">
                                <label for="Document" class="form-label fw-bold">Upload Specs Document</label>
                                <input type="file" name="Document" id="Document" class="form-control">
                                @error('Document')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="PRNumber" class="form-label fw-bold">PR Number
                                    <small>(Optional)</small></label>
                                <input type="text" name="pr_ref" id="PRNumber" class="form-control"
                                       placeholder="PR/2025/xxx">
                                @error('PRNumber')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                    </div>
                </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel
                            </button>
                            <button
                                type="submit"
                                class="btn btn-success"
                                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();"
                            >
                                Add Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Edit Item Modal -->
        @foreach ($items as $item)
            <div class="modal fade" id="editItemModal-{{$item->id}}" tabindex="-1" aria-labelledby="addItemModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content rounded-3 shadow">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addItemModalLabel">Edit Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="item_id" class="form-label">Item</label>
                                    <select name="item_id" id="item_id" class="form-select" required>
                                        <option>-- Select Item --</option>
                                        @foreach($otherItemsForThatTender as $item1)
                                            <option
                                                value="{{ $item1->Id }}" {{$item1->ID==$item->itemID?'selected':''}}>{{ $item1->ItemName }}</option>
                            @endforeach
                        </select>
                    </div>

                                <div class="mb-3">
                                    <label for="QtyToTender" class="form-label">Quantity to Tender</label>
                                    <input type="number" name="QtyToTender" value="{{$item->QtyToTender}}"
                                           id="QtyToTender" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label for="Document" class="form-label">Upload Specs Document</label>
                                    <input type="file" name="Document" id="Document" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="PRNumber" class="form-label">PR Number</label>
                                    <input type="text" name="PRNumber" value="{{$item->RelatedPRID}}" id="PRNumber"
                                           class="form-control" placeholder="PR/2025/xxx" required>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel
                                </button>
                                <button type="submit" class="btn btn-success">Edit Item</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach



        <!-- Add Supplier based on categoryfilter Modal -->
        <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addItemModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content rounded-3 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemModalLabel">Add Supplier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('initiatetender.update', $tender->Id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="type" value='crudSupplier'>
                        <input type="hidden" name="crudType" value='addSupplier'>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="item_id" class="form-label">Select Supplier</label>
                                <select name="supplier_id" id="supplier_id" class="form-select" required>
                                    <option selected disabled>-- Select Supplier --</option>
                                    @foreach($otherSuppliers as $item)
                                        <option value="{{ $item->Id }}">{{ $item->SupplierName }}
                                            | {{ $item->ContactPhone }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel
                            </button>
                            @canUpdate('tender')
                            <button type="submit" class="btn btn-success">Add Supplier</button>
                            @endcanUpdate
                        </div>
                    </form>
                </div>
            </div>
        </div>


@endsection
@push('scripts')
<script>
(function(){
  const tenderCatSel = document.getElementById('tenderCategory');
  const itemCatSel   = document.getElementById('itemCategory');

  async function refreshItemCategories(){
    const catId = tenderCatSel && tenderCatSel.value ? tenderCatSel.value : '';
    if (!catId) { return; }
    const url = `{{ route('initiatetender.allowedCategories') }}` + `?tender_category_id=${encodeURIComponent(catId)}`;
    try{
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      if(!data.ok) return;
      const current = itemCatSel.value;
      itemCatSel.innerHTML = '<option value="" disabled selected>-- Select Category --</option>';
      (data.categories || []).forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.Id; opt.textContent = c.Name;
        if (String(c.Id) === String(current)) opt.selected = true;
        itemCatSel.appendChild(opt);
      });
    }catch(e){ /* ignore */ }
  }

  if (tenderCatSel) {
    tenderCatSel.addEventListener('change', refreshItemCategories);
    if (tenderCatSel.value) { refreshItemCategories(); }
  }
})();
</script>
@endpush
