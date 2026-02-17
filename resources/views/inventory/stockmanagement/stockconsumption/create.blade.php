@extends('layouts.app')

@section('title', 'Record Stock Consumption')

@section('content')
<div class="container">
    <form action="{{ route('stockconsumption.store') }}" method="POST" id="consumptionForm">
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
                <label class="form-label">Branch <span class="text-danger">*</span></label>
                <input type="text" class="form-control" value="{{ $branch->Name }}" readonly>
                <input type="hidden" name="BranchID" value="{{ $branch->Id }}">
            </div>

            <div class="col-md-6">
                <label for="StoreID" class="form-label">Store <span class="text-danger">*</span></label>
                <select class="form-select @error('StoreID') is-invalid @enderror" name="StoreID" id="StoreID" required>
                    <option value="">Select Store</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->Id }}" {{ old('StoreID') == $store->Id ? 'selected' : '' }}>
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
                </select>
                @error('ItemID')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="Quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('Quantity') is-invalid @enderror" 
                       name="Quantity" id="Quantity" value="{{ old('Quantity') }}"
                       step="0.01" min="0.01" required>
                @error('Quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                <input type="text" id="UOM_Display" class="form-control" readonly>
                <input type="hidden" name="UOM" id="UOM" value="{{ old('UOM') }}">
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
                        <option value="{{ $type->ID }}" {{ old('IssuedToType') == $type->ID ? 'selected' : '' }}>
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
                    @foreach($employees as $employee)
                        <option value="{{ $employee['id'] }}" {{ old('IssuedToID') == $employee['id'] ? 'selected' : '' }}>
                            {{ $employee['name'] }}
                        </option>
                    @endforeach
                    @foreach($departments as $department)
                        <option value="{{ $department['id'] }}" {{ old('IssuedToID') == $department['id'] ? 'selected' : '' }}>
                            {{ $department['name'] }}
                        </option>
                    @endforeach
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
                <div class="input-group">
                    <input type="text" class="form-control bg-light" 
                           value="{{ \Carbon\Carbon::now()->format('F d, Y') }}" 
                           id="IssuedOnDisplay" readonly>
                    <input type="hidden" name="IssuedOn" id="IssuedOn" 
                           value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                </div>
                <small class="text-muted">Auto-set to current date</small>
                @error('IssuedOn')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="Remarks" class="form-label">Remarks</label>
            <textarea class="form-control @error('Remarks') is-invalid @enderror" name="Remarks" id="Remarks" rows="3">{{ old('Remarks') }}</textarea>
            @error('Remarks')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
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
        const currentUserId = {{ Auth::id() }};
        
        const today = new Date();
        const currentDate = today.toISOString().split('T')[0];
        
        const displayDate = today.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        let availableQty = 0;
        let issuedByUserId = currentUserId;

        function loadItems(storeId, oldItemId) {
            let itemSelect = $('#ItemID');
            itemSelect.empty().append('<option value="">Loading...</option>');
            $.ajax({
                url: '{{ route("stockconsumption.getItems") }}',
                type: 'GET',
                data: {StoreID: storeId},
                success: function (data) {
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
                },
                error: function() {
                    itemSelect.empty().append('<option value="">Error loading items</option>');
                }
            });
        }

        function loadIssuedToOptions(type, oldIssuedToId) {
            const recipientSelect = $('#IssuedToID');
            recipientSelect.empty().append('<option value="">Loading...</option>');
            
            $.ajax({
                url: '{{ route("stockconsumption.getIssuedToOptions") }}',
                type: 'GET',
                data: {type: type},
                success: function (data) {
                    recipientSelect.empty().append('<option value="">Select Recipient</option>');
                    
                    if (type && data && data.length > 0) {
                        data.forEach(item => {
                            recipientSelect.append(`<option value="${item.Id}" ${item.Id == oldIssuedToId ? 'selected' : ''}>${item.Name}</option>`);
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
            const issuedBy = issuedByUserId;
            const issuedTo = $('#IssuedToID').val();
            
            $('#issued-to-error').remove();
            $('#IssuedToID').removeClass('is-invalid');
            
            if (issuedTo) {
                if (issuedBy == issuedTo) {
                    $('#IssuedToID').addClass('is-invalid');
                    $('#IssuedToID').after('<div id="issued-to-error" class="invalid-feedback">Issued To cannot be the same as the logged-in user (Issued By)</div>');
                    return false;
                }
            }
            return true;
        }

        $('#StoreID').on('change', function () {
            loadItems($(this).val(), null);
        });

        $('#ItemID').on('change', function () {
            let selectedOption = $(this).find('option:selected');
            $('#UOM_Display').val(selectedOption.data('uom') || '');
            $('#UOM').val(selectedOption.data('uom-id') || '');
            availableQty = parseFloat(selectedOption.data('currentqty')) || 0;

            $('#Quantity').val('').removeClass('is-invalid');
            $('#qty-error').remove();
            $('button[type="submit"]').prop('disabled', false);

            $('#Quantity').attr('placeholder', `Max: ${availableQty}`);
        });

        $('#IssuedToType').on('change', function () {
            loadIssuedToOptions($(this).val(), null);
        });

        $('#IssuedToID').on('change', function () {
            validateUsers();
        });

$('#Quantity').on('input', function () {
    let qty = parseFloat($(this).val());
    $(this).removeClass('is-invalid');
    $('#qty-error').remove();
    $('button[type="submit"]').prop('disabled', false);

    if (isNaN(qty) || qty <= 0) {
        $(this).addClass('is-invalid');
        $(this).after(`<div id="qty-error" class="invalid-feedback">
            Quantity must be greater than 0.
        </div>`);
        $('button[type="submit"]').prop('disabled', true);
        return;
    }

    if (qty > availableQty) {
        $(this).addClass('is-invalid');
        $(this).after(`<div id="qty-error" class="invalid-feedback">
            Quantity exceeds available stock (${availableQty}).
        </div>`);
        $('button[type="submit"]').prop('disabled', true);
    }
});


        $('#consumptionForm').on('submit', function(e) {
            const issuedTo = $('#IssuedToID').val();
            
            if (issuedByUserId == issuedTo) {
                e.preventDefault();
                alert('Error: Issued To cannot be the same as the logged-in user.');
                $('#IssuedToID').focus();
                return false;
            }

            const qty = parseFloat($('#Quantity').val());

            if (isNaN(qty) || qty <= 0) {
                e.preventDefault();
                $('#Quantity').addClass('is-invalid');
                $('#qty-error').remove();
                $('#Quantity').after(`<div id="qty-error" class="invalid-feedback">
                    Quantity must be greater than 0.
                </div>`);
                $('#Quantity').focus();
                return false;
            }

            
            if (qty > availableQty) {
                e.preventDefault();
                alert(`Error: Quantity exceeds available stock (${availableQty}).`);
                $('#Quantity').focus();
                return false;
            }
            
            const selectedDate = $('#IssuedOn').val();
            if (selectedDate !== currentDate) {
                e.preventDefault();
                alert(`Error: Issued date must be today's date (${displayDate}).`);
                return false;
            }
            
            $('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        });

        $(document).on('click', '#IssuedOnDisplay', function(e) {
            e.preventDefault();
            alert('Issued date is automatically set to today and cannot be changed.');
        });

        $(document).on('keydown', '#IssuedOnDisplay', function(e) {
            e.preventDefault();
            return false;
        });

        if ($('#StoreID').val()) {
            loadItems($('#StoreID').val(), oldItemID);
        }
        if (oldIssuedToType) {
            loadIssuedToOptions(oldIssuedToType, oldIssuedToID);
        }
        
        validateUsers();
        
        $('#IssuedOnDisplay').val(displayDate);
    });
</script>

<style>
    .text-danger {
        font-weight: bold;
    }

    .form-label {
        font-weight: 500;
    }
    
    .invalid-feedback {
        display: block;
    }
    
    .spinner-border {
        vertical-align: middle;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    .input-group .form-control[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
    
    .text-muted {
        font-size: 0.875rem;
    }
</style>
@endsection