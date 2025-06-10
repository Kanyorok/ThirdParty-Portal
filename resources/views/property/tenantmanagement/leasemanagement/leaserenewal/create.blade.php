@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

  <h4 class="fw-bold mb-3">🔁 Renew Lease Agreement</h4>

    <form action="{{ route('renewlease.store') }}" method="POST">
        @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📝 New Lease Terms</div>
    <div class="card-body">
        <!-- Select Current Lease -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Current Lease</label>
            <select name="CurrentLease" class="form-select" required>
                @foreach ($newtenants as $newtenant)
                    <option value="{{ $newtenant->id }}">{{ $newtenant->TenantName }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">End Date of Current Lease</label>
            <input type="date" class="form-control" value="2025-08-31" name="EndDateCurrentLease">
        </div>
        <div class="col-md-3">
          <label class="form-label">New Start Date</label>
            <input type="date" class="form-control" value="2025-09-01" name="NewStartDate">
        </div>
      </div>

      <!-- New Terms -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">New End Date</label>
            <input type="date" class="form-control" value="2026-08-31" name="NewEndDate">
        </div>
        <div class="col-md-4">
          <label class="form-label">New Monthly Rent</label>
            <input type="number" class="form-control" value="27500" name="NewMonthlyRent">
        </div>
        <div class="col-md-4">
          <label class="form-label">Payment Frequency</label>
            <select class="form-select" name="PaymentFrequency">
            <option selected>Monthly</option>
            <option>Quarterly</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Remarks or Changes</label>
          <textarea class="form-control" rows="2" placeholder="E.g. rent increased by KES 2,500"
                    name="Remarks"></textarea>
      </div>
        <button class="btn btn-success">🔁 Renew Lease</button>
    </div>
  </div>
</div>
@endsection
