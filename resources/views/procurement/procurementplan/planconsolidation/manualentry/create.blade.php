@extends('layouts.app')
@section('title', 'Add Plan Item To Procurement Plan')
@section('content')

<div class="card p-4 shadow rounded-4">
  <h4 class="mb-4">➕ Add Line Item to Procurement Plan</h4>

    <form action="{{ route('plan.manual-input.store') }}" method="POST">
        @csrf

    <!-- Plan Selection -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Procurement Plan</label>
        <select name="PlanID" class="form-select" required>
            <option selected disabled>Select Plan</option>
            @foreach($plans as $plan)
                <option value="{{ $plan->PlanID }}">{{ $plan->Title }}</option>
            @endforeach
        </select>
            </div>
    </div>

        <!-- Item and Category -->
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Item Name</label>
          <select name="ItemID" class="form-select" id="item-select" required>
              <option selected disabled>Select Item</option>
              @foreach($items as $item)
                  <option value="{{ $item->Id }}"
                          data-uom="{{ $item->UOM ?? 'N/A' }}"
                          data-category-id="{{ $item->category->Id ?? '' }}"
                          data-category-name="{{ $item->category->Name ?? 'N/A' }}">
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
                       placeholder="e.g. 10" required>
                @error('quantity')
                <div class="alert alert-danger mt-1">{{ $message }}</div>
                @enderror
      </div>
            <div class="col-md-6">
        <label class="form-label">Unit of Measure</label>
                <input type="text" class="form-control" id="display-uom" readonly>
                <input type="hidden" name="unit_of_measure" id="unit_of_measure">
      </div>
    </div>

        <!-- Estimated Cost and Schedule -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Estimated Cost</label>
            <input type="number" name="estimated_cost"
                   class="form-control @error('estimated_cost') is-invalid @enderror" placeholder="e.g. 50000" required>
            @error('estimated_cost')
            <div class="alert alert-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
      <div class="col-md-6">
        <label class="form-label">Planned Quarter</label>
          <select name="schedule_period" class="form-select @error('schedule_period') is-invalid @enderror" required>
              <option selected disabled>Select Quarter</option>
              <option value="Q1">Q1</option>
              <option value="Q2">Q2</option>
              <option value="Q3">Q3</option>
              <option value="Q4">Q4</option>
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
                 class="form-control @error('expected_delivery_date') is-invalid @enderror" required>
          @error('expected_delivery_date')
          <div class="alert alert-danger mt-1">{{ $message }}</div>
          @enderror
      </div>
            <div class="col-md-6">
                <label class="form-label">Link to Budget Line</label>
                <select name="budget_line_id" class="form-select" required>
                    <option selected disabled>Select Budget Line</option>
                    @foreach($budgetLines as $budgetLine)
                        <option value="{{ $budgetLine->BudgetLineID }}">
                            {{ $budgetLine->Description }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Notes -->
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Notes / Justification</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Add any remarks..."></textarea>
            </div>
    </div>

        <!-- Submit -->
    <div class="d-flex justify-content-end">
      <button type="reset" class="btn btn-outline-secondary me-2">Clear</button>
      <button type="submit" class="btn btn-primary">➕ Add to Plan</button>
    </div>
  </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemSelect = document.getElementById('item-select');
        const uomInput = document.getElementById('unit_of_measure');
        const uomDisplay = document.getElementById('display-uom');
        const categoryIdInput = document.getElementById('category-id');
        const categoryDisplay = document.getElementById('display-category');

        itemSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const uom = selected.getAttribute('data-uom');
            const categoryId = selected.getAttribute('data-category-id');
            const categoryName = selected.getAttribute('data-category-name');

            uomInput.value = uom || '';
            uomDisplay.value = uom || 'N/A';

            categoryIdInput.value = categoryId || '';
            categoryDisplay.value = categoryName || 'N/A';
        });
    });
</script>

@endsection
