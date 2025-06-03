@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🧾 Generate Rent Invoice</h4>

 <form action="{{ route('rentinvoice.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📄 Lease Billing Details</div>
    <div class="card-body">
      <!-- Lease Selection -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Lease</label>
            <select name="Lease" class="form-select" required>
              @foreach ($newleases as $newlease)
                <option value="{{ $newlease->Tenant  }}">{{ $newlease->Tenant }} - {{ $newlease->Unit }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Billing Month</label>
          <input type="month" class="form-control" value="2025-05" name="BillingMonth">
        </div>
        <div class="col-md-3">
          <label class="form-label">Invoice Date</label>
          <input type="date" class="form-control" value="2025-05-01" name="InvoiceDate">
        </div>
      </div>

      <!-- Charges Summary -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Rent Amount</label>
          <input type="number" class="form-control" value="25000" name="RentAmount">
        </div>
        <div class="col-md-4">
          <label class="form-label">Service Charge</label>
          <input type="number" class="form-control" value="1500" name="ServicesCharge">
        </div>
        <div class="col-md-4">
          <label class="form-label">Other Charges</label>
          <input type="number" class="form-control" value="0" name="OtherCharges">
        </div>
      </div>

      <!-- Optional Notes -->
      <div class="mb-3">
        <label class="form-label">Invoice Notes</label>
        <textarea class="form-control" rows="2" placeholder="Optional notes or remarks..." name="InvoiceNotes"></textarea>
      </div>
      <button class="btn btn-success">📤 Generate Invoice</button>
      </form>
    </div>
  </div>
</div>
@endsection