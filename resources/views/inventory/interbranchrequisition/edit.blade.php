@extends('layouts.app')

@section('title', 'Edit Inter-Branch Requisition')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-4">
    <form action="{{ route('interbranchrequisition.update', $item->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">✏️ Edit Inter-Branch Requisition</div>

            <div class="card-body">
                <div class="row g-3 mb-3">

                    <div class="col-md-4">
                        <label class="form-label">Requesting Branch <span class="text-danger">*</span></label>
                        <input type="hidden" name="ToBranch" value="{{ $currentBranch->Id }}">
                        <input type="text" class="form-control" value="{{ $currentBranch->Name }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">From Branch <span class="text-danger">*</span></label>
                        <select name="FromBranch" id="FromBranch" class="form-select" required>
                            <option value="">Select Branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->Id }}"
                                    {{ old('FromBranch', $item->FromBranch) == $branch->Id ? 'selected' : '' }}>
                                    {{ $branch->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="hidden" name="CreatedOn"
                               value="{{ old('CreatedOn', \Carbon\Carbon::parse($item->CreatedOn)->format('Y-m-d')) }}">
                        <input type="text" class="form-control"
                               value="{{ \Carbon\Carbon::parse($item->CreatedOn)->format('m/d/Y') }}" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary" id="addItemBtn">➕ Add Item</button>
                </div>

                <div id="itemsContainer"></div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        Update Requisition
                    </button>
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
                    <label class="form-label">Parent Category *</label>
                    <select name="items[__INDEX__][Category]"
                            class="form-select category-select" required>
                        <option value="">-- Select Category --</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select name="items[__INDEX__][Subcategory]"
                            class="form-select subcategory-select">
                        <option value="">-- Select Subcategory --</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Item *</label>
                    <select name="items[__INDEX__][Item]"
                            class="form-select item-select" required>
                        <option value="">-- Select Item --</option>
                    </select>
                    <input type="hidden" name="items[__INDEX__][item_name]" class="item-name-hidden">
                </div>

               <div class="col-md-2">
                    <label class="form-label">Item Code</label>
                    <input type="text" class="form-control item-code" readonly>
                    <input type="hidden" name="items[__INDEX__][ItemCode]" class="item-code-hidden">
                </div>

                <div class="col-md-2">
                    <label class="form-label">UOM</label>
                    <input type="text" class="form-control item-uom" readonly>
                </div>

                <div class="col-md-1">
                    <label class="form-label">Qty *</label>
                    <input type="number" name="items[__INDEX__][RequestedQty]"
                           class="form-control item-qty" min="1" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Remarks</label>
                    <input type="text" name="items[__INDEX__][Remarks]" class="form-control">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn">✖</button>
                </div>

            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
let itemCounter = 0;



function populateCategories(entry, selected = null, cb = null) {
    const fromBranch = document.getElementById('FromBranch').value;
    const cat = entry.querySelector('.category-select');

    cat.innerHTML = '';
    cat.disabled = true;

    if (!fromBranch) {
        cat.innerHTML = '<option value="">Select From Branch</option>';
        return;
    }

    fetch(`/inventory/get-categories-by-branch?from_branch_id=${fromBranch}`)
        .then(r => r.json())
        .then(d => {
            cat.disabled = false;
            cat.innerHTML = '<option value="">-- Select Category --</option>';
            d.categories.forEach(c => {
                const o = new Option(c.Name, c.Id);
                if (c.Id == selected) o.selected = true;
                cat.add(o);
            });
            cb?.();
        });
}

function populateSubcategories(entry, selected = null, cb = null) {
    const catId = entry.querySelector('.category-select').value;
    const fromBranch = document.getElementById('FromBranch').value;
    const sub = entry.querySelector('.subcategory-select');

    sub.innerHTML = '<option value="">-- Select Subcategory --</option>';
    sub.disabled = true;

    if (!catId || !fromBranch) return;

    fetch(`/inventory/get-subcategories-by-branch-and-category?from_branch_id=${fromBranch}&category_id=${catId}`)
        .then(r => r.json())
        .then(d => {
            if (d.subcategories.length === 0) {
                populateItems(entry, null, catId);
                return;
            }
            sub.disabled = false;
            d.subcategories.forEach(s => {
                const o = new Option(s.Name, s.Id);
                if (s.Id == selected) o.selected = true;
                sub.add(o);
            });
            cb?.();
        });
}

function populateItems(entry, selected = null, fallbackCategory = null) {
    const cat = fallbackCategory || entry.querySelector('.category-select').value;
    const sub = entry.querySelector('.subcategory-select').value;
    const fromBranch = document.getElementById('FromBranch').value;
    const itemSel = entry.querySelector('.item-select');

    itemSel.innerHTML = '';
    itemSel.disabled = true;

    let url = `/inventory/get-items?from_branch_id=${fromBranch}`;
    if (sub) url += `&subcategory_id=${sub}`;
    else if (cat) url += `&category_id=${cat}`;
    else return;

    fetch(url)
        .then(r => r.json())
        .then(d => {
            itemSel.disabled = false;
            itemSel.innerHTML = '<option value="">-- Select Item --</option>';
            d.items.forEach(i => {
                const o = new Option(i.ItemName, i.Id);
                if (i.Id == selected) o.selected = true;
                itemSel.add(o);
            });
            if (selected) fetchItemDetails(selected, entry);
        });
}

function fetchItemDetails(id, entry) {
    fetch(`/inventory/items/code/${id}`)
        .then(r => r.json())
        .then(d => {
            entry.querySelector('.item-code').value = d.item_code ?? '';
            entry.querySelector('.item-code-hidden').value = d.item_code ?? ''; 
            entry.querySelector('.item-uom').value = d.item_uom ?? '';
            entry.querySelector('.item-name-hidden').value =
                entry.querySelector('.item-select option:checked')?.text || '';
        });
}



function addItem(values = {}) {
    const tpl = document.getElementById('itemTemplate').content.cloneNode(true);
    tpl.querySelectorAll('[name]').forEach(e => {
        e.name = e.name.replace('__INDEX__', itemCounter);
    });

    document.getElementById('itemsContainer').appendChild(tpl);
    const entry = document.querySelectorAll('.item-entry')[itemCounter];

    populateCategories(entry, values.Category, () => {
        populateSubcategories(entry, values.Subcategory, () => {
            populateItems(entry, values.Item, values.Category);
        });
    });

    entry.querySelector('.item-qty').value = values.RequestedQty ?? 1;
    entry.querySelector('[name$="[Remarks]"]').value = values.Remarks ?? '';

    entry.querySelector('.remove-item-btn').onclick = () => entry.remove();
    itemCounter++;
}



document.getElementById('addItemBtn').onclick = () => addItem();

document.getElementById('FromBranch').addEventListener('change', () => {
    document.querySelectorAll('.item-entry').forEach(e => populateCategories(e));
});

document.addEventListener('change', e => {
    const entry = e.target.closest('.item-entry');
    if (!entry) return;

    if (e.target.classList.contains('category-select')) {
        populateSubcategories(entry);
        populateItems(entry);
    }

    if (e.target.classList.contains('subcategory-select')) {
        populateItems(entry);
    }

    if (e.target.classList.contains('item-select')) {
        fetchItemDetails(e.target.value, entry);
    }
});



@php
$rows = old('items')
    ? old('items')
    : $item->items->map(fn($i) => [
        'Category'     => $i->item->category->ParentId ?? $i->item->category->Id,  
        'Subcategory'  => $i->item->category->ParentId ? $i->item->category->Id : null,  
        'Item'         => $i->Item,
        'RequestedQty' => $i->RequestedQty,
        'Remarks'      => $i->Remarks,
    ]);
@endphp



@foreach ($rows as $row)
    addItem(@json($row));
@endforeach
</script>
@endpush
@endsection
