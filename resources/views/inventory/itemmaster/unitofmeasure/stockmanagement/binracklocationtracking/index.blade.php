@extends('layouts.app')
@section('title', 'Location Assignments')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📋 Bin / Location Assignments</h4>

  <!-- Filters -->
  <div class="card shadow mb-3">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">🏢 Branch</label>
          <select class="form-select" id="filterBranch">
            <option value="">All Branches</option>
            <option value="Branch A">Branch A</option>
            <option value="Branch B">Branch B</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">📦 Store</label>
          <select class="form-select" id="filterStore">
            <option value="">All Stores</option>
            <option value="Main Store">Main Store</option>
            <option value="Back Store">Back Store</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">🔍 Bin Code</label>
          <input type="text" class="form-control" id="filterBin" placeholder="e.g. R1-S2-B3">
        </div>
        <div class="col-md-3 text-end">
          <button class="btn btn-primary" onclick="applyFilters()">🔄 Apply Filters</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Table -->
  <table class="table table-bordered table-striped align-middle" id="binTable">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Branch</th>
        <th>Store</th>
        <th>Item</th>
        <th>Bin Code</th>
        <th>Current Qty</th>
        <th>Max Capacity</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody>
      <tr data-branch="Branch A" data-store="Main Store" data-bin="R1-S2-B3">
        <td>1</td>
        <td>Branch A</td>
        <td>Main Store</td>
        <td>A4 Paper</td>
        <td>R1-S2-B3</td>
        <td>60</td>
        <td>100</td>
        <td>Front row shelf</td>
      </tr>
      <tr data-branch="Branch B" data-store="Back Store" data-bin="WH2-R4">
        <td>2</td>
        <td>Branch B</td>
        <td>Back Store</td>
        <td>Printer</td>
        <td>WH2-R4</td>
        <td>5</td>
        <td>10</td>
        <td>Temperature controlled</td>
      </tr>
      <tr data-branch="Branch A" data-store="Main Store" data-bin="R2-S1-B1">
        <td>3</td>
        <td>Branch A</td>
        <td>Main Store</td>
        <td>Envelopes</td>
        <td>R2-S1-B1</td>
        <td>200</td>
        <td>250</td>
        <td>Upper shelf</td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  function applyFilters() {
    const branch = document.getElementById('filterBranch').value.toLowerCase();
    const store = document.getElementById('filterStore').value.toLowerCase();
    const bin = document.getElementById('filterBin').value.toLowerCase();

    const rows = document.querySelectorAll('#binTable tbody tr');
    rows.forEach(row => {
      const rowBranch = row.dataset.branch.toLowerCase();
      const rowStore = row.dataset.store.toLowerCase();
      const rowBin = row.dataset.bin.toLowerCase();

      const matches =
        (branch === '' || rowBranch === branch) &&
        (store === '' || rowStore === store) &&
        (bin === '' || rowBin.includes(bin));

      row.style.display = matches ? '' : 'none';
    });
  }
</script>
@endsection