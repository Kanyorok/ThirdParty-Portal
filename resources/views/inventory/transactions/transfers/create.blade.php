@extends('layouts.app')
@section('title', 'Create Tranfer')
@section('content')
<div class="card mb-4">
  <div class="card-header bg-warning text-dark">🚚 Dispatch Stock Transfer</div>
  <div class="card-body">
    <form>
      <div class="row mb-3">
        <div class="col">
          <label class="form-label">Requisition Ref</label>
          <select class="form-select">
            <option>Select Requisition</option>
          </select>
        </div>
        <div class="col">
          <label class="form-label">From Branch</label>
          <input type="text" class="form-control" readonly value="Warehouse A" />
        </div>
        <div class="col">
          <label class="form-label">To Branch</label>
          <input type="text" class="form-control" readonly value="Branch X" />
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Dispatch Date</label>
        <input type="date" class="form-control" />
      </div>

      <div class="mb-3">
        <label class="form-label">Items to Transfer</label>
        <table class="table table-bordered">
          <thead class="table-light">
            <tr>
              <th>Product</th>
              <th>Qty Dispatched</th>
              <th>Batch No</th>
              <th>Expiry Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><input class="form-control" /></td>
              <td><input class="form-control" /></td>
              <td><input class="form-control" /></td>
              <td><input type="date" class="form-control" /></td>
              <td><button class="btn btn-danger btn-sm">Remove</button></td>
            </tr>
          </tbody>
        </table>
        <button class="btn btn-secondary btn-sm">➕ Add Item</button>
      </div>

      <button class="btn btn-success">Dispatch Stock</button>
    </form>
  </div>
</div>

@endSection