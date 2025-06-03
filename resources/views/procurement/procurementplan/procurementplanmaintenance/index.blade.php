@extends('layouts.app')
@section('title', 'Consolidated Procurement Plans')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')

    <div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📄 Consolidated Procurement Plans</h4>
    <a href="{{ route('procurementplanmaintain.create') }}" class="btn btn-success btn-sm">+ New Plan</a>
  </div>

  <!-- Table -->
  <div class="table-responsive">
      <table id="procplanmaintainTable" class="table table-bordered table-striped align-middle">
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
            <td><span class="badge bg-{{ $plan->Status->badgeColor() }}">{{ $plan->Status->label() }}</span></td>
            <td>{{ $plan->createdBy->Name ?? 'N/A' }}</td>
            <td>
              @if($plan->CreatedDate)
                {{ (new DateTime($plan->CreatedDate))->format('Y-m-d') }}
              @else
                N/A
              @endif
            </td> 
            <td>
              <a href="{{ route('procurementplanmaintain.show', $plan->PlanID) }}" class="btn btn-sm btn-outline-primary">View</a>
              <a href="{{ url('/planning/edit-draft/' . $plan->PlanID) }}" class="btn btn-sm btn-outline-success">Edit</a>
            </td>
          </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            @if(!$plans->isEmpty())
            $('#procplanmaintainTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                language: {
                    emptyTable: ""
                }
            });
            @endif
        });
    </script>
@endsection
