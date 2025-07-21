@extends('layouts.app')
@section('title', 'Raise Procurement Need')
@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📥 Raise Procurement Need</h4>

    <form action="{{ route('procurementdepartmentalplan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="Status" value="p">
        <input type="hidden" name="PriorityLevel" value="Normal">
        <input type="hidden" name="IsEmergency" value="0">
        <input type="hidden" name="FiscalYear" value="{{ now()->year }}">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="ItemID" class="form-label">Item Name</label>
                <select name="ItemID" id="itemDropdown" class="form-select @error('ItemID') is-invalid @enderror"
                        required>
                    <option disabled selected>Select an item</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->Id }}"
    data-category="{{ $item->category ? $item->category->Name : '' }}"
    data-uom="{{ $item->uom->Name ?? 'N/A' }}"
    data-estimatedprice="{{ $item->price->EstimatedPrice ?? 'N/A' }}"
    {{ old('ItemID') == $item->Id ? 'selected' : '' }}>
    {{ $item->ItemName }}
</option>
                    @endforeach
                </select>
                @error('ItemID')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="categoryField" class="form-label">Item Category</label>
                <input type="text" id="categoryField" class="form-control" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="RequestedQty" class="form-label">Quantity Needed</label>
                <input type="number" name="RequestedQty" id="RequestedQty" value="{{ old('RequestedQty') }}"
                       class="form-control @error('RequestedQty') is-invalid @enderror" placeholder="Enter quantity"
                       required>
                @error('RequestedQty')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="uomField" class="form-label">Unit of Measure</label>
                <input type="text" id="uomField" class="form-control" readonly>
            </div>
        </div>

        <div class="mb-3">
    <label for="EstimatedUnitCost" class="form-label">Estimated Unit Cost</label>
    <input type="number" step="0.01" name="EstimatedUnitCost" id="EstimatedUnitCost"
           value="{{ old('EstimatedUnitCost') }}"
           class="form-control @error('EstimatedUnitCost') is-invalid @enderror"
           placeholder="e.g. 100.50" readonly>
    @error('EstimatedUnitCost')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

        <div class="mb-3">
            <label for="Justification" class="form-label">Justification</label>
            <textarea name="Justification" id="Justification" rows="3"
                      class="form-control @error('Justification') is-invalid @enderror"
                      placeholder="Explain the need...">{{ old('Justification') }}</textarea>
            @error('Justification')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="RequestedDate" class="form-label">Date Needed</label>
            <input type="date" name="RequestedDate" id="RequestedDate" value="{{ old('RequestedDate') }}"
                   class="form-control @error('RequestedDate') is-invalid @enderror" required>
            @error('RequestedDate')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Additional fields like Status, FiscalYear, PriorityLevel, IsEmergency can be added here --}}

        <div class="d-flex justify-content-end">
            <button type="reset" class="btn btn-secondary me-2">Clear</button>
            <button type="submit" class="btn btn-primary">Submit Need</button>
        </div>
    </form>
</div>

@section('scripts')
    <script>
        flatpickr("#RequestedDate", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            allowInput: true
        });
        document.addEventListener('DOMContentLoaded', function () {
            const itemDropdown = document.getElementById('itemDropdown');
            const categoryField = document.getElementById('categoryField');
            const uomField = document.getElementById('uomField');
            const estimatedUnitCostField = document.getElementById('EstimatedUnitCost');

            itemDropdown.addEventListener('change', function () {
                const selectedOption = itemDropdown.options[itemDropdown.selectedIndex];
                categoryField.value = selectedOption.dataset.category || '';
                uomField.value = selectedOption.dataset.uom || '';
                estimatedUnitCostField.value = selectedOption.dataset.estimatedprice || '';
            });

            // Trigger change event on page load to set initial values
            itemDropdown.dispatchEvent(new Event('change'));
        });
    </script>
@endsection

@if(session('success'))
    <div class="alert alert-success mb-3">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@endsection
