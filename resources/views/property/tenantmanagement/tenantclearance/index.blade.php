@extends('layouts.app')
@section('title', 'Tenant Exit')
@section('content')
<div class="container mt-4">
<a href="{{ route('tenantclearance.create') }}" class="btn btn-primary mb-3">New Clearance</a>
  <h4 class="fw-bold mb-3">📋 Tenant Exit & Clearance Records</h4>

  @if($clearancetenants->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Tenant</th>
        <th>Exit Date</th>
        <th>Final Inspection done</th>
        <th>Dues Cleared</th>
        <th>Keys Returned</th>
        <th>Deposit Status</th>
        <th>Additional Notes</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($clearancetenants as $clearancetenant)
      <tr>
        <td>{{ $loop->iteration ?? '-' }}</td>
        <td>{{ $clearancetenant->Tenant ?? '-' }}</td>
        <td>{{ $clearancetenant->ExitDate ?? '-' }}</td>
        <td>{{ $clearancetenant->FinalInspection?? '-' }}</td>
        <td>{{ $clearancetenant->AllDuesPaid ?? '-' }}</td>
        <td>{{ $clearancetenant->KeysReturned ?? '-' }}</td>
        <td>{{ $clearancetenant->DepositRefunded ?? '-' }}</td>
        <td>{{ $clearancetenant->AdditionalNotes ?? '-' }}</td>
        <td><span class="badge bg-success">Cleared</span></td>
        <td>
          <a href="{{ route('tenantclearance.show', $clearancetenant->id) }}" class="btn btn-sm btn-info">👁 View</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
@else
  <p>No clearance records registered yet.</p>
@endif
</div>
@endsection