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

    <form action="{{ route('inventoryholdreview.store') }}" method="POST" id="reviewForm">
        @csrf

        <div class="mb-3">
            <label for="InventoryHoldID" class="form-label">Select Item to Review</label>
            <select name="InventoryHoldID" id="InventoryHoldID" class="form-control" required>
                <option value="">-- Select --</option>
                @foreach($holds as $hold)
                    <option value="{{ $hold->Id }}" {{ old('InventoryHoldID') == $hold->Id ? 'selected' : '' }}>
                        {{ $hold->InventoryHoldID }}
                    </option>
                @endforeach
            </select>
        </div>

        <input type="hidden" name="ItemID" id="ItemID_hidden">
        <input type="hidden" name="FromBranch" id="FromBranch_hidden">
        <input type="hidden" name="Quantity" id="Quantity_hidden">
        <input type="hidden" name="Id" id="Id_hidden">

        <div id="hold-details" class="bg-light p-3 rounded d-none">
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Item Name</label>
                    <input type="text" class="form-control" id="ItemName" disabled>
                </div>

                <div class="col">
                    <label class="form-label">Quantity</label>
                    <input type="text" class="form-control" id="Quantity" disabled>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">From Branch</label>
                    <input type="text" class="form-control" id="FromBranch" disabled>
                </div>
                <div class="col">
                    <label class="form-label">Store</label>
                    <input type="text" class="form-control" id="Store" disabled>
                </div>
                <div class="col">
                    <label class="form-label">Defect</label>
                    <input type="text" class="form-control" id="Defect" disabled>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="Condition" class="form-label">Condition</label>
            <select name="Condition" id="Condition" class="form-control" required>
                <option value="">-- Select Condition --</option>
                @php
                    $conditionOptions = \App\Models\Core\CodeDetail::where('CodeID', 'DefectsCondition')->get();
                @endphp
                @foreach($conditionOptions as $option)
                    <option value="{{ $option->ID }}" {{ old('Condition') == $option->ID ? 'selected' : '' }}>
                        {{ $option->Description }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="Notes" class="form-label">Review Notes</label>
            <textarea name="Notes" id="Notes" class="form-control" rows="3">{{ old('Notes') }}</textarea>
        </div>

        <button type="button" class="btn btn-danger" id="disposeBtn">Dispose</button>
        <!-- <button type="button" class="btn btn-warning" id="repairBtn">Mark as Repair</button> -->
        <!--<button type="button" class="btn btn-info" id="returnBtn">Return to Sender</button>-->
    </form>
</div>

<script>
document.getElementById('InventoryHoldID').addEventListener('change', function () {
    const holdId = this.value;
    if (!holdId) return;

    fetch(`/inventory/inventoryholdreview/${holdId}/details`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('ItemName').value = data.ItemName || '';
            document.getElementById('Quantity').value = data.Quantity || '';
            document.getElementById('FromBranch').value = data.FromBranch || '';
            document.getElementById('Store').value = data.Store || '';
            document.getElementById('Defect').value = data.Defect || ''; // Should be description from backend
            document.getElementById('ItemID_hidden').value = data.ItemID || '';
            document.getElementById('FromBranch_hidden').value = data.BranchID || '';
            document.getElementById('Quantity_hidden').value = data.Quantity || '';
            document.getElementById('Id_hidden').value = holdId;
            document.getElementById('hold-details').classList.remove('d-none');
        });
});

document.getElementById('disposeBtn').addEventListener('click', function () {
    submitAction('dispose');
});
document.getElementById('repairBtn')?.addEventListener('click', function () {
    submitAction('repair');
});
document.getElementById('returnBtn').addEventListener('click', function () {
    submitAction('return');
});

function submitAction(action) {
    const holdId = document.getElementById('InventoryHoldID').value;
    if (!holdId) {
        alert('Please select an item to review.');
        return;
    }
    const form = document.getElementById('reviewForm'); 
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';        
    actionInput.name = 'Action';    
    actionInput.value = action;

    form.appendChild(actionInput);
    form.submit();          
}
</script>
@endsection
