@extends('layouts.app')

@section('title', 'Inter-Branch Requisition List')

@section('content')

<div class="container mt-4">
  <h4 class="fw-bold mb-3">📋 Inter-Branch Requisition List</h4>

  <a href="{{ route('interbranchrequisition.create') }}" class="btn btn-sm btn-light mb-3">➕ Add Requisition</a>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Requisition No</th>
              <th>From Branch</th>
              <th>To Branch</th>
              <th>Date</th>
              <th>Status</th>
              <th>Items</th>
              <th>Action</th>
            </tr>
          </thead>
 <tbody>
  @foreach ($groupedRequisitions as $requisition)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $requisition->fromBranch->BranchName ?? '-' }}</td>
      <td>{{ $requisition->toBranch->BranchName ?? '-' }}</td>
      <td>{{ \Carbon\Carbon::parse($requisition->CreatedOn)->format('Y-m-d') }}</td>
      <td>
        @if ($requisition->Status)
          <span class="badge bg-success">Issued</span>
        @else
          <span class="badge bg-warning">Pending Approval</span>
        @endif
      </td>
      <td>{{ $requisition->Items }}</td>
      <td>
        <a href="{{ route('interbranchrequisition.show', $requisition->ReqNo) }}" class="btn btn-sm btn-outline-primary">👁 View</a>
      </td>
    </tr>
  @endforeach
</tbody>

        </table>
      </div>
    </div>
  </div>
</div>
@endsection
