@php use App\Enums\Property\TenantClearanceEnum; @endphp
@extends('layouts.app')
@section('title', 'Tenant Exit')
@section('content')
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  <div class="container mt-4">

    <h4 class="fw-bold mb-3">Tenant Exit & Clearance Checklist</h4>

    <form action="{{ route('tenantclearance.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="card shadow">
        <div class="card-header bg-light fw-bold"> Exit Process</div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Tenant / Lease<span class="text-danger">*</span></label>
              <select name="LeaseId" class="form-select" required>
                <option>--Select the tenant</option>
                @foreach ($newtenants as $newtenant)
                  <option value="{{ $newtenant->LeaseID }}">
                    Name:{{ $newtenant->lease->tenant->TenantName }} &nbsp;&nbsp; LeaseNo:
                    {{ $newtenant->lease->LeaseNumber }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" id="exit-date">Exit Date<span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="exit-date" value="ExitDate" name="ExitDate" required>
            </div>
          </div>

          <!-- Checklist Items -->
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label">Final Inspection Done?<span class="text-danger">*</span></label>
              <select class="form-select" name="FinalInspection">
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">All Dues Paid?<span class="text-danger">*</span></label>
              <select class="form-select" name="AllDuesPaid">
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Keys Returned?<span class="text-danger">*</span></label>
              <select class="form-select" name="KeysReturned">
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Deposit Refunded?<span class="text-danger">*</span></label>
              <select class="form-select" name="DepositRefunded" required>
                <option>--Select the tenant Type</option>
                @foreach ($codedetails as $codedetail)
                  <option value="{{ $codedetail->ID }}">{{ $codedetail->Description }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Status<span class="text-danger">*</span></label>
            <select class="form-select" name="Status" required>
              <option value="">-- Select Status --</option>
              @foreach (TenantClearanceEnum::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
              @endforeach
            </select>
          </div>

          <!-- Document Upload -->
          <div class="mb-3">
            <label class="form-label">Upload clearance Documents</label>
            <input type="file" name="Document" class="form-control" multiple>
            <small class="text-muted">e.g. Extra Clearance info</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Additional Notes</label>
            <textarea class="form-control" rows="2" placeholder="Any final notes or clearance details..."
              name="AdditionalNotes"></textarea>
          </div>
          <button type="submit" class="btn btn-success"
            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();"> Finalize Exit</button>
    </form>
  </div>
  </div>
  </div>


  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    flatpickr("#exit-date", {
      dateFormat: "d/m/Y",
      allowInput: true
    });
  </script>
@endsection
