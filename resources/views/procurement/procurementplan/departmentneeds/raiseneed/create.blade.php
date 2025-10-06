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
                    @php
                        // Build a set of item IDs that already have a pending need for this department to hide them
                        $pendingItemIds = \App\Models\Procurement\DepartmentNeed::query()
                            ->where('DepartmentID', auth()->user()->employee->DepartmentId ?? null)
                            ->where('Status', \App\Enums\Procurement\DepartmentNeedsEnum::Pending)
                            ->pluck('ItemID')->toArray();
                    @endphp
                    @foreach ($items as $item)
                        @if(!in_array($item->Id, $pendingItemIds))
                        <option value="{{ $item->Id }}" data-category="{{ $item->category->Name ?? '' }}"
                                data-uom="{{ $item->uom->Name ?? 'N/A' }}"
                                data-price="{{ $item->price->ActualPrice ?? '0.00' }}"
                            {{ old('ItemID') == $item->Id ? 'selected' : '' }}>
                            {{ $item->ItemName }}
                        </option>
                        @endif
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
            <label for="EstimatedUnitCostField" class="form-label">Estimated Unit Cost</label>
            <input type="text" id="EstimatedUnitCostField" class="form-control" readonly>
            <input type="hidden" name="EstimatedUnitCost" id="EstimatedUnitCostHidden"
                   value="{{ old('EstimatedUnitCost') }}">
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
            <input type="date" name="RequestedDate" id="RequestedDate" value="{{ old('RequestedDate') }}" min="{{ now()->toDateString() }}"
                   class="form-control @error('RequestedDate') is-invalid @enderror" required>
            @error('RequestedDate')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Additional fields like Status, FiscalYear, PriorityLevel, IsEmergency can be added here --}}

        <div class="d-flex justify-content-end">
            <button type="reset" class="btn btn-secondary me-2">Clear</button>
            <button type="submit" id="submitBtn" class="btn btn-primary" disabled>Submit Need</button>
        </div>
    </form>
    </div>

@section('scripts')
    <script>
    flatpickr("#RequestedDate", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        allowInput: true,
        minDate: "today"
    });
    document.addEventListener('DOMContentLoaded', function() {
        const itemDropdown = document.getElementById('itemDropdown');
        const categoryField = document.getElementById('categoryField');
        const uomField = document.getElementById('uomField');
        const estimatedCostField = document.getElementById('EstimatedUnitCostField');
        const estimatedCostHidden = document.getElementById('EstimatedUnitCostHidden');
        const submitBtn = document.getElementById('submitBtn');

        const priceErrorId = 'priceErrorMsg';
        function ensurePriceErrorEl() {
            let el = document.getElementById(priceErrorId);
            if (!el) {
                el = document.createElement('div');
                el.id = priceErrorId;
                el.className = 'text-danger mt-2';
                // place right under the Estimated Unit Cost field
                estimatedCostField.parentElement.appendChild(el);
            }
            return el;
        }

        function fillFields() {
            const selected = itemDropdown.options[itemDropdown.selectedIndex];

            // If placeholder (no explicit value or disabled), don't show the warning by default
            if (!selected || !selected.hasAttribute('value') || selected.disabled) {
                categoryField.value = '';
                uomField.value = '';
                estimatedCostField.value = '';
                estimatedCostHidden.value = '';
                submitBtn.disabled = true;
                return;
            }
            categoryField.value = selected.getAttribute('data-category') || '';
            uomField.value = selected.getAttribute('data-uom') || '';
            const priceRaw = selected.getAttribute('data-price') || '';
            const price = parseFloat(priceRaw);
            const hasValidPrice = !isNaN(price) && price > 0;
            estimatedCostField.value = hasValidPrice ? price.toFixed(2) : '';
            estimatedCostHidden.value = hasValidPrice ? price.toFixed(2) : '';

            const msgEl = ensurePriceErrorEl();
            if (!hasValidPrice) {
                msgEl.textContent = 'This item has no estimated cost configured. Please contact procurement or select another item.';
                submitBtn.disabled = true;
            } else {
                msgEl.textContent = '';
                submitBtn.disabled = false;
            }
        }

        // Only pre-fill if a real item (with a value attribute) is preselected (e.g., after validation errors)
        const initSelected = itemDropdown.options[itemDropdown.selectedIndex];
        if (initSelected && initSelected.hasAttribute('value') && !initSelected.disabled) {
            fillFields();
        } else {
            submitBtn.disabled = true;
        }
        itemDropdown.addEventListener('change', fillFields);
    });
    </script>
@endsection

    @if (session('success'))
        <div class="alert alert-success mb-3">
    {{ session('success') }}
        </div>
@endif

    @if ($errors->any())
        <div class="alert alert-danger mb-3">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
        </div>
@endif

@endsection
