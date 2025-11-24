@extends('layouts.app')
@section('title', '📂 Consolidated Procurement Plans')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        /* Compact fixed layout to fit at 100% zoom */
        .procplan-table { table-layout: fixed; width: 100%; font-size: .9rem; }
        .procplan-table th, .procplan-table td { padding: .4rem .5rem; vertical-align: middle; }
        /* Column widths */
        .col-idx{width:44px}
        .col-ref{width:140px}
        .col-title{width:260px}
        .col-year{width:80px}
        .col-items{width:80px}
        .col-cost{width:160px}
        .col-status{width:110px}
        .col-created-by{width:160px}
        .col-created-on{width:120px}
        .col-actions{width:130px;text-align:center}
        /* Truncation helpers */
        .truncate{display:inline-block;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:bottom}
        /* Numeric alignment */
        .text-num{text-align:right}
        /* Prevent badge wrap */
        .procplan-table .badge{white-space:nowrap}
    </style>
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
            <table id="procplanmaintainTable" class="table table-bordered table-striped table-sm align-middle procplan-table">
      <thead class="table-light">
        <tr>
                    <th class="col-idx">#</th>
                    <th class="col-ref">Plan Ref No</th>
                    <th class="col-title">Title</th>
                    <th class="col-year">Year</th>
                    <th class="col-items text-num">Items</th>
                    <th class="col-cost text-num">Est. Total (KES)</th>
                    <th class="col-status">Status</th>
                    <th class="col-created-by">Created By</th>
                    <th class="col-created-on">Created On</th>
                    <th class="col-actions">Actions</th>
        </tr>
      </thead>
      <tbody>
      @foreach($plans as $key => $plan)
          @php
              $itemsCount = $plan->lineItems->count();
              $estimatedCost = $plan->lineItems->sum(fn($item) => $item->MergedQty * $item->EstimatedUnitCost);
          @endphp
          <tr>
                        <td class="col-idx">{{ $key + 1 }}</td>
                        <td class="col-ref"><span class="truncate" title="{{ $plan->ReferenceNumber }}">{{ $plan->ReferenceNumber }}</span></td>
                        <td class="col-title"><span class="truncate" title="{{ $plan->Title }}">{{ $plan->Title }}</span></td>
                        <td class="col-year">{{ $plan->FiscalYear }}</td>
                        <td class="col-items text-num">{{ $itemsCount }}</td>
                        <td class="col-cost text-num">{{ number_format($estimatedCost, 2) }}</td> <!-- Use $estimatedCost, not $plan->estimatedCost -->
                            <td class="col-status"><span class="badge bg-{{ $plan->Status->badgeColor() }}">{{ $plan->Status->label() }}</span></td>
                        <td class="col-created-by"><span class="truncate" title="{{ $plan->createdBy->Name ?? 'N/A' }}">{{ $plan->createdBy->Name ?? 'N/A' }}</span></td>
                        <td class="col-created-on">
              @if($plan->CreatedDate)
                    {{ (new DateTime($plan->CreatedDate))->format('d M Y') }}
              @else
                N/A
              @endif
            </td>
                            <td class="col-actions text-center">
                                    <a href="{{ route('procurementplanmaintain.show', $plan->PlanID) }}"
                                         class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    @if($plan->Status->value === 'Dr' && $itemsCount > 0)
                                            <a href="{{ route('planning.editDraftItems', ['PlanID' => $plan->PlanID]) }}"
                                                 class="btn btn-sm btn-outline-success" title="Edit Items"><i class="fas fa-edit"></i></a>
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
                autoWidth: false,
                responsive: false,
                language: {
                    emptyTable: ""
                },
                columnDefs: [
                    { targets: [0], orderable: true, width: 44 },
                    { targets: [1], width: 140 },
                    { targets: [2], width: 260 },
                    { targets: [3], width: 80 },
                    { targets: [4], className: 'text-end', width: 80 },
                    { targets: [5], className: 'text-end', width: 160 },
                    { targets: [6], width: 110 },
                    { targets: [7], width: 160 },
                    { targets: [8], width: 120 },
                    { targets: [9], orderable: false, width: 130 }
                ]
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
