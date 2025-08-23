@extends('layouts.app')

@section('title', 'Record Stock Consumption')

@section('content')
<div class="container">
    <h4 class="mb-4">Record Stock Consumption</h4>

    <form action="{{ route('stockconsumption.store') }}" method="POST">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="BranchID" class="form-label">Branch</label>
                <select class="form-select @error('BranchID') is-invalid @enderror" name="BranchID" id="BranchID" required>
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->Id }}" {{ old('BranchID') == $branch->Id ? 'selected' : '' }}>
                            {{ $branch->Name }}
                        </option>
                    @endforeach
                </select>
                @error('BranchID')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="StoreID" class="form-label">Store</label>
                <select class="form-select @error('StoreID') is-invalid @enderror" name="StoreID" id="StoreID">
                    <option value="">Select Store</option>
                </select>
                @error('StoreID')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="ItemID" class="form-label">Item</label>
                <select class="form-select @error('ItemID') is-invalid @enderror" name="ItemID" id="ItemID" required>
                    <option value="">Select Item</option>
                    @foreach($items as $item)
                        <option
                            value="{{ $item->Id }}"
                            data-uom="{{ $item->uom->Code ?? 'Not Found' }}"
                            data-uom-id="{{ $item->UOM ?? '' }}"
                            {{ old('ItemID') == $item->Id ? 'selected' : '' }}>
                            {{ $item->ItemName }}
                        </option>
                    @endforeach
                </select>
                @error('ItemID')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="Quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control @error('Quantity') is-invalid @enderror" name="Quantity" id="Quantity" value="{{ old('Quantity') }}" required>
                @error('Quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Unit of Measure</label>
                <input type="text" id="UOM_Display" class="form-control" readonly>
                <input type="hidden" name="UOM" id="UOM" value="{{ old('UOM') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="IssuedToType" class="form-label">Issued To Type</label>
                <select name="IssuedToType" id="IssuedToType" class="form-select @error('IssuedToType') is-invalid @enderror" required>
                    <option value="">Select Option</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->ID }}" {{ old('IssuedToType') == $type->ID ? 'selected' : '' }}>{{ $type->Description }}</option>
                    @endforeach
                </select>
                @error('IssuedToType')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="IssuedToID" class="form-label">Issued To</label>
                <select name="IssuedToID" id="IssuedToID" class="form-select @error('IssuedToID') is-invalid @enderror" required>
                    <option value="">Select Recipient</option>
                </select>
                @error('IssuedToID')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="IssuedBy" class="form-label">Issued By</label>
                <select name="IssuedBy" id="IssuedBy" class="form-select @error('IssuedBy') is-invalid @enderror" required>
                    <option value="">Select User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->Id }}" {{ old('IssuedBy') == $user->Id ? 'selected' : '' }}>{{ $user->Name }}</option>
                    @endforeach
                </select>
                @error('IssuedBy')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="IssuedOn" class="form-label">Issued On</label>
                <input type="date" name="IssuedOn" id="IssuedOn" class="form-control @error('IssuedOn') is-invalid @enderror" value="{{ old('IssuedOn', now()->format('Y-m-d')) }}" required>
                @error('IssuedOn')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="Remarks" class="form-label">Remarks</label>
                <textarea class="form-control @error('Remarks') is-invalid @enderror" name="Remarks" id="Remarks" rows="3">{{ old('Remarks') }}</textarea>
                @error('Remarks')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
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
        const oldBranchID = '{{ old('BranchID') }}';
        const oldStoreID = '{{ old('StoreID') }}';
        const oldItemID = '{{ old('ItemID') }}';
        const oldIssuedToType = '{{ old('IssuedToType') }}';
        const oldIssuedToID = '{{ old('IssuedToID') }}';

        function loadStores(branchId, oldStoreId) {
            const storeSelect = $('#StoreID');
            storeSelect.empty().append('<option value="">Select Store</option>');
            if (!branchId) return;

            $.ajax({
                url: '{{ route("stockconsumption.getStores") }}',
                type: 'GET',
                data: { BranchID: branchId },
                beforeSend: function() {
                    storeSelect.empty().append('<option>Loading...</option>');
                },
                success: function(data) {
                    storeSelect.empty().append('<option value="">Select Store</option>');
                    if (data.length === 0) {
                        storeSelect.append('<option value="">No Stores Found</option>');
                    } else {
                        data.forEach(store => {
                            storeSelect.append(`<option value="${store.Id}" ${store.Id == oldStoreId ? 'selected' : ''}>${store.StoreName}</option>`);
                        });
                    }
                },
                error: function() {
                    storeSelect.empty().append('<option value="">Error loading stores</option>');
                }
            });
        }

        function loadIssuedToOptions(type, oldIssuedToId) {
            const recipientSelect = $('#IssuedToID');
            recipientSelect.empty().append('<option value="">Select Recipient</option>');
            if (!type) return;

            $.ajax({
                url: '{{ route("stockconsumption.getIssuedToOptions") }}',
                type: 'GET',
                data: { type: type },
                beforeSend: function() {
                    recipientSelect.empty().append('<option>Loading...</option>');
                },
                success: function(data) {
                    recipientSelect.empty().append('<option value="">Select Recipient</option>');
                    if (data.length > 0) {
                        data.forEach(function (item) {
                            // CORRECTED: Changed 'item.ID' to 'item.Id' to match the controller's JSON response
                            recipientSelect.append(`<option value="${item.Id}" ${item.Id == oldIssuedToId ? 'selected' : ''}>${item.Name}</option>`);
                        });
                    } else {
                        recipientSelect.append('<option value="">No recipients found</option>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                    recipientSelect.empty().append('<option value="">Error loading data</option>');
                }
            });
        }

        // Event listener for Branch change
        $('#BranchID').on('change', function () {
            let branchId = $(this).val();
            loadStores(branchId, null);
        });

        // Event listener for IssuedToType change
        $('#IssuedToType').on('change', function () {
            const type = $(this).val();
            loadIssuedToOptions(type, null);
        });

        // Set UOM display and hidden field based on selected item
        $('#ItemID').on('change', function () {
            let selectedOption = $(this).find('option:selected');
            let uomName = selectedOption.data('uom') || 'Not Found';
            let uomId = selectedOption.data('uom-id') || '';

            $('#UOM_Display').val(uomName);
            $('#UOM').val(uomId);
        });

        // Restore old values on page load
        if (oldBranchID) loadStores(oldBranchID, oldStoreID);
        if (oldIssuedToType) loadIssuedToOptions(oldIssuedToType, oldIssuedToID);
        if (oldItemID) $('#ItemID').trigger('change');
    });
</script>
@endsection
