@extends('layouts.app')
@section('title', ' Item Price Management')
@section('content')
@stack('scripts')
<div class="container mt-4">
  <h4 class="mb-3">📦 Item Price Management</h4>

  <!-- Nav Tabs -->
  <ul class="nav nav-tabs" id="priceTabs" role="tablist">
    <li class="nav-item">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#addPrice" type="button">➕ Add Price</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#viewPrices" type="button">📄 View Price List</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#uploadPrice" type="button">📁 Upload Price List</button>
    </li>
  </ul>

  <!-- Tab Contents -->
  <div class="tab-content border p-3">

    <!-- Add Price Tab -->
    <div class="tab-pane fade show active" id="addPrice">
      <form>
        <div class="mb-3">
          <label class="form-label">Item</label>
          <select class="form-select">
            <option selected disabled>Select Item</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">UOM</label>
          <select class="form-select">
            <option selected disabled>Select UOM</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">SKUID</label>
          <select class="form-select">
            <option selected disabled>Select SKU</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Price</label>
          <input type="number" step="0.01" class="form-control">
        </div>
        <div class="row">
          <div class="col-md-6">
            <label class="form-label">Effective From</label>
            <input type="date" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Effective To</label>
            <input type="date" class="form-control">
          </div>
        </div>
        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="isDefault">
          <label class="form-check-label" for="isDefault">Mark as Default Price</label>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Save Price</button>
      </form>
    </div>

    <!-- View Prices Tab -->
    <div class="tab-pane fade" id="viewPrices">
      <table class="table table-bordered table-striped mt-3">
        <thead>
          <tr>
            <th>#</th>
            <th>Item</th>
            <th>UOM</th>
            <th>Price</th>
            <th>Effective From</th>
            <th>Effective To</th>
            <th>Default</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Example Row -->
          <tr>
            <td>1</td>
            
            <td>A4 Paper</td>
            <td>PCS</td>
            <td>500.00</td>
            <td>2025-06-01</td>
            <td>—</td>
            <td>✔️</td>
            <td>
              <button class="btn btn-sm btn-info">Edit</button>
              <button class="btn btn-sm btn-danger">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Upload Price List Tab -->
    <div class="tab-pane fade" id="uploadPrice">
      <form action="/upload-price-list" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Upload Excel or CSV File</label>
          <input class="form-control" type="file" name="priceFile" accept=".csv,.xlsx,.xls" required>
        </div>
        <div class="alert alert-info small">
          Ensure your file has headers: <code>ItemCode, UOMCode, Price, EffectiveFrom, EffectiveTo, Currency, IsDefault</code>
        </div>
        <button type="submit" class="btn btn-success">Upload</button>
      </form>
    </div>

  </div>
</div>


@endsection
