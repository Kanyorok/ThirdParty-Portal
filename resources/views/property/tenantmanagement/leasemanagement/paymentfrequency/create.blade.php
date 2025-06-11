@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">➕ Add Payment Frequency</h4>

<form action="{{ route('paymentfrequency.store') }}" method="POST">
   @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🛠 Frequency Setup</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Frequency Name</label>
          <input type="text" class="form-control" placeholder="e.g. Monthly, Quarterly"name="FrequencyName">
        </div>
        <div class="col-md-4">
          <label class="form-label">Frequency Code</label>
          <input type="text" class="form-control" placeholder="e.g. MTH, QTR, ANL"name="FrequencyCode">
        </div>
        <div class="col-md-4">
          <label class="form-label">Number of Months</label>
          <input type="number" class="form-control" placeholder="e.g. 1 for Monthly, 3 for Quarterly"name="NumberOfMonths">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" rows="2" placeholder="Optional description..."name="Description"></textarea>
      </div>
        <button class="btn btn-success">💾 Save Frequency</button>
        </form>
      </div>
  </div>
</div>
@endsection