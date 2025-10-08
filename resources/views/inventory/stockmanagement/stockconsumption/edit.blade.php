@extends('layouts.app')

@section('title', 'Edit Stock Consumption')

@section('content')
<div class="container">
    <h4 class="mb-4">Edit Stock Consumption</h4>

    <form action="{{ route('stockconsumption.update', $consumption->Id) }}" method="POST">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Branch --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Branch</label>
                <input type="text" class="form-control" value="{{ $branch->Name }}" readonly>
                <input type="hidden" name="BranchID" value="{{ $branch->Id }}">
            </div>

            {{-- Store --}}
            <div class="col-md-6">
                <label for="StoreID" class="form-label">Store</label>
                <select class="form-select" name="StoreID" id="StoreID" required>
                    <option value="">Select Store</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->Id }}" 
                            {{ old('StoreID', $consumption->StoreID) == $store->Id ? 'selected' : '' }}>
                            {{ $store->StoreName }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Item, Quantity, UOM --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="ItemID" class="form-label">Item</label>
                <select class="form-select" name="ItemID" id="ItemID" required>
                    <option value="">Select Item</option>
                    {{-- Items will be loaded dynamically via AJAX --}}
                </select>
            </div>

            <div class="col-md-3">
                <label for="Quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" 
                       name="Quantity" id="Quantity" 
                       value="{{ old('Quantity', $consumption->Quantity) }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Unit of Measure</label>
                <input type="text" id="UOM_Display" class="form-control" readonly>
                <input type="hidden" name="UOM" id="UOM" 
                       value="{{ old('UOM', $consumption->UOM) }}">
            </div>
        </div>

        {{-- Issued To --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="IssuedToType" class="form-label">Issued To Type</label>
                <select name="IssuedToType" id="IssuedToType" class="form-select" required>
                    <option value="">Select Option</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->ID }}" 
                            {{ old('IssuedToType', $consumption->IssuedToType) == $type->ID ? 'selected' : '' }}>
                            {{ $type->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="IssuedToID" class="form-label">Issued To</label>
                <select name="IssuedToID" id="IssuedToID" class="form-select" required>
                    <option value="">Select Recipient</option>
                    {{-- Options loaded via AJAX --}}
                </select>
            </div>
        </div>

        {{-- Issued By + Date --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="IssuedBy" class="form-label">Issued By</label>
                <select name="IssuedBy" id="IssuedBy" class="form-select" required>
                    <option value="">Select User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->Id }}" 
                            {{ old('IssuedBy', $consumption->IssuedBy) == $user->Id ? 'selected' : '' }}>
                            {{ $user->Name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="IssuedOn" class="form-label">Issued On</label>
                <input type="date" name="IssuedOn" id="IssuedOn" class="form-control" 
                       value="{{ old('IssuedOn', $consumption->IssuedOn ? \Carbon\Carbon::parse($consumption->IssuedOn)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
            </div>
        </div>

        {{-- Remarks --}}
        <div class="mb-3">
            <label for="Remarks" class="form-label">Remarks</label>
            <textarea class="form-control" name="Remarks" id="Remarks" rows="3">{{ old('Remarks', $consumption->Remarks) }}</textarea>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('stockconsumption.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    const oldItemID      = '{{ old('ItemID', $consumption->ItemID) }}';
    const oldIssuedToType = '{{ old('IssuedToType', $consumption->IssuedToType) }}';
    const oldIssuedToID   = '{{ old('IssuedToID', $consumption->IssuedToID) }}';

    let availableQty = 0;

    function loadItems(storeId, selectedId) {
        let itemSelect = $('#ItemID');
        itemSelect.empty().append('<option value="">Loading...</option>');
        $.ajax({
            url: '{{ route("stockconsumption.getItems") }}',
            type: 'GET',
            data: { StoreID: storeId },
            success: function(data) {
                itemSelect.empty().append('<option value="">Select Item</option>');
                data.forEach(item => {
                    itemSelect.append(`
                        <option value="${item.Id}" 
                                data-uom="${item.UOMCode}" 
                                data-uom-id="${item.UOM}" 
                                data-currentqty="${item.CurrentQty}" 
                                ${item.Id == selectedId ? 'selected' : ''}>
                            ${item.ItemName}
                        </option>`);
                });
                if (selectedId) $('#ItemID').trigger('change');
            }
        });
    }

    function loadIssuedToOptions(type, selectedId) {
        const recipientSelect = $('#IssuedToID');
        recipientSelect.empty().append('<option value="">Loading...</option>');
        $.ajax({
            url: '{{ route("stockconsumption.getIssuedToOptions") }}',
            type: 'GET',
            data: { type: type },
            success: function(data) {
                recipientSelect.empty().append('<option value="">Select Recipient</option>');
                data.forEach(item => {
                    recipientSelect.append(`<option value="${item.Id}" ${item.Id == selectedId ? 'selected' : ''}>${item.Name}</option>`);
                });
            }
        });
    }

    $('#StoreID').on('change', function () {
        loadItems($(this).val(), null);
    });

    $('#ItemID').on('change', function () {
        let selectedOption = $(this).find('option:selected');
        $('#UOM_Display').val(selectedOption.data('uom') || '');
        $('#UOM').val(selectedOption.data('uom-id') || '');
        availableQty = selectedOption.data('currentqty') || 0;
    });

    $('#IssuedToType').on('change', function () {
        loadIssuedToOptions($(this).val(), null);
    });

    // Restore on load
    loadItems($('#StoreID').val(), oldItemID);
    if (oldIssuedToType) loadIssuedToOptions(oldIssuedToType, oldIssuedToID);
    if (oldItemID) $('#ItemID').trigger('change');
});
</script>
@endsection
