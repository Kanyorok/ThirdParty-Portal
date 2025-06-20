@extends('layouts.app')
@section('title', '📂 Consolidated Procurement Plans')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')

    <div class="container mt-4">
        <div class="d-flex align-items-center mb-3">
            <button type="button" class="btn btn-outline-success btn-sm ms-auto" data-bs-toggle="modal"
                    data-bs-target="#newPlanModal">
                + New Plan
            </button>
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
          <th>Estimated Total Cost (KES)</th>
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
                <a href="{{ route('procurementplanmaintain.show', $plan->PlanID) }}"
                   class="btn btn-sm btn-outline-primary">
                    @if($plan->Status->value === 'Dr')
                        View to Add Items
                    @elseif(in_array($plan->Status->value, ['Su', 'Ap']))
                        View
                    @else
                        View
                    @endif
                </a>
               @if($plan->Status->value === 'Dr')
                  <a href="{{ route('planning.editDraftItems', ['PlanID' => $plan->PlanID]) }}" class="btn btn-sm btn-outline-success">Edit</a>
                @endif
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
    <!-- New Plan Modal -->
    <div class="modal fade" id="newPlanModal" tabindex="-1" aria-labelledby="newPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="newPlanModalLabel">🧾 Create Head Office Procurement Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('procurementplanmaintain.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Plan Title</label>
                            <input type="text" class="form-control" name="Title"
                                   placeholder="e.g. Annual Procurement Plan - 2025">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Plan Year</label>
                                <input type="text" class="form-control" id="fiscalYearPicker" name="FiscalYear"
                                       placeholder="Select year">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="Draft" readonly>
                                <input type="hidden" name="Status" value="Draft">
                            </div>

                            <input type="hidden" name="CreatedBy" value="{{ auth()->user()->Id }}">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary">Save Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        flatpickr("#fiscalYearPicker", {
            dateFormat: "Y",  // only year
            defaultDate: new Date().getFullYear().toString(),
            onReady: function (selectedDates, dateStr, instance) {
                instance.currentYearElement.disabled = false;
            },
        });
    </script>
@endsection
