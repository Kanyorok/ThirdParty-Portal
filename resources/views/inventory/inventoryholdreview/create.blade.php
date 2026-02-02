@php use App\Models\Core\Approval\CodeDetail; @endphp
@extends('layouts.app')

@section('title', 'Inventory Review')

@section('content')
    <div class="container bg-white p-4 rounded shadow">
        <h4>Review Inventory Defects</h4>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('inventoryholdreview.store') }}" method="POST" id="reviewForm">
            @csrf

            <div class="mb-3">
                <label for="InventoryHoldID" class="form-label">Select Item to Review</label>
                <select name="InventoryHoldID" id="InventoryHoldID" class="form-control" required>
                    <option value="">-- Select --</option>
                    @foreach($holds as $hold)
                        @php
                            $displayText = $hold->InventoryHoldID;
                            if ($hold->sourceDetail) {
                                $sourceType = $hold->sourceDetail->Description ?? '';
                                if ($sourceType === 'Transaction Transfer' && $hold->fromBranch) {
                                    $displayText .= ' (' . $hold->fromBranch->Name . ' → ' . ($hold->branch->Name ?? 'Current') . ')';
                                } else {
                                    $displayText .= ' (' . $sourceType . ')';
                                }
                            }
                        @endphp
                        <option value="{{ $hold->Id }}" 
                                data-itemid="{{ $hold->ItemID }}"
                                data-branchid="{{ $hold->BranchID }}"
                                data-quantity="{{ $hold->Quantity }}"
                                data-frombranch="{{ $hold->fromBranch->Name ?? '' }}"
                                data-currentbranch="{{ $hold->branch->Name ?? '' }}"
                                data-sourcetype="{{ $hold->sourceDetail->Description ?? '' }}"
                                data-store="{{ $hold->store->StoreName ?? '' }}"
                                 @foreach($reviews as $review)
                                data-defect="{{ $review->defectDetail->Description ?? $review->Reason }}"
                                @endforeach
                                data-itemname="{{ $hold->item->ItemName ?? '' }}"
                                {{ old('InventoryHoldID') == $hold->Id ? 'selected' : '' }}>
                            {{ $displayText }}
                        </option>
                    @endforeach
                </select>
            </div>

            <input type="hidden" name="ItemID" id="ItemID_hidden">
            <input type="hidden" name="FromBranch" id="FromBranch_hidden">
            <input type="hidden" name="Quantity" id="Quantity_hidden">
            <input type="hidden" name="Id" id="Id_hidden">

            <div id="hold-details" class="bg-light p-3 rounded d-none">
                <h6 class="mb-3">Item Details</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="ItemName" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Quantity</label>
                        <input type="text" class="form-control" id="Quantity" disabled>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Source / Transfer Path</label>
                        <input type="text" class="form-control" id="SourceDisplay" disabled>
                    </div>
                    
                    <div class="col-md-6" id="store-col" style="display: none;">
                        <label class="form-label">Store</label>
                        <input type="text" class="form-control" id="Store" disabled>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label">Defect Reason</label>
                        <input type="text" class="form-control" id="Defect" disabled>
                    </div>
                </div>
            </div>

            <div class="mb-3 mt-4">
                <label for="Condition" class="form-label">Condition Assessment</label>
                <select name="Condition" id="Condition" class="form-control" required>
                    <option value="">-- Select Condition --</option>
                    @php
                        $conditionOptions = CodeDetail::where('CodeID', 'DefectsCondition')->get();
                    @endphp
                    @foreach($conditionOptions as $option)
                        <option value="{{ $option->ID }}" {{ old('Condition') == $option->ID ? 'selected' : '' }}>
                            {{ $option->Description }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Select the current condition of the defective item</div>
            </div>

            <div class="mb-4">
                <label for="Notes" class="form-label">Review Notes</label>
                <textarea name="Notes" id="Notes" class="form-control" rows="3" 
                          placeholder="Add any additional notes about the item's condition or disposal...">{{ old('Notes') }}</textarea>
                <div class="form-text">Optional notes for the review record</div>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-danger" id="disposeBtn">
                    <i class="fas fa-trash"></i> Dispose Item
                </button>
                <!--
                <button type="button" class="btn btn-warning" id="repairBtn">
                    <i class="fas fa-tools"></i> Mark for Repair
                </button>
                -->
                <button type="button" class="btn btn-info" id="returnBtn">
                    <i class="fas fa-undo"></i> Return to Sender
                </button>
            </div>
            
            <div class="mt-3">
                <small class="text-muted">
                    <strong>Actions:</strong> 
                    <span class="text-danger">Dispose</span> - Permanently remove defective item | 
                    <span class="text-info">Return to Sender</span> - Send back to originating branch
                </small>
            </div>
        </form>
    </div>

    <script>
    document.getElementById('InventoryHoldID').addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        
        if (!this.value) {
            document.getElementById('hold-details').classList.add('d-none');
            document.getElementById('returnBtn').disabled = true;
            return;
        }

        // Get data from data attributes
        const itemName = selectedOption.getAttribute('data-itemname');
        const quantity = selectedOption.getAttribute('data-quantity');
        const fromBranch = selectedOption.getAttribute('data-frombranch');
        const currentBranch = selectedOption.getAttribute('data-currentbranch');
        const sourceType = selectedOption.getAttribute('data-sourcetype');
        const store = selectedOption.getAttribute('data-store');
        const defect = selectedOption.getAttribute('data-defect');
        const itemId = selectedOption.getAttribute('data-itemid');
        const branchId = selectedOption.getAttribute('data-branchid');

        // Populate the form fields
        document.getElementById('ItemName').value = itemName || '';
        document.getElementById('Quantity').value = quantity || '';
        document.getElementById('Store').value = store || '';
        document.getElementById('Defect').value = defect || '';
        
        // Determine source display
        let sourceDisplay = '';
        if (sourceType === 'Transaction Transfer' && fromBranch) {
            sourceDisplay = `${fromBranch} → ${currentBranch || 'Current Branch'}`;
        } else {
            sourceDisplay = sourceType || fromBranch || 'N/A';
        }
        document.getElementById('SourceDisplay').value = sourceDisplay;

        // Set hidden fields
        document.getElementById('ItemID_hidden').value = itemId || '';
        document.getElementById('FromBranch_hidden').value = branchId || '';
        document.getElementById('Quantity_hidden').value = quantity || '';
        document.getElementById('Id_hidden').value = this.value;

        // Show details section
        document.getElementById('hold-details').classList.remove('d-none');

        // 🔒 Disable or hide "Return to Sender" if it's an Adjustment
        const returnBtn = document.getElementById('returnBtn');
        if (sourceType && sourceType.toLowerCase().includes('adjustment')) {
            returnBtn.disabled = true;
            returnBtn.classList.add('btn-secondary');
            returnBtn.classList.remove('btn-info');
            returnBtn.innerHTML = '<i class="fas fa-ban"></i> Return To Sender';
        } else {
            returnBtn.disabled = false;
            returnBtn.classList.remove('btn-secondary');
            returnBtn.classList.add('btn-info');
            returnBtn.innerHTML = '<i class="fas fa-undo"></i> Return to Sender';
        }
    });

    // Action button handlers
    document.getElementById('disposeBtn').addEventListener('click', function () {
        if (confirm('Are you sure you want to dispose this item? This action cannot be undone.')) {
            submitAction('dispose');
        }
    });

    document.getElementById('repairBtn')?.addEventListener('click', function () {
        if (confirm('Mark this item for repair?')) {
            submitAction('repair');
        }
    });

    document.getElementById('returnBtn').addEventListener('click', function () {
        if (this.disabled) return; // Prevent disabled button action
        if (confirm('Return this item to the sender?')) {
            submitAction('return');
        }
    });

    function submitAction(action) {
        const holdId = document.getElementById('InventoryHoldID').value;
        if (!holdId) {
            alert('Please select an item to review.');
            return;
        }

        // Validate required fields for dispose action
        if (action === 'dispose') {
            const condition = document.getElementById('Condition').value;
            if (!condition) {
                alert('Please select a condition before disposing the item.');
                document.getElementById('Condition').focus();
                return;
            }
        }

        const form = document.getElementById('reviewForm');
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'Action';
        actionInput.value = action;

        form.appendChild(actionInput);
        form.submit();
    }

    // Initialize form if there's a previously selected value
    document.addEventListener('DOMContentLoaded', function() {
        const holdSelect = document.getElementById('InventoryHoldID');
        if (holdSelect.value) {
            holdSelect.dispatchEvent(new Event('change'));
        }
    });
</script>

@endsection