@extends('layouts.app')
@section('title', 'Link Procurement Items to Budget Lines')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">

  <!-- Plan Summary -->
  <div class="mb-4 p-3 border rounded bg-light">
    <p><strong>Plan:</strong> Annual Procurement Plan - 2025</p>
    <p><strong>Status:</strong> DRAFT</p>
    <p><strong>Total Items:</strong> 15 | <strong>Unassigned Methods:</strong> 9</p>
  </div>

  <form method="POST" action="{{ route('planning.assign.methods.store') }}">
    @csrf
    <div class="table-responsive">
      <table id="linkbudgetTable" class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Item</th>
            <th>Branch</th>
            <th>Dept</th>
            <th>Qty</th>
            <th>Est. Cost</th>
            <th>Budget Line</th>
          </tr>
        </thead>
        <tbody>
            @foreach($draftItems as $index => $item)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
              <td>{{ $item->branch->Name ?? 'N/A' }}</td>
              <td>{{ $item->department->Name ?? 'N/A' }}</td>
              <td>{{ $item->MergedQty }}</td>
              <td>{{ number_format($item->MergedQty * $item->EstimatedUnitCost, 2) }}</td>
              <td>
                <select name="budgetLine_{{ $item->LineItemID }}" class="form-select">
                  <option selected disabled>Select Budget Line</option>
                  @foreach($budgetLines as $budgetLine)
                    <option value="{{ $budgetLine->BudgetLineID }}">{{ $budgetLine->Description }}</option>
                  @endforeach
                </select>
                <input type="hidden" name="lineItemIds[]" value="{{ $item->LineItemID }}">
              </td>
            </tr>
            @endforeach
          </tbody>

      </table>
    </div>

    <!-- Submission -->
    <div class="d-flex justify-content-end mt-3">
      <button type="submit" class="btn btn-primary">
        💾 Save Procurement Methods
      </button>
    </div>
  </form>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        @if(!$draftItems->isEmpty())
        $('#linkbudgetTable').DataTable({
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