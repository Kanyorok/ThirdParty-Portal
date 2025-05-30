@extends('layouts.app')
@section('title', '')
@section('content')

<div class="card p-4 shadow rounded-4">
  <h4 class="mb-4">➕ Add Line Item to Procurement Plan</h4>

    <form action="{{ route('plan.manual-input.store') }}" method="POST">
        @csrf

    <!-- Plan Selection -->
    <div class="mb-3">
      <label class="form-label">Procurement Plan</label>
        <select name="PlanID" class="form-select" required>
        <option selected disabled>Select Plan</option>
            @foreach($plans as $plan)
                <option value="{{ $plan->PlanID }}">{{ $plan->Title }}</option>
            @endforeach
      </select>
    </div>

    <!-- Item Details -->
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Item Name</label>
          <select name="ItemID" class="form-select" required>
              <option selected disabled>Select Item</option>
              @foreach($items as $item)
                  <option value="{{ $item->Id }}">{{ $item->ItemName }}</option>
              @endforeach
          </select>
      </div>

      <div class="col-md-6">
          <label class="form-label">Item Category</label>
          <select name="CategoryID" class="form-select" required>
              <option selected disabled>Select Category</option>
              @foreach($categories as $category)
                  <option value="{{ $category->Id }}">{{ $category->Name }}</option>
              @endforeach
          </select>
      </div>


        <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Quantity</label>
          <input type="number" name="quantity" class="form-control" placeholder="e.g. 10" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Unit of Measure</label>
          <select name="unit_of_measure" class="form-select" required>
              <option selected disabled>Select Unit</option>
          <option>Pcs</option>
          <option>Boxes</option>
          <option>Units</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Estimated Cost</label>
          <input type="number" name="estimated_cost" class="form-control" placeholder="e.g. 50000" required>
      </div>
    </div>

    <!-- Schedule -->
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Planned Quarter</label>
          <select name="schedule_period" class="form-select" required>
              <option selected disabled>Select Quarter</option>
              <option value="Q1">Q1</option>
              <option value="Q2">Q2</option>
              <option value="Q3">Q3</option>
              <option value="Q4">Q4</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Expected Delivery Date</label>
          <input type="date" name="expected_delivery_date" class="form-control" required>
      </div>
    </div>

        <!-- Budget Line -->
        <div class="mb-4">
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
    <!-- Notes -->
    <div class="mb-3">
      <label class="form-label">Notes / Justification</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Add any remarks..."></textarea>
    </div>

        <!-- Submit -->
    <div class="d-flex justify-content-end">
      <button type="reset" class="btn btn-outline-secondary me-2">Clear</button>
      <button type="submit" class="btn btn-primary">➕ Add to Plan</button>
    </div>
  </form>
</div>

@endsection
