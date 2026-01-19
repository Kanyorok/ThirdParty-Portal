@extends('layouts.app')

@section('title', 'Maintenance Requests')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
  .action-buttons {
    display: flex;
    flex-wrap: nowrap;
    gap: 0.4rem;
    align-items: center;
  }
</style>
@endsection

@section('content')
<div class="container mt-4">

  <!-- Header -->
  <div class="d-flex justify-content-end align-items-center mb-3">
    <a href="{{ route('maintenancerequest.create') }}" class="btn btn-primary">
      <i class="bi bi-wrench-adjustable-circle me-1"></i> New Request
    </a>
  </div>

  <p class="text-muted">
    <small>This table lists all raised maintenance requests and their details.</small>
  </p>

  @if ($maintenancerequests->count())
  <div class="card shadow-sm">
    <div class="card-body">
      <table id="MaintenanceRequest" class="table table-bordered table-striped table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 5%">#</th>
            <th>Request No.</th>
            <th>Property</th>
            <th>Reported By</th>
            <th>Issue Type</th>
            <th>Priority</th>
            <th style="width: 20%">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($maintenancerequests as $maintenancerequest)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $maintenancerequest->RequestNumber ?? '-' }}</td>
            <td>{{ $maintenancerequest->property->PropertyName ?? '-' }}</td>
            <td>{{ $maintenancerequest->reportedByUser->ThirdPartyName ?? '-' }}</td>
            <td>{{ $maintenancerequest->issueType->Description ?? '-' }}</td>
            <td>{{ $maintenancerequest->priority->Description ?? '-' }}</td>
            <td>
              <div class="action-buttons">
                <a href="{{ route('maintenancerequest.show', $maintenancerequest->Id) }}"
                  class="btn btn-sm btn-info text-white"
                  title="View Request">
                  <i class="bi bi-eye"></i>
                </a>

                @if($maintenancerequest->requestId()->exists())
                <button class="btn btn-sm btn-secondary" title="In Use">
                  <i class="bi bi-lock"></i>
                </button>
                @else
                <a href="{{ route('maintenancerequest.edit', $maintenancerequest->Id) }}"
                  class="btn btn-sm btn-warning"
                  title="Edit Request">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <form action="{{ route('maintenancerequest.destroy', $maintenancerequest->Id) }}"
                  method="POST"
                  onsubmit="return confirm('Are you sure you want to delete this maintenance request?');"
                  class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" title="Delete Request">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @else
  <div class="alert alert-info mt-3">
    <i class="bi bi-info-circle me-2"></i> No maintenance requests have been raised yet.
  </div>
  @endif

</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function() {
    $('#MaintenanceRequest').DataTable({
      pageLength: 10,
      ordering: true,
      searching: true,
      lengthChange: true
    });
  });
</script>
@endsection