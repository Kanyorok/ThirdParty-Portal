@extends('layouts.app')
@section('title', 'Manual Entry – Procurement Plan Items')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')

<div class="card p-4 shadow rounded-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4>📋 Manual Entry – Procurement Plan Items</h4>
    <a href="{{ route('planmanualinput.create') }}" class="btn btn-primary">
      ➕ Add Item
    </a>
  </div>

  <!-- Plan Filter -->
  <div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Select Procurement Plan</label>
        <form method="GET" action="{{ route('planmanualinput.index') }}">
            <select name="plan_id" class="form-select" onchange="this.form.submit()">
                <option disabled {{ empty($planId) ? 'selected' : '' }}>Select Plan</option>
                @foreach ($plans as $plan)
                    <option
                        value="{{ $plan->PlanID }}" {{ (isset($planId) && $planId == $plan->PlanID) ? 'selected' : '' }}>
                        {{ $plan->Title }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

      <table id="manualinputTable" class="table table-bordered table-striped align-middle">
          <thead class="table-light">
          <tr>
              <th>#</th>
              <th>Item</th>
              <th>Category</th>
              <th>Qty</th>
              <th>UOM</th>
              <th>Est. Cost</th>
              <th>Planned Quarter</th>
              <th>Expected Delivery</th>
              <th>Action</th>
          </tr>
          </thead>
          <tbody>
          @forelse ($lineItems as $index => $item)
      <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
          <td>{{ $item->item->category->Name ?? 'N/A' }}</td>
          <td>{{ $item->MergedQty }}</td>
          <td>{{ $item->item->uom->Name ?? 'N/A' }}</td>
          <td>{{ number_format($item->EstimatedUnitCost, 2) }}</td>
          <td>{{ $item->SchedulePeriod ?? 'N/A' }}</td>
          <td>{{ \Carbon\Carbon::parse($item->ExpectedDeliveryDate)->format('Y-m-d') ?? 'N/A' }}</td>
          <td>
              <a href="{{ route('planmanualinput.edit', $item->LineItemID) }}" class="btn btn-sm btn-outline-primary">Edit</a>
              <form method="POST" action="{{ route('planmanualinput.destroy', $item->LineItemID) }}"
                    style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger"
                          onclick="return confirm('Are you sure you want to delete this item?')">Delete
                  </button>
              </form>
        </td>
      </tr>
          @empty
              <tr>
                  <td colspan="9" class="text-center">No plan items found.</td>
              </tr>
          @endforelse
          </tbody>
      </table>

  </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            @if(!$lineItems->isEmpty())
            $('#manualinputTable').DataTable({
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
