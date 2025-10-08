@extends('layouts.app')
@section('title', 'Maintenance Dashboard')

@section('content')
<div class="container mt-4">
  <p><small>This dashboard displays the position of the maintenance task based on its current status.</small></p>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card text-white bg-primary shadow-sm">
        <div class="card-body text-center">
          <h6>Total Requests</h6>
          <h3>{{ $totalRequests }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-warning shadow-sm">
        <div class="card-body text-center">
          <h6>In Progress</h6>
          <h3>{{ $inProgress }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-success shadow-sm">
        <div class="card-body text-center">
          <h6>Completed</h6>
          <h3>{{ $completed }}</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <form method="GET" action="{{ route('maintenancedashboard.index') }}" class="row g-3 mb-3">
    <div class="col-md-3">
      <select name="property_id" class="form-select">
        <option value="">All Properties</option>
        @foreach($properties as $property)
          <option value="{{ $property->Id }}" {{ request('property_id') == $property->Id ? 'selected' : '' }}>
            {{ $property->PropertyName }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <select name="priority" class="form-select">
        <option value="">All Priorities</option>
        <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
        <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
        <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
      </select>
    </div>
    <div class="col-md-3">
      <select name="status" class="form-select">
        <option value="">All Statuses</option>
        <option value="{{ \App\Enums\Core\PostingEnum::Pending }}" {{ request('status') == \App\Enums\Core\PostingEnum::Pending ? 'selected' : '' }}>Pending</option>
        <option value="{{ \App\Enums\Core\PostingEnum::Completed }}" {{ request('status') == \App\Enums\Core\PostingEnum::Completed ? 'selected' : '' }}>Completed</option>
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn btn-outline-primary w-100">🔍 Refresh</button>
    </div>
  </form>

  <!-- Table View -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Request</th>
          <th>Property</th>
          <th>Type</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Assigned To</th>
          <th>Date Reported</th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $assign)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $assign->request->RequestNumber ?? '-' }}</td>
            <td>{{ $assign->request->property->PropertyName ?? '-' }}</td>
            <td>{{ $assign->request->issueType->Description ?? '-' }}</td>
            <td>
              @php
                $priority = $assign->request->priority->Description ?? '-';
              @endphp
              <span class="badge 
                @if($priority === 'High') bg-danger
                @elseif($priority === 'Medium') bg-warning text-dark
                @elseif($priority === 'Low') bg-info
                @else bg-secondary
                @endif">
                {{ $priority }}
              </span>
            </td>
            <td>
              <span class="badge
                @if($assign->Status == \App\Enums\Core\PostingEnum::Completed) bg-success
                @elseif($assign->Status == \App\Enums\Core\PostingEnum::Pending) bg-warning text-dark
                @else bg-secondary
                @endif">
                {{ $assign->Status->Label() ?? 'Unknown' }}
              </span>
            </td>
            <td>
              {{ $assign->internalTechnician->FullName ?? $assign->prequalifiedVendor->TradingName ?? '-' }}
            </td>
            <td>{{ $assign->AssignmentDate ? \Carbon\Carbon::parse($assign->AssignmentDate)->format('d/m/Y') : '-' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center">No maintenance records found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="mt-3">
    {{ $requests->withQueryString()->links() }}
  </div>
</div>
@endsection
