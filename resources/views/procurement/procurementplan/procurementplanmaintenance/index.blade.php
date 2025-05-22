@extends('layouts.app')
@section('title', 'Consolidated Procurement Plans')
@section('content')

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📄 Consolidated Procurement Plans</h4>
    <a href="{{ route('procurementplanmaintain.create') }}" class="btn btn-success btn-sm">+ New Plan</a>
  </div>

  <!-- Optional: Filter Controls -->
  <div class="row mb-3">
    <div class="col-md-4">
      <select class="form-select">
        <option selected>All Years</option>
        <option>2025</option>
        <option>2026</option>
      </select>
    </div>
    <div class="col-md-4">
      <select class="form-select">
        <option selected>All Statuses</option>
        <option>Draft</option>
        <option>Pending Approval</option>
        <option>Approved</option>
      </select>
    </div>
    <div class="col-md-4">
      <button class="btn btn-outline-primary w-100">Apply Filters</button>
    </div>
  </div>

  <!-- Table -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Plan Ref No</th>
          <th>Title</th>
          <th>Year</th>
          <th>Items</th>
          <th>Estimated Cost (KES)</th>
          <th>Status</th>
          <th>Created By</th>
          <th>Created On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($plans as $key => $plan)
          @php
            $itemsCount = $plan->lineItems->count();
            $estimatedCost = $plan->lineItems->sum(fn($item) => $item->MergedQty * $item->EstimatedUnitCost);
          @endphp
          <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $plan->ReferenceNumber }}</td>
            <td>{{ $plan->Title }}</td>
            <td>{{ $plan->FiscalYear }}</td>
            <td>{{ $itemsCount }}</td>
            <td>{{ number_format($estimatedCost, 2) }}</td> <!-- Use $estimatedCost, not $plan->estimatedCost -->
            <td>
              @php
                $badgeClass = match($plan->Status) {
                  'Approved' => 'bg-success',
                  'Pending Approval' => 'bg-warning',
                  'Draft' => 'bg-secondary',
                  default => 'bg-light'
                };
              @endphp
              <span class="badge {{ $badgeClass }}">{{ $plan->Status }}</span>
            </td>
            <td>{{ $plan->createdBy->Name ?? 'N/A' }}</td>
            <td>
              @if($plan->CreatedDate)
                {{ (new DateTime($plan->CreatedDate))->format('Y-m-d') }}
              @else
                N/A
              @endif
            </td> 
            <td>
              <a href="#" class="btn btn-sm btn-outline-primary">View</a>
              <a href="#" class="btn btn-sm btn-outline-success">Edit</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection