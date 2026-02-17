@extends('layouts.app')

@section('title', 'Edit Stock Consumption')

@section('content')
<div class="container">
    <form action="{{ route('stockconsumption.update', $consumption->Id) }}" method="POST" id="consumptionForm">
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

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Branch <span class="text-danger">*</span></label>
                <input type="text" class="form-control" value="{{ $branch->Name }}" readonly>
                <input type="hidden" name="BranchID" value="{{ $branch->Id }}">
            </div>

            <div class="col-md-6">
                <label for="StoreID" class="form-label">Store <span class="text-danger">*</span></label>
                <select class="form-select @error('StoreID') is-invalid @enderror" name="StoreID" id="StoreID" required>
                    <option value="">Select Store</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->Id }}" 
                            {{ old('StoreID', $consumption->StoreID) == $store->Id ? 'selected' : '' }}>
                            {{ $store->StoreName }}
                        </option>
                    @endforeach
                </select>
                @error('StoreID')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="ItemID" class="form-label">Item <span class="text-danger">*</span></label>
                <select class="form-select @error('ItemID') is-invalid @enderror" name="ItemID" id="ItemID" required>
                    <option value="">Select Item</option>
                    @foreach($items as $stockItem)
                        @php
                            $itemAvailableQty = $stockItem->CurrentQty;
                            if ($stockItem->ItemID == $consumption->ItemID) {
                                $itemAvailableQty += $consumption->Quantity;
                            }
                        @endphp
                        <option value="{{ $stockItem->ItemID }}" 
                            data-uom="{{ $stockItem->uom->Code ?? '' }}"
                            data-uom-id="{{ $stockItem->UOM }}"
                            data-currentqty="{{ $itemAvailableQty }}"
                            {{ old('ItemID', $consumption->ItemID) == $stockItem->ItemID ? 'selected' : '' }}>
                            {{ $stockItem->item->ItemName ?? 'Unknown Item' }}
                        </option>
                    @endforeach
                </select>
                @error('ItemID')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="Quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('Quantity') is-invalid @enderror" 
                       name="Quantity" id="Quantity" value="{{ old('Quantity', $consumption->Quantity) }}"
                       step="0.01" min="0.01" required>
                @error('Quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                <input type="text" id="UOM_Display" class="form-control" 
                       value="{{ $consumption->uom->Code ?? '' }}" readonly>
                <input type="hidden" name="UOM" id="UOM" value="{{ old('UOM', $consumption->UOM) }}">
                @error('UOM')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="IssuedToType" class="form-label">Issued To Type <span class="text-danger">*</span></label>
                <select name="IssuedToType" id="IssuedToType" class="form-select @error('IssuedToType') is-invalid @enderror" required>
                    <option value="">Select Option</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->ID }}" 
                            {{ old('IssuedToType', $consumption->IssuedToType) == $type->ID ? 'selected' : '' }}>
                            {{ $type->Description }}
                        </option>
                    @endforeach
                </select>
                @error('IssuedToType')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="IssuedToID" class="form-label">Issued To <span class="text-danger">*</span></label>
                <select name="IssuedToID" id="IssuedToID" class="form-select @error('IssuedToID') is-invalid @enderror" required>
                    <option value="">Select based on type</option>
                    @if($consumption->IssuedToType)
                        @php
                            $type = \App\Models\Core\Approval\CodeDetail::find($consumption->IssuedToType);
                            $typeName = $type ? strtoupper($type->Description) : '';
                        @endphp
                        
                        @if($typeName === 'EMPLOYEE')
                            @foreach($employees as $employee)
                                <option value="{{ $employee['id'] }}" 
                                    {{ old('IssuedToID', $consumption->IssuedToID) == $employee['id'] ? 'selected' : '' }}>
                                    {{ $employee['name'] }}
                                </option>
                            @endforeach
                        @elseif($typeName === 'DEPARTMENT')
                            @foreach($departments as $department)
                                <option value="{{ $department['id'] }}" 
                                    {{ old('IssuedToID', $consumption->IssuedToID) == $department['id'] ? 'selected' : '' }}>
                                    {{ $department['name'] }}
                                </option>
                            @endforeach
                        @endif
                    @endif
                </select>
                @error('IssuedToID')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Options will filter based on selected type</small>
            </div>
        </div>

         <div class="row mb-3">
            <div class="col-md-6">
                <label for="IssuedBy" class="form-label">Issued By <span class="text-danger">*</span></label>
                <div class="input-group">
                    @php
                        $currentUser = Auth::user();
                        $employee = $currentUser->employee;
                        $displayName = ($employee ? $employee->FirstName . ' ' . $employee->LastName : $currentUser->UserName);
                        if ($employee && $employee->EmployeeID) {
                            $displayName .= ' (' . $employee->EmployeeID . ')';
                        } else {
                            $displayName .= ' (' . $currentUser->UserName . ')';
                        }
                    @endphp
                    <input type="text" class="form-control bg-light" value="{{ $displayName }}" readonly>
                    <input type="hidden" name="IssuedBy" id="IssuedBy" value="{{ $currentUser->Id }}">
                </div>
                <small class="text-muted">Auto-populated with logged-in user</small>
                @error('IssuedBy')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>


            <div class="col-md-6">
                <label for="IssuedOn" class="form-label">Issued On <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('IssuedOn') is-invalid @enderror" 
                       name="IssuedOn" id="IssuedOn" 
                       value="{{ old('IssuedOn', \Carbon\Carbon::parse($consumption->IssuedOn)->format('Y-m-d')) }}" 
                       required>
                @error('IssuedOn')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small class="text-muted">Select the date when stock was issued</small>
            </div>
        </div>

        <div class="mb-3">
            <label for="Remarks" class="form-label">Remarks</label>
            <textarea class="form-control @error('Remarks') is-invalid @enderror" name="Remarks" id="Remarks" rows="3">{{ old('Remarks', $consumption->Remarks) }}</textarea>
            @error('Remarks')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('stockconsumption.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        const currentUserId = {{ Auth::id() }};
        const consumptionId = {{ $consumption->Id }};
        const oldItemID = '{{ old('ItemID', $consumption->ItemID) }}';
        const oldIssuedToType = '{{ old('IssuedToType', $consumption->IssuedToType) }}';
        const oldIssuedToID = '{{ old('IssuedToID', $consumption->IssuedToID) }}';
        
        const currentStoreId = '{{ $consumption->StoreID }}';
        
        let availableQty = 0;
        let originalQty = parseFloat('{{ $consumption->Quantity }}') || 0;
        let originalItemId = '{{ $consumption->ItemID }}';
        let itemsLoaded = false; 

        function loadItems(storeId, selectedItemId) {
            let itemSelect = $('#ItemID');
            itemSelect.empty().append('<option value="">Loading...</option>');
            
            $.ajax({
                url: '{{ route("stockconsumption.getItems") }}',
                type: 'GET',
                data: {StoreID: storeId},
                success: function (data) {
                    itemSelect.empty().append('<option value="">Select Item</option>');
                    
                    console.log('Loading items for store:', storeId);
                    console.log('Selected item ID:', selectedItemId);
                    console.log('Available items:', data);
                    
                    if (data && data.length > 0) {
                        data.forEach(item => {
                            const isSelected = (item.Id == selectedItemId);
                            
                            let itemAvailableQty = parseFloat(item.CurrentQty) || 0;
                            if (item.Id == originalItemId) {
                                itemAvailableQty = itemAvailableQty + originalQty;
                            }
                            
                            const option = new Option(
                                item.ItemName || 'Unknown Item',
                                item.Id,
                                isSelected,
                                isSelected
                            );
                            
                            $(option).data('uom', item.UOMCode || '');
                            $(option).data('uom-id', item.UOM || '');
                            $(option).data('currentqty', itemAvailableQty);
                            
                            itemSelect.append(option);
                            
                            if (isSelected) {
                                availableQty = itemAvailableQty;
                                $('#UOM_Display').val(item.UOMCode || '');
                                $('#UOM').val(item.UOM || '');
                                
                                $('#Quantity').attr('placeholder', `Max: ${availableQty.toFixed(2)}`);
                            }
                        });
                        
                        itemsLoaded = true;
                        
                        if (selectedItemId && !itemSelect.val()) {
                            console.warn('Selected item not found in results:', selectedItemId);
                            itemSelect.prepend(new Option(
                                'Selected Item (Not Found)',
                                selectedItemId,
                                true,
                                true
                            ));
                        }
                    } else {
                        itemSelect.append('<option value="">No items found</option>');
                    }
                    
                    validateQuantity();
                },
                error: function(xhr, status, error) {
                    console.error('Error loading items:', error);
                    itemSelect.empty().append('<option value="">Error loading items</option>');
                    
                    if (selectedItemId) {
                        itemSelect.append(new Option(
                            'Selected Item',
                            selectedItemId,
                            true,
                            true
                        ));
                    }
                }
            });
        }

        function loadIssuedToOptions(type, selectedId) {
            const recipientSelect = $('#IssuedToID');
            
            if (!type) {
                recipientSelect.empty().append('<option value="">Select based on type</option>');
                return;
            }
            
            recipientSelect.empty().append('<option value="">Loading...</option>');
            
            $.ajax({
                url: '{{ route("stockconsumption.getIssuedToOptions") }}',
                type: 'GET',
                data: {type: type},
                success: function (data) {
                    recipientSelect.empty().append('<option value="">Select Recipient</option>');
                    
                    if (data && data.length > 0) {
                        data.forEach(item => {
                            const isSelected = (item.Id == selectedId);
                            recipientSelect.append(new Option(
                                item.Name,
                                item.Id,
                                isSelected,
                                isSelected
                            ));
                        });
                    } else {
                        recipientSelect.append('<option value="">No options available</option>');
                    }
                    
                    validateUsers();
                },
                error: function() {
                    recipientSelect.empty().append('<option value="">Error loading options</option>');
                }
            });
        }

        function validateUsers() {
            const issuedBy = $('#IssuedBy').val();
            const issuedTo = $('#IssuedToID').val();
            
            $('#issued-to-error').remove();
            $('#IssuedToID').removeClass('is-invalid');
            
            if (issuedBy && issuedTo) {
                if (issuedBy == issuedTo) {
                    $('#IssuedToID').addClass('is-invalid');
                    $('#IssuedToID').after('<div id="issued-to-error" class="invalid-feedback">Issued To cannot be the same as Issued By</div>');
                    return false;
                }
            }
            return true;
        }

        function validateQuantity() {
            const currentQty = parseFloat($('#Quantity').val()) || 0;
            const itemId = $('#ItemID').val();
            
            $('#Quantity').removeClass('is-invalid');
            $('#qty-error').remove();
            $('button[type="submit"]').prop('disabled', false);

            if (currentQty > 0 && itemId) {
                const selectedOption = $('#ItemID option:selected');
                const maxQty = parseFloat(selectedOption.data('currentqty')) || 0;
                
                if (currentQty > maxQty) {
                    $('#Quantity').addClass('is-invalid');
                    $('#Quantity').after(`<div id="qty-error" class="invalid-feedback">
                        Quantity exceeds available stock (${maxQty.toFixed(2)}).
                    </div>`);
                    $('button[type="submit"]').prop('disabled', true);
                    return false;
                }
            }
            return true;
        }

      function initializeForm() {
        
        if ($('#ItemID').val()) {
            $('#ItemID').trigger('change');
        }

        if (oldIssuedToType) {
            loadIssuedToOptions(oldIssuedToType, oldIssuedToID);
        }
        
        validateUsers();
        validateQuantity();
}
        
</script>