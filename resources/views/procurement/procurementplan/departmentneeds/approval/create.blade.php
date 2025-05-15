@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')

<div class="card p-4 shadow rounded-4">
  <h4 class="mb-4">🔍 View Procurement Need Details</h4>

  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label">Item Name</label>
      <p class="form-control-plaintext">Desktop Computer</p>
    </div>
    <div class="col-md-6">
      <label class="form-label">Category</label>
      <p class="form-control-plaintext">IT Equipment</p>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-4">
      <label class="form-label">Quantity Needed</label>
      <p class="form-control-plaintext">3</p>
    </div>
    <div class="col-md-4">
      <label class="form-label">Unit of Measure</label>
      <p class="form-control-plaintext">Pcs</p>
    </div>
    <div class="col-md-4">
      <label class="form-label">Estimated Cost</label>
      <p class="form-control-plaintext">75,000</p>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label">Required By</label>
      <p class="form-control-plaintext">2025-06-30</p>
    </div>
    <div class="col-md-6">
      <label class="form-label">Submitted On</label>
      <p class="form-control-plaintext">2025-05-10</p>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">Justification</label>
    <div class="form-control-plaintext border rounded p-2 bg-light">
      Replacement for aging computers in operations.
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">Submitted By</label>
    <p class="form-control-plaintext">John Kamau (Operations Department)</p>
  </div>

  <div class="mb-3">
    <label class="form-label">Attached Document</label>
    <p class="form-control-plaintext">
      <a href="/uploads/Quote-Desktop.pdf" target="_blank">Quote-Desktop.pdf</a>
    </p>
  </div>

  <div class="d-flex justify-content-end mt-4">
    <a href="/planning/approve/123" class="btn btn-success me-2">Approve</a>
    <a href="/planning/reject/123" class="btn btn-danger me-2">Reject</a>
    <a href="/planning/needs" class="btn btn-secondary">Back to List</a>
  </div>
</div>
@endsection