@extends('layouts.app')

@section('title', 'Record Stock Consumption')

@section('content')
<div class="container">

    <form action="{{ route('stockconsumption.store') }}" method="POST">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Branch <span class="text-danger">*</span></label>
                <input type="text" class="form-control" value="{{ $branch->Name }}" readonly>
                <input type="hidden" name="BranchID" value="{{ $branch->Id }}">
            </div>

            <div class="col-md-6">
                <label for="StoreID" class="form-label">Store <span class="text-danger">*</span></label>
                <select class="form-select" name="StoreID" id="StoreID" required>
                    <option value="">Select Store</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->Id }}" {{ old('StoreID') == $store->Id ? 'selected' : '' }}>
                            {{ $store->StoreName }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="ItemID" class="form-label">Item <span class="text-danger">*</span></label>
                <select class="form-select" name="ItemID" id="ItemID" required>
                    <option value="">Select Item</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="Quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="Quantity" id="Quantity" value="{{ old('Quantity') }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                <input type="text" id="UOM_Display" class="form-control" readonly>
                <input type="hidden" name="UOM" id="UOM" value="{{ old('UOM') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="IssuedToType" class="form-label">Issued To Type <span class="text-danger">*</span></label>
                <select name="IssuedToType" id="IssuedToType" class="form-select" required>
                    <option value="">Select Option</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->ID }}" {{ old('IssuedToType') == $type->ID ? 'selected' : '' }}>
                            {{ $type->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="IssuedToID" class="form-label">Issued To <span class="text-danger">*</span></label>
                <select name="IssuedToID" id="IssuedToID" class="form-select" required>
                    <option value="">Select Recipient</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="IssuedBy" class="form-label">Issued By <span class="text-danger">*</span></label>
                <select name="IssuedBy" id="IssuedBy" class="form-select" required>
                    <option value="">Select User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->Id }}" {{ old('IssuedBy') == $user->Id ? 'selected' : '' }}>
                            {{ $user->Name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="IssuedOn" class="form-label">Issued On <span class="text-danger">*</span></label>
                <input type="date" name="IssuedOn" id="IssuedOn" class="form-control" value="{{ old('IssuedOn', now()->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="Remarks" class="form-label">Remarks</label>
            <textarea class="form-control" name="Remarks" id="Remarks" rows="3">{{ old('Remarks') }}</textarea>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('stockconsumption.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    const oldItemID = '{{ old('ItemID') }}';
    const oldIssuedToType = '{{ old('IssuedToType') }}';
    const oldIssuedToID = '{{ old('IssuedToID') }}';

    let availableQty = 0; 

    function loadItems(storeId, oldItemId) {
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
                                ${item.Id == oldItemId ? 'selected' : ''}>
                            ${item.ItemName}
                        </option>`);
                });
                if (oldItemId) $('#ItemID').trigger('change');
            }
        });
    }

    function loadIssuedToOptions(type, oldIssuedToId) {
        const recipientSelect = $('#IssuedToID');
        recipientSelect.empty().append('<option value="">Loading...</option>');
        $.ajax({
            url: '{{ route("stockconsumption.getIssuedToOptions") }}',
            type: 'GET',
            data: { type: type },
            success: function(data) {
                recipientSelect.empty().append('<option value="">Select Recipient</option>');
                data.forEach(item => {
                    recipientSelect.append(`<option value="${item.Id}" ${item.Id == oldIssuedToId ? 'selected' : ''}>${item.Name}</option>`);
                });
            }
        });
    }

    $('#StoreID').on('change', function () {
        loadItems($(this).val(), null);
    });

    // Item change event
    $('#ItemID').on('change', function () {
        let selectedOption = $(this).find('option:selected');
        $('#UOM_Display').val(selectedOption.data('uom') || '');
        $('#UOM').val(selectedOption.data('uom-id') || '');
        availableQty = parseFloat(selectedOption.data('currentqty')) || 0;
        
        // Reset quantity validation
        $('#Quantity').val('').removeClass('is-invalid'); 
        $('#qty-error').remove(); 
        $('button[type="submit"]').prop('disabled', false);
        
        // Update quantity placeholder with available stock
        $('#Quantity').attr('placeholder', `Max: ${availableQty}`);
    });

    // Issued To Type change event
    $('#IssuedToType').on('change', function () {
        loadIssuedToOptions($(this).val(), null);
    });

    // Real-time quantity validation
    $('#Quantity').on('input', function () {
        let qty = parseInt($(this).val(), 10);
        $(this).removeClass('is-invalid');
        $('#qty-error').remove();
        $('button[type="submit"]').prop('disabled', false);

        if (qty > availableQty) {
            $(this).addClass('is-invalid');
            $(this).after(`<div id="qty-error" class="invalid-feedback">
                Quantity exceeds available stock (${availableQty}).
            </div>`);
            $('button[type="submit"]').prop('disabled', true);
        }
    });

    // Initial loads
    loadItems($('#StoreID').val(), oldItemID);
    if (oldIssuedToType) loadIssuedToOptions(oldIssuedToType, oldIssuedToID);
});
</script>

<style>
.text-danger {
    font-weight: bold;
}
.form-label {
    font-weight: 500;
}
</style>
@endsection