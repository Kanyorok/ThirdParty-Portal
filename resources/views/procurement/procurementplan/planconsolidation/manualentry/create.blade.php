@extends('layouts.app')
@section('title', 'Add Plan Item To Procurement Plan')
@section('content')

  <div class="card p-4 shadow rounded-4">

    <form action="{{ route('plan.manual-input.store') }}" method="POST">
      @csrf

      <!-- Plan Selection -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Procurement Plan</label>
          <select name="PlanID" class="form-select" disabled>
            <option disabled {{ !isset($selectedPlanId) ? 'selected' : '' }}>Select Plan</option>
            @foreach ($plans as $plan)
              <option value="{{ $plan->PlanID }}"
                {{ isset($selectedPlanId) && $selectedPlanId == $plan->PlanID ? 'selected' : '' }}>
                {{ $plan->Title }}
              </option>
            @endforeach
          </select>
          <!-- Hidden input to submit the selected plan ID -->
          <input type="hidden" name="PlanID" value="{{ $selectedPlanId }}">
        </div>
      </div>


      <!-- Item and Category -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Item Name</label>
          <select name="ItemID" class="form-select" id="item-select" required>
            <option disabled {{ old('ItemID') ? '' : 'selected' }}>Select Item</option>
            @foreach ($items as $item)
              <option value="{{ $item->Id }}" data-uom-name="{{ $item->uom->Name ?? 'N/A' }}"
                data-uom-code="{{ $item->uom->Code ?? '' }}" data-category-id="{{ $item->category->Id ?? '' }}"
                data-uom-id="{{ $item->uom->Id ?? '' }}" data-estimatedprice="{{ $item->price->ActualPrice ?? '' }}"
                data-category-name="{{ $item->category->Name ?? 'N/A' }}"
                {{ old('ItemID') == $item->Id ? 'selected' : '' }}>
                {{ $item->ItemName }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Item Category</label>
          <input type="text" id="display-category" class="form-control" readonly>
          <input type="hidden" name="CategoryID" id="category-id">
        </div>
      </div>

      <!-- Quantity and UOM -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Quantity</label>
          <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
            placeholder="e.g. 10" value="{{ old('quantity') }}" required>
          @error('quantity')
            <div class="alert alert-danger mt-1">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Unit of Measure</label>
          <input type="text" class="form-control" id="display-uom" readonly>
          <input type="hidden" name="unit_of_measure_id" id="unit_of_measure_id">
        </div>
      </div>

      <!-- Estimated Cost and Schedule -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Estimated Unit Cost</label>
          <input type="number" name="estimated_cost" class="form-control @error('estimated_cost') is-invalid @enderror"
            placeholder="e.g. 50000" value="{{ old('estimated_cost') }}" id="estimated-cost" readonly>
          @error('estimated_cost')
            <div class="alert alert-danger mt-1">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Planned Quarter</label>
          <select name="schedule_period" class="form-select @error('schedule_period') is-invalid @enderror" required>
            <option disabled {{ old('schedule_period') ? '' : 'selected' }}>Select Quarter</option>
            <option value="Q1" {{ old('schedule_period') == 'Q1' ? 'selected' : '' }}>Q1</option>
            <option value="Q2" {{ old('schedule_period') == 'Q2' ? 'selected' : '' }}>Q2</option>
            <option value="Q3" {{ old('schedule_period') == 'Q3' ? 'selected' : '' }}>Q3</option>
            <option value="Q4" {{ old('schedule_period') == 'Q4' ? 'selected' : '' }}>Q4</option>
          </select>
          @error('schedule_period')
            <div class="alert alert-danger mt-1">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <!-- Delivery Date and Budget Line -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Expected Delivery Date</label>
          <input type="date" name="expected_delivery_date"
            class="form-control @error('expected_delivery_date') is-invalid @enderror"
            value="{{ old('expected_delivery_date') }}" required>
          @error('expected_delivery_date')
            <div class="alert alert-danger mt-1">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Link to Budget Line</label>
          <select name="budget_line_id" class="form-select @error('budget_line_id') is-invalid @enderror" required>
            <option disabled {{ old('budget_line_id') ? '' : 'selected' }}>Select Budget Line</option>
            @foreach ($budgetLines as $budgetLine)
              <option value="{{ $budgetLine->BudgetLineID }}"
                {{ old('budget_line_id') == $budgetLine->BudgetLineID ? 'selected' : '' }}>
                {{ $budgetLine->Description }}
              </option>
            @endforeach
          </select>
          @error('budget_line_id')
            <div class="alert alert-danger mt-1">{{ $message }}</div>
          @enderror
        </div>
      </div>
  </div>

  <!-- Notes -->
  <div class="row mb-3">
    <div class="col-md-12">
      <label class="form-label">Notes / Justification</label>
      <textarea name="notes" class="form-control" rows="3" placeholder="Add any remarks...">{{ old('notes') }}</textarea>
    </div>
  </div>
  <!-- Submit -->
  <div class="d-flex justify-content-end">
    <button type="reset" class="btn btn-outline-secondary me-2">Clear</button>
    <button type="submit" class="btn btn-primary">➕ Add to Plan</button>
  </div>
  </form>
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    @if ($errors->any())
      @foreach ($errors->all() as $error)
        <div class="toast align-items-center text-white bg-danger border-0 mb-2" role="alert" aria-live="assertive"
          aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              {{ $error }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
              aria-label="Close"></button>
          </div>
        </div>
      @endforeach
    @endif
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const toastElList = [].slice.call(document.querySelectorAll('.toast'))
      toastElList.forEach(function(toastEl) {
        const toast = new bootstrap.Toast(toastEl)
        toast.show()
      });
    });
  </script>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const itemSelect = document.getElementById('item-select');
      const uomDisplay = document.getElementById('display-uom');
      const uomIdInput = document.getElementById('unit_of_measure_id');
      const categoryIdInput = document.getElementById('category-id');
      const categoryDisplay = document.getElementById('display-category');
      const estimatedCostInput = document.getElementById('estimated-cost');

      function updateItemFields() {
        const selected = itemSelect.options[itemSelect.selectedIndex];
        const uomCode = selected.getAttribute('data-uom-code');
        const categoryId = selected.getAttribute('data-category-id');
        const categoryName = selected.getAttribute('data-category-name');
        const uomId = selected.getAttribute('data-uom-id');
        const estimatedPrice = selected.getAttribute('data-estimatedprice');

        uomIdInput.value = uomId || '';
        uomDisplay.value = uomCode || 'N/A';
        categoryIdInput.value = categoryId || '';
        categoryDisplay.value = categoryName || 'N/A';
        estimatedCostInput.value = estimatedPrice || '';
      }

      itemSelect.addEventListener('change', updateItemFields);

      // Trigger it once on load in case item is pre-selected
      if (itemSelect.value) {
        updateItemFields();
      }
    });
  </script>

@endsection
