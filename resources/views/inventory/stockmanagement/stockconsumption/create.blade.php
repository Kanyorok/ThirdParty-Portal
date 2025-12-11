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
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                        @if($consumption->stockItem)
                            <option value="{{ $consumption->ItemID }}" selected>
                                {{ $consumption->stockItem->item?->ItemName ?? 'N/A' }}
                            </option>
                        @endif
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="Quantity" class="form-label">Quantity</label>
                    <input type="number" step="0.01" class="form-control"
                           name="Quantity" id="Quantity"
                           value="{{ old('Quantity', $consumption->Quantity) }}" required>
                    <small class="text-muted" id="available-qty">Available: <span id="available-qty-value">0</span></small>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Unit of Measure</label>
                    <input type="text" id="UOM_Display" class="form-control" 
                           value="{{ $consumption->uom?->Code ?? $consumption->UOM }}" readonly>
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
                        @if($consumption->IssuedToID && $preSelectedValue)
                            @php
                                $selectedName = '';
                                if(strtoupper($consumption->issuedToTypeDetail?->Description ?? '') == 'EMPLOYEE') {
                                    $employee = \App\Models\HRM\Employee::with('user')
                                        ->whereHas('user', function($q) use ($consumption) {
                                            $q->where('Id', $consumption->IssuedToID);
                                        })
                                        ->first();
                                    if($employee) {
                                        $selectedName = $employee->FirstName . ' ' . $employee->LastName . 
                                                       ($employee->EmployeeID ? ' (' . $employee->EmployeeID . ')' : '');
                                    }
                                } elseif(strtoupper($consumption->issuedToTypeDetail?->Description ?? '') == 'DEPARTMENT') {
                                    $department = \App\Models\HRM\Department::find($consumption->IssuedToID);
                                    $selectedName = $department?->Name ?? '';
                                }
                            @endphp
                            @if($selectedName)
                                <option value="{{ $consumption->IssuedToID }}" selected>
                                    {{ $selectedName }}
                                </option>
                            @endif
                        @endif
                    </select>
                </div>
            </div>

            {{-- Issued By + Date --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="IssuedBy" class="form-label">Issued By</label>
                    <div class="input-group">
                        <input type="text" class="form-control" 
                               value="{{ $issuedByDisplay }}" 
                               id="IssuedByDisplay" readonly>
                        <input type="hidden" name="IssuedBy" 
                               value="{{ $currentUser->Id }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="IssuedOn" class="form-label">Issued On</label>
                    <input type="date" name="IssuedOn" id="IssuedOn" class="form-control"
                           value="{{ old('IssuedOn', $consumption->IssuedOn ? \Carbon\Carbon::parse($consumption->IssuedOn)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                           required>
                </div>
            </div>

            {{-- Remarks --}}
            <div class="mb-3">
                <label for="Remarks" class="form-label">Remarks</label>
                <textarea class="form-control" name="Remarks" id="Remarks"
                          rows="3">{{ old('Remarks', $consumption->Remarks) }}</textarea>
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
    const oldItemID = '{{ old('ItemID', $consumption->ItemID) }}';
    const oldIssuedToType = '{{ old('IssuedToType', $consumption->IssuedToType) }}';
    const oldIssuedToID = '{{ old('IssuedToID', $consumption->IssuedToID) }}';
    const oldStoreID = '{{ old('StoreID', $consumption->StoreID) }}';
    
    let availableQty = 0;

    // Load items for the selected store
    function loadItems(storeId, selectedId) {
        let itemSelect = $('#ItemID');
        itemSelect.empty().append('<option value="">Loading...</option>');
        
        $.ajax({
            url: '{{ route("stockconsumption.getItems") }}',
            type: 'GET',
            data: {StoreID: storeId},
            success: function (data) {
                itemSelect.empty().append('<option value="">Select Item</option>');
                
                if (data.length === 0) {
                    itemSelect.append('<option value="">No items found for this store</option>');
                }
                
                data.forEach(item => {
                    const isSelected = (item.Id == selectedId);
                    itemSelect.append(`
                        <option value="${item.Id}"
                                data-uom="${item.UOMCode}"
                                data-uom-id="${item.UOM}"
                                data-currentqty="${item.CurrentQty}"
                                ${isSelected ? 'selected' : ''}>
                            ${item.ItemName} (Available: ${item.CurrentQty} ${item.UOMCode})
                        </option>`);
                        
                    if (isSelected) {
                        $('#UOM_Display').val(item.UOMCode || '');
                        $('#UOM').val(item.UOM || '');
                        $('#available-qty-value').text(item.CurrentQty || '0');
                        availableQty = item.CurrentQty || 0;
                    }
                });
                
                if (selectedId && !$('#ItemID').val()) {
                    // If the previously selected item isn't in the new list, add it
                    itemSelect.prepend(`<option value="${selectedId}" selected disabled>
                        Previously selected item (may no longer be available)
                    </option>`);
                }
            },
            error: function() {
                itemSelect.empty().append('<option value="">Error loading items</option>');
            }
        });
    }

    // Load issued to options based on type
    function loadIssuedToOptions(type, selectedId) {
        const recipientSelect = $('#IssuedToID');
        recipientSelect.empty().append('<option value="">Loading...</option>');
        
        $.ajax({
            url: '{{ route("stockconsumption.getIssuedToOptions") }}',
            type: 'GET',
            data: {type: type},
            success: function (data) {
                recipientSelect.empty().append('<option value="">Select Recipient</option>');
                
                if (data.length === 0) {
                    recipientSelect.append('<option value="">No options available</option>');
                }
                
                data.forEach(item => {
                    recipientSelect.append(`<option value="${item.Id}" ${item.Id == selectedId ? 'selected' : ''}>${item.Name}</option>`);
                });
                
                if (selectedId && !$('#IssuedToID').val()) {
                    // If the previously selected recipient isn't in the new list, add it
                    recipientSelect.prepend(`<option value="${selectedId}" selected disabled>
                        Previously selected recipient
                    </option>`);
                }
            },
            error: function() {
                recipientSelect.empty().append('<option value="">Error loading recipients</option>');
            }
        });
    }

    // Quantity validation
    $('#Quantity').on('change', function() {
        const enteredQty = parseFloat($(this).val());
        if (enteredQty > availableQty) {
            alert(`Warning: Quantity (${enteredQty}) exceeds available quantity (${availableQty})!`);
        }
    });

    // Store change event
    $('#StoreID').on('change', function () {
        loadItems($(this).val(), oldItemID);
    });

    // Item change event
    $('#ItemID').on('change', function () {
        let selectedOption = $(this).find('option:selected');
        $('#UOM_Display').val(selectedOption.data('uom') || '');
        $('#UOM').val(selectedOption.data('uom-id') || '');
        availableQty = selectedOption.data('currentqty') || 0;
        $('#available-qty-value').text(availableQty);
    });

    // Issued To Type change event
    $('#IssuedToType').on('change', function () {
        loadIssuedToOptions($(this).val(), oldIssuedToID);
    });

    // Initialize form on page load
    function initializeForm() {
        // Load items for the current store
        if (oldStoreID) {
            loadItems(oldStoreID, oldItemID);
        }
        
        // Load issued to options if type is selected
        if (oldIssuedToType) {
            loadIssuedToOptions(oldIssuedToType, oldIssuedToID);
        }
        
        // Set the current available quantity if item is selected
        if (oldItemID) {
            setTimeout(() => {
                const selectedItem = $(`#ItemID option[value="${oldItemID}"]`);
                if (selectedItem.length) {
                    availableQty = selectedItem.data('currentqty') || 0;
                    $('#available-qty-value').text(availableQty);
                }
            }, 500);
        }
    }

    // Call initialization
    initializeForm();
});
</script>
@endsection