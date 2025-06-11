@extends('layouts.app')
@section('title', 'Register New Supplier')

@section('content')
<div class="card">
  <div class="card-header bg-primary text-white">🧾 Supplier Registration Wizard</div>
  <div class="card-body">
    <form method="POST" action="{{ route('suppliers.store') }}" enctype="multipart/form-data">
      @csrf

      <ul class="nav nav-tabs" id="supplierTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#basic">1. Basic Info</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#contact">2. Contact</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bank">3. Bank</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#category">4. Categories</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#docs">5. Documents</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#declaration">6. Declaration</a></li>
      </ul>

      <div class="tab-content p-3 border border-top-0">
        {{-- Basic Info --}}
        <div class="tab-pane fade show active" id="basic">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Legal Name</label>
              <input type="text" name="SupplierName" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label>Trading Name</label>
              <input type="text" name="TradingName" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Business Type</label>
              <select name="BusinessType" class="form-select" required>
                <option value="">--Select--</option>
                <option>Company</option>
                <option>Individual</option>
                <option>Joint Venture</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label>Registration Number</label>
              <input type="text" name="RegistrationNumber" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>KRA PIN</label>
              <input type="text" name="TaxPIN" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>VAT Number</label>
              <input type="text" name="VATNumber" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Country</label>
              <input type="text" name="Country" class="form-control" value="Kenya">
            </div>
            <div class="col-md-6 mb-3">
              <label>Website</label>
              <input type="text" name="Website" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Email</label>
              <input type="email" name="Email" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Phone</label>
              <input type="text" name="Phone" class="form-control">
            </div>
            <div class="col-12 mb-3">
              <label>Physical Address</label>
              <textarea name="PhysicalAddress" class="form-control"></textarea>
            </div>
          </div>
        </div>

        {{-- Contact Tab --}}
        <div class="tab-pane fade" id="contact">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Contact Name</label>
              <input type="text" name="ContactName" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Designation</label>
              <input type="text" name="Designation" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Contact Email</label>
              <input type="email" name="ContactEmail" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Contact Phone</label>
              <input type="text" name="ContactPhone" class="form-control">
            </div>
            <div class="col-12 mb-3">
              <label><input type="checkbox" name="IsPrimary" value="1"> Primary Contact</label>
            </div>
          </div>
        </div>

        {{-- Bank Tab --}}
        <div class="tab-pane fade" id="bank">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Bank Name</label>
              <input type="text" name="BankName" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Branch</label>
              <input type="text" name="Branch" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Account Number</label>
              <input type="text" name="AccountNumber" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Currency</label>
              <select name="Currency" class="form-select">
                <option value="KES">KES</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label>SWIFT Code</label>
              <input type="text" name="SwiftCode" class="form-control">
            </div>
          </div>
        </div>

        {{-- Categories Tab --}}
        <div class="tab-pane fade" id="category">
          <div class="mb-3">
            <label>Supply Categories</label>
            <select name="CategoryIDs[]" class="form-select" multiple>
              <option value="1">IT Equipment</option>
              <option value="2">Office Furniture</option>
              <option value="3">Stationery</option>
              <option value="4">Cleaning Supplies</option>
              <option value="5">Electrical Fittings</option>
              <option value="6">Plumbing</option>
              <option value="7">Security Services</option>
              <option value="8">Consultancy</option>
            </select>
            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
          </div>
        </div>

        {{-- Documents Tab --}}
        <div class="tab-pane fade" id="docs">
          <div class="mb-3">
            <label>Document Type</label>
            <input type="text" name="DocType" class="form-control" placeholder="e.g., Certificate of Incorporation">
          </div>
          <div class="mb-3">
            <label>Upload Document</label>
            <input type="file" name="FilePath" class="form-control">
          </div>
          <div class="mb-3">
            <label>Expiry Date (if any)</label>
            <input type="date" name="ExpiryDate" class="form-control">
          </div>
        </div>

        {{-- Declaration Tab --}}
        <div class="tab-pane fade" id="declaration">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="Declaration" value="1" required>
            <label class="form-check-label">I declare that the above information is true and correct.</label>
          </div>
          <button type="submit" class="btn btn-success">📝 Submit Supplier Registration</button>
        </div>

      </div>
    </form>
  </div>
</div>
@endsection
