@extends('layouts.app')
@section('title', 'Tenant Exit')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
<a href="{{ route('tenantclearance.create') }}" class="btn btn-primary mb-3">New Clearance</a>
  <h4 class="fw-bold mb-3">Tenant Exit & Clearance Records</h4>

    @if($clearancetenants->count())
  <table class="table table-bordered table-striped align-middle" id="tenantclearance">
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
          <td>{{ $clearancetenant->tenant->TenantName ?? '-' }}</td>
          <td>{{ $clearancetenant->ExitDate ? \Carbon\Carbon::parse($clearancetenant->ExitDate)->format('d/m/Y') : '-' }}</td>
          <td>@if($clearancetenant->FinalInspection) <span class="badge bg-success">Yes</span> @else <span class="badge bg-danger">No</span> @endif</td>
          <td>@if($clearancetenant->AllDuesPaid) <span class="badge bg-success">Yes</span> @else <span class="badge bg-danger">No</span> @endif</td>
          <td>@if($clearancetenant->KeysReturned) <span class="badge bg-success">Yes</span> @else <span class="badge bg-danger">No</span> @endif</td>
          <td>{{ $clearancetenant->code->Description ?? '-' }}</td>
          <td>{{ $clearancetenant->AdditionalNotes ?? '-' }}</td>
          <td>
              <span class="badge bg-{{ $clearancetenant->Status->badgeColor() }}">
                  {{ $clearancetenant->Status->label() }}
              </span>
          </td>
        <td>
            <a href="{{ route('tenantclearance.show', $clearancetenant->Id) }}" class="btn btn-sm btn-info">👁 View</a>
            <a href="{{ route('tenantclearance.edit', $clearancetenant->Id) }}" class="btn btn-sm btn-warning">Edit</a>
            <form action="{{ route('tenantclearance.destroy', $clearancetenant->Id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this property?');">Delete</button>
            </form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No clearance records registered yet.</p>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tenantclearance').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
