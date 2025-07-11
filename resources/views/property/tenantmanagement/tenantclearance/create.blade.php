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

    <form action="{{ route('tenantclearance.store') }}" method="POST">
        @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold"> Exit Process</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Tenant / Lease</label>
            <select name="LeaseId" class="form-select" required>
                <option>--Select the tenant</option>
                @foreach ($newtenants as $newtenant)
                    <option value="{{ $newtenant->LeaseID }}">
                      Name:{{ $newtenant->lease->tenant->TenantName }} &nbsp;&nbsp; LeaseNo: {{$newtenant->lease->LeaseNumber}}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" id="exit-date">Exit Date</label>
            <input type="date" class="form-control" id="exit-date" value="ExitDate" name="ExitDate">
        </div>
      </div>

      <!-- Checklist Items -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Final Inspection Done?</label>
            <select class="form-select" name="FinalInspection">
                <option value="1">Yes</option>
                <option value="0">No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">All Dues Paid?</label>
            <select class="form-select" name="AllDuesPaid">
                <option value="1">Yes</option>
                <option value="0">No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Keys Returned?</label>
            <select class="form-select" name="KeysReturned">
                <option value="1">Yes</option>
                <option value="0">No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Deposit Refunded?</label>
            <select class="form-select" name="DepositRefunded" required>
                <option>--Select the tenant Type</option>
                @foreach ($codedetails as $codedetail)
                    <option value="{{ $codedetail->ID }}">{{ $codedetail->Description }}</option>
                @endforeach
          </select>
        </div>
      </div>
      <div class="col-md-3">
          <label class="form-label">Status</label>
          <select class="form-select" name="Status" required>
              <option value="">-- Select Status --</option>
              @foreach (\App\Enums\Property\TenantClearanceEnum::cases() as $status)
                  <option value="{{ $status->value }}">{{ $status->label() }}</option>
              @endforeach
          </select>
      </div>

      <!-- Upload & Remarks -->
      <div class="mb-3">
        <label class="form-label">Upload Exit Document (optional)</label>
        <input type="file" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Additional Notes</label>
          <textarea class="form-control" rows="2" placeholder="Any final notes or clearance details..."
                    name="AdditionalNotes"></textarea>
      </div>
        <button class="btn btn-danger"> Finalize Exit</button>
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
