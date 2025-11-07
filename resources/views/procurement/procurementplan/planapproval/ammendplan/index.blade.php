@extends('layouts.app')

@section('title', 'Edit Draft Plan Items')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>✏️ Edit Draft Plan Items </h4>
      <a href="/planning" class="btn btn-sm btn-outline-secondary" hidden></a>
  </div>

  <!-- Summary -->
    @if($PlanID && $selectedPlan = $availablePlans->firstWhere('PlanID', $PlanID))
        <div class="alert alert-info">
            <strong>Status:</strong> {{ strtoupper($selectedPlan->Status->name ?? 'Draft') }}|
            <strong>Total Items:</strong> {{ $draftItems->count() }} |
            <strong>Editable:</strong> {{ $selectedPlan->IsEditable ? 'Yes' : 'Yes' }}
        </div>
    @else
        <div class="alert alert-warning">
            ℹ️ No plan selected. Please choose a draft plan to view summary.
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ⚠️ {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <!-- Plan selection form -->
    <form method="GET" action="{{ route('planning.editDraftItems') }}" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <label for="PlanID" class="form-label">Selected Draft Plan</label>
                <select name="PlanID" id="PlanID" class="form-select" readonly disabled>
                    @if($PlanID && $selectedPlan = $availablePlans->firstWhere('PlanID', $PlanID))
                        <option value="{{ $selectedPlan->PlanID }}" selected>{{ $selectedPlan->Title ?? 'Unnamed Plan' }}</option>
                    @else
                        <option value="">-- Choose Draft Plan --</option>
                    @endif
                </select>
                <input type="hidden" name="PlanID" value="{{ $PlanID }}">
            </div>
        </div>
    </form>


    <form method="POST" action="{{ route('planning.updateDraftItems') }}">
        @csrf
        @foreach($draftItems as $item)
            <input type="hidden" name="lineItemIds[]" value="{{ $item->LineItemID }}">
        @endforeach

    <div class="table-responsive">
        <table id="amendTable" class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Item</th>
            <th>From Branch</th>
            <th>Original Qty</th>
              <th>Current Qty</th>
              <th>Adjust Qty</th>
            <th>Unit Cost</th>
            <th>Total</th>
              <th class="d-none">Remarks</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        @foreach($draftItems as $index => $item)
          <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
              <td>{{ $item->branch->Name ?? 'N/A' }}</td>
              <td>{{ $item->OriginalQTY }}</td>
              <td>{{ $item->MergedQty }}</td>
              <td>
                  <input type="number" class="form-control form-control-sm" name="qty_{{ $item->LineItemID }}"
                         value="{{ $item->MergedQty }}" min="1" max="{{ $item->OriginalQTY }}">
              </td>
            <td>
                <input type="number" class="form-control form-control-sm" name="unitCost_{{ $item->LineItemID }}"
                       value="{{ $item->EstimatedUnitCost }}" min="0" step="0.01">
            </td>
            <td>
                <span class="text-muted">{{ number_format($item->MergedQty * $item->EstimatedUnitCost) }}</span>
            </td>
              <td class="d-none">
                <textarea name="remarks_{{ $item->LineItemID }}" class="form-control form-control-sm"
                          rows="1">{{ $item->ChangeRemarks }}</textarea>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="confirmDelete({{ $item->LineItemID }})">
                    🗑 Remove
                </button>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-end mt-3">
      <button type="submit" class="btn btn-success">
        💾 Save Changes to Draft Plan
      </button>
    </div>
  </form>

    <!-- Hidden Delete Forms -->
    @foreach($draftItems as $item)
        <form id="delete-form-{{ $item->LineItemID }}" method="POST"
              action="{{ route('procurement.planning.deleteDraftItem', ['id' => $item->LineItemID]) }}"
              style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
</div>

<script>
    function confirmDelete(id) {
        if (confirm("⚠️ Are you sure you want to delete this draft item? This action cannot be undone.")) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        @if(!$draftItems->isEmpty())
        $('#amendTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            columnDefs: [
                {
                    targets: [8],
                    visible: false,
                    searchable: false
                }
            ],
            language: {
                emptyTable: ""
            }
        });
        @endif
    });
</script>
@endsection
