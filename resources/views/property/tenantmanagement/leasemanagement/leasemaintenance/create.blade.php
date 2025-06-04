@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📄 New Lease Agreement</h4>
<form method="POST" action="{{ route('addlease.store') }}">
  @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📝 Lease Details</div>
    <div class="card-body">
      <!-- Tenant and Unit Selection -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Tenant</label>
            <select name="Tenant" class="form-select" required>
              @foreach ($newtenants as $newtenant)
                <option value="{{ $newtenant->TenantName }}">{{ $newtenant->TenantName }}</option>
              @endforeach
            </select>
        </div>
        <div class="row g-3 mb-3">
      <label class="form-label">Select Property</label>
            <select name="PropertyID" class="form-select" required>
              @foreach ($units as $unit)
                <option value="{{ $unit->PropertyID }}">{{ $unit->PropertyID }}</option>
              @endforeach
            </select>
        </div>
      <div class="col-md-4">
          <label class="form-label">Select Block</label>
            <select name="BlockID" class="form-select" required>
              @foreach ($units as $unit)
                <option value="{{ $unit->BlockID }}">{{ $unit->BlockID }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Select Floor</label>
            <select name="FloorID" class="form-select" required>
              @foreach ($units as $unit)
                <option value="{{ $unit->FloorID }}">{{ $unit->FloorID }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Select Unit(s)</label>
            <select name="Unit" class="form-select" required>
              @foreach ($units as $unit)
                <option value="{{ $unit->UnitCode }}">{{ $unit->UnitCode }}</option>
              @endforeach
            </select>
        </div>
      </div>
      <!-- Lease Duration -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Start Date</label>
          <input type="date" class="form-control" name="StartDate">
        </div>
        <div class="col-md-4">
          <label class="form-label">End Date</label>
          <input type="date" class="form-control" name="EndDate">
        </div>
        <div class="col-md-4">
          <label class="form-label">Payment Frequency</label>
          <select class="form-select"name="PaymentFrequency">
            <option>Monthly</option>
            <option>Quarterly</option>
            <option>Bi-Annually</option>
            <option>Annually</option>
          </select>
        </div>
      </div>
      <!-- Financials -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Monthly Rent (KES)</label>
          <input type="number" class="form-control" placeholder="e.g. 25000" name="MonthlyRent">
        </div>
        <div class="col-md-4">
          <label class="form-label">Deposit (KES)</label>
          <input type="number" class="form-control" placeholder="e.g. 25000" name="Deposit">
        </div>
        <div class="col-md-4">
          <label class="form-label">Due Day</label>
          <input type="number" class="form-control" placeholder="e.g. 5 (for 5th of each month)"name="DueDay">
        </div>
      </div>

      <!-- Terms and Documents -->
      <div class="mb-3">
        <label class="form-label">Special Terms & Conditions</label>
        <textarea class="form-control" rows="3" placeholder="Optional terms or notes..." name="SpecialTerms"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Upload Lease Document</label>
        <input type="file" class="form-control" accept=".pdf,.docx">
      </div>
      <button class="btn btn-success">💾 Save Lease</button>
      </form>
    </div>
  </div>
</div>
@endsection