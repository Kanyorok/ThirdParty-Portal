@extends('layouts.app')
@section('title', 'Department Needs Approval')
@section('content')
<div class="card p-4 shadow rounded-4">
  <h4 class="mb-4">✅ Departmental/Branch Needs - Approval Queue</h4>

  <table class="table table-hover table-bordered">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Item Name</th>
        <th>Category</th>
        <th>Quantity</th>
        <th>Est. Cost</th>
        <th>Submitted By</th>
        <th>Submitted On</th>
        <th>Required By</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Sample Row -->
      @forelse ($NeedsApprovalviews as $index => $NeedsApprovalview)
      <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $NeedsApprovalview->item->ItemName ?? 'N/A' }}</td>
          <td>{{ $NeedsApprovalview->item->category->Name ?? 'N/A' }}</td>
          <td>{{ $NeedsApprovalview->RequestedQty }}</td>
          <td>{{ $NeedsApprovalview->EstimatedUnitCost }}</td>
          <td>{{ $NeedsApprovalview->creator->Name }}</td>
          <td>{{ \Carbon\Carbon::parse($NeedsApprovalview->CreatedOn)->format('d M Y') }}</td>
          <td>{{ \Carbon\Carbon::parse($NeedsApprovalview->RequestedDate)->format('d M Y') }}</td>
        <td>

            <a class="btn btn-sm btn-primary"
               href="{{ route('department-need-approval.show', $NeedsApprovalview->Id) }}">View</a>
            <button class="btn btn-sm btn-success">Approve</button>
            <button class="btn btn-sm btn-danger">Reject</button>
            </form>
        </td>
      </tr>
      @empty
          <tr>
              <td colspan="9" class="text-center">No submissions found.</td>
          </tr>
      @endforelse
      <!-- Loop rows dynamically -->
    </tbody>
  </table>
</div>

@endsection
