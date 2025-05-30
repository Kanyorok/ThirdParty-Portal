@extends('layouts.app')

@section('title', 'New Inter-Branch Requisition')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container mt-4">
        <h4 class="fw-bold mb-3">🔄 New Inter-Branch Requisition</h4>

        <form action="{{ route('interbranchrequisition.store') }}" method="POST">
            @csrf

            <div class="card shadow">
                <div class="card-header bg-light fw-bold">➕ Request Stock from Another Branch</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Requesting Branch</label>
                            <select name="FromBranch" class="form-select" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">To Branch</label>
                            <select name="ToBranch" class="form-select" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="CreatedOn" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" id="addItemBtn">➕ Add Item</button>
                    </div>

                    <div id="itemsContainer"></div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">📤 Submit Requisition</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <template id="itemTemplate">
        <div class="card mb-3 item-entry">
            <div class="card-body border">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="Category" class="form-select category-select" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->Id }}">{{ $category->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Subcategory</label>
                        <select name="Subcategory" id="Subcategory" class="form-select">
                            <option value="">-- Select Subcategory --</option>
                         </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Item</label>
                        <select name="Item" id="Item" class="form-select" required>
                          <option value="">-- Select Item --</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Item Code</label>
                        <input type="text" name="items[][ItemCode]" class="form-control item-code" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">UOM</label>
                        <select name="UOM" class="form-select" required>
                            @foreach ($uoms as $uom)
                                <option value="{{ $uom->Id }}">{{ $uom->Code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Requested Qty</label>
                        <input type="number" name="RequestedQty" class="form-control" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="Remarks" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </template>

    @push('scripts')
        <script>
            document.getElementById('addItemBtn').addEventListener('click', function () {
                const template = document.getElementById('itemTemplate');
                const clone = template.content.cloneNode(true);
                document.getElementById('itemsContainer').appendChild(clone);
            });

            document.addEventListener('change', function (e) {
                if (e.target.classList.contains('category-select')) {
                    const categoryId = e.target.value;
                    const entry = e.target.closest('.item-entry');
                    const subcategorySelect = entry.querySelector('.subcategory-select');
                    const itemSelect = entry.querySelector('.item-select');
                    const itemCodeInput = entry.querySelector('.item-code');

                    fetch(`/subcategories/${categoryId}`)
                        .then(res => res.json())
                        .then(data => {
                            subcategorySelect.innerHTML = '<option value="">Select</option>';
                            data.forEach(sub => {
                                subcategorySelect.innerHTML += `<option value="${sub.Id}">${sub.CategoryName}</option>`;
                            });
                            subcategorySelect.disabled = false;
                            itemSelect.innerHTML = '';
                            itemSelect.disabled = true;
                            itemCodeInput.value = '';
                        });
                }

                if (e.target.classList.contains('subcategory-select')) {
                    const subcategoryId = e.target.value;
                    const entry = e.target.closest('.item-entry');
                    const itemSelect = entry.querySelector('.item-select');
                    const itemCodeInput = entry.querySelector('.item-code');

                    fetch(`/items/${subcategoryId}`)
                        .then(res => res.json())
                        .then(data => {
                            itemSelect.innerHTML = '<option value="">Select Item</option>';
                            data.forEach(item => {
                                itemSelect.innerHTML += `<option value="${item.Id}" data-code="${item.ItemCode}">${item.ItemCode} - ${item.ItemName}</option>`;
                            });
                            itemSelect.disabled = false;
                            itemCodeInput.value = '';
                        });
                }

                if (e.target.classList.contains('item-select')) {
                    const selectedOption = e.target.selectedOptions[0];
                    const itemCode = selectedOption.getAttribute('data-code') || '';
                    const entry = e.target.closest('.item-entry');
                    const itemCodeInput = entry.querySelector('.item-code');
                    itemCodeInput.value = itemCode;
                }
            });
        </script>
    @endpush
@endsection
