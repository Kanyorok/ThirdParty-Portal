@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📆 Lease Schedule Generator</h4>

<form action="{{ route('schedulelease.store') }}" method="POST">
   @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🧮 Generate Billing Periods</div>
    <div class="card-body">
      <!-- Lease Selection -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Lease Agreement</label>
            <select name="leaseID" class="form-select" required>
              @foreach ($newtenants as $newtenant)
                <option value="{{ $newtenant->TenantName }}">{{ $newtenant->TenantName }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Payment Frequency</label>
          <input type="text" class="form-control" value="Monthly"name="PaymentFrequency" >
        </div>
      </div>

      <!-- Financial Parameters -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Start Date</label>
          <input type="date" class="form-control" value="2025-05-01"name="StartDate">
        </div>
        <div class="col-md-4">
          <label class="form-label">End Date</label>
          <input type="date" class="form-control" value="2026-04-30"name="EndDate">
        </div>
        <div class="col-md-4">
          <label class="form-label">Base Rent per Period (KES)</label>
          <input type="number" class="form-control" value="25000"name="BaseRent">
        </div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Service Charge (KES)</label>
          <input type="number" class="form-control" value="1500"name="ServiceCharge">
        </div>
        <div class="col-md-4">
          <label class="form-label">Parking Fee (KES)</label>
          <input type="number" class="form-control" value="2000"name="ParkingFee">
        </div>
        <div class="col-md-4">
          <label class="form-label">Other Charges (KES)</label>
          <input type="number" class="form-control" value="0"name="OtherCharges">
        </div>
      </div>
        <button class="btn btn-success">🧾 Generate Schedule</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection