@extends('layouts.app')

@section('title', 'Edit Draft Plan Items')

@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>✏️ Edit Draft Plan Items – Annual Procurement Plan 2025</h4>
    <a href="/planning" class="btn btn-sm btn-outline-secondary">← Back to Dashboard</a>
  </div>

  <!-- Summary -->
  <div class="alert alert-info">
    <strong>Status:</strong> DRAFT | <strong>Total Items:</strong> 20 | <strong>Editable:</strong> Yes
  </div>

    <form method="POST" action="{{ route('planning.updateDraftItems') }}">
        @csrf
        @foreach($draftItems as $item)
            <input type="hidden" name="lineItemIds[]" value="{{ $item->LineItemID }}">
        @endforeach

    <div class="table-responsive">
      <table class="table table-bordered align-middle table-hover">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Item</th>
            <th>From Branch</th>
            <th>Original Qty</th>
            <th>Planned Qty</th>
            <th>Unit Cost</th>
            <th>Total</th>
            <th>Remarks</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        @foreach($draftItems as $index => $item)
          <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
              <td>{{ $item->branch->Name ?? 'N/A' }}</td>
              <td>{{ $item->MergedQty }}</td>
              <td>
                  <input type="number" class="form-control form-control-sm" name="qty_{{ $item->LineItemID }}"
                         value="{{ $item->MergedQty }}" min="1">
              </td>
            <td>
                <input type="number" class="form-control form-control-sm" name="unitCost_{{ $item->LineItemID }}"
                       value="{{ $item->EstimatedUnitCost }}" min="0" step="0.01">
            </td>
            <td>
                <span class="text-muted">{{ number_format($item->MergedQty * $item->EstimatedUnitCost) }}</span>
            </td>
            <td>
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
@endsection
