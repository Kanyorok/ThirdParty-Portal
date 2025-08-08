@extends('layouts.app')
@section('title', 'Maintenance Dashboard')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📈 Maintenance Dashboard</h4>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card text-white bg-primary shadow">
        <div class="card-body">
          <h6>Total Requests</h6>
          <h3>{{ $totalRequests }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-warning shadow">
        <div class="card-body">
          <h6>In Progress</h6>
          <h3>{{ $inProgress }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-success shadow">
        <div class="card-body">
          <h6>Completed</h6>
          <h3>{{ $completed }}</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters (To be implemented dynamically later) -->
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <select class="form-select" disabled>
        <option selected>All Properties</option>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select" disabled>
        <option selected>All Priorities</option>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select" disabled>
        <option selected>All Statuses</option>
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn btn-outline-primary w-100" disabled>🔍 Refresh</button>
    </div>
  </div>

  <!-- Table View -->
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Request</th>
        <th>Unit</th>
        <th>Type</th>
        <th>Priority</th>
        <th>Status</th>
        <th>Assigned To</th>
        <th>Date Reported</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($requests as $index => $assign)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $assign->request->RequestNumber ?? '-' }}</td>
          <td>{{ $assign->request->unit->UnitCode ?? '-' }}</td>
          <td>{{ $assign->request->issueType->Description ?? '-' }}</td>
          <td>
            <span class="badge bg-{{ $assign->request->priority->Description == 'High' ? 'danger' : 'secondary' }}">
              {{ $assign->request->priority->Description ?? '-' }}
            </span>
          </td>
          <td>
            <span class="badge bg-warning text-dark">
              {{ $assign->Status->Label() }}
            </span>
          </td>
          <td>
            {{ $assign->internalTechnician->FullName ?? $assign->prequalifiedVendor->TradingName ?? '-' }}
          </td>
          <td>{{ \Carbon\Carbon::parse($assign->AssignmentDate)->format('d/m/Y') }}</td>
          <td>
            <a href="{{ route('workcompletion.show', $assign->Id) }}" class="btn btn-sm btn-outline-primary">View</a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="9" class="text-center">No maintenance records found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
