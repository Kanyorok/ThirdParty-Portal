@extends('layouts.app')
@section('title', 'Raise Need')
@section('content')
<div class="card p-4 shadow rounded-4">
  <h4 class="mb-4">📥 Raise Procurement Need</h4>

  <form>
    <div class="row mb-3">
      <div class="col-md-6">
        <label for="itemName" class="form-label">Item Name</label>
        <input type="text" class="form-control" id="itemName" placeholder="e.g. Desktop Computer">
      </div>
      <div class="col-md-6">
        <label for="itemCategory" class="form-label">Item Category</label>
        <select class="form-select" id="itemCategory">
          <option selected disabled>Select category</option>
          <option>IT Equipment</option>
          <option>Office Supplies</option>
          <option>Furniture</option>
          <!-- Dynamically loaded -->
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label for="quantity" class="form-label">Quantity Needed</label>
        <input type="number" class="form-control" id="quantity" placeholder="e.g. 5">
      </div>
      <div class="col-md-4">
        <label for="uom" class="form-label">Unit of Measure</label>
        <select class="form-select" id="uom">
          <option selected disabled>Select UOM</option>
          <option>Pcs</option>
          <option>Litres</option>
          <option>Boxes</option>
        </select>
      </div>
      <div class="col-md-4">
        <label for="estimatedCost" class="form-label">Estimated Total Cost</label>
        <input type="number" class="form-control" id="estimatedCost" placeholder="e.g. 50000">
      </div>
    </div>

    <div class="mb-3">
      <label for="requiredDate" class="form-label">Required By</label>
      <input type="date" class="form-control" id="requiredDate">
    </div>

    <div class="mb-3">
      <label for="justification" class="form-label">Justification</label>
      <textarea class="form-control" id="justification" rows="3" placeholder="Explain the need..."></textarea>
    </div>

    <div class="mb-3">
      <label for="attachment" class="form-label">Attach Document (optional)</label>
      <input class="form-control" type="file" id="attachment">
    </div>

    <div class="d-flex justify-content-end">
      <button type="reset" class="btn btn-secondary me-2">Clear</button>
      <button type="submit" class="btn btn-primary">Submit Need</button>
    </div>
  </form>
</div>

@endsection