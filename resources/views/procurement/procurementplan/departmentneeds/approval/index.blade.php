@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Department Needs Approval')
@section('content')
    <div class="card p-4 shadow rounded-4">
        <style>
            /* Fit content at 100% zoom without overlapping */
            .approval-table-wrapper { overflow-x: auto; }
            .approval-table { font-size: 0.9rem; }
            .approval-table th,
            .approval-table td { white-space: normal; word-break: break-word; vertical-align: middle; }
            .approval-table th { font-weight: 600; }
            /* Slightly tighter padding to squeeze columns without truncation */
            .approval-table th, .approval-table td { padding: .5rem .6rem; }
        </style>
        <h4 class="mb-4">✅ Departmental/Branch Needs - Approval Queue</h4>

    <div class="approval-table-wrapper table-responsive">
    <table id="needs-approval" class="table table-hover table-bordered approval-table">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Need Id</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Est. Unit Cost</th>
                <th>Est. Cost</th>
                <th>Submitted By</th>
                <th>Submitted On</th>
                <th>Date Needed</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <!-- Sample Row -->
            @forelse ($NeedsApprovalviews as $index => $NeedsApprovalview)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $NeedsApprovalview->NeedID }}</td>
                    <td>{{ $NeedsApprovalview->item->ItemName ?? 'N/A' }}</td>
                    <td>{{ $NeedsApprovalview->item->category->Name ?? 'N/A' }}</td>
                    <td>{{ $NeedsApprovalview->RequestedQty }}</td>
                    <td>
                        {{ is_numeric($NeedsApprovalview->EstimatedUnitCost ?? null)
                            ? number_format($NeedsApprovalview->EstimatedUnitCost, 2, '.', ',')
                            : 'N/A' }}
                    </td>
                    <!-- <td>{{ $NeedsApprovalview->EstimatedUnitCost }}</td> -->

                    <!-- Include the est. cost as estimatedcost * requestedqty  -->
                    <td>{{isset($NeedsApprovalview->RequestedQty, $NeedsApprovalview->EstimatedUnitCost)
          ? number_format($NeedsApprovalview->RequestedQty * $NeedsApprovalview->EstimatedUnitCost, 2, '.', ',')
      : 'N/A'}}</td>
                    <td>{{ $NeedsApprovalview->creator->Name }}</td>
                    <td>{{ Carbon::parse($NeedsApprovalview->CreatedOn)->format('d/m/Y') }}</td>
                    <td>{{ Carbon::parse($NeedsApprovalview->RequestedDate)->format('d/m/Y') }}</td>
                    <td>
                        <a class="btn btn-sm btn-primary" href="{{ route('department-need-approval.show', $NeedsApprovalview->Id) }}">View to Approve</a>
                    </td>
                </tr>
            @empty
            @endforelse
            <!-- Loop rows dynamically -->
            </tbody>
        </table>
        </div>
    </div>

    {{-- DataTables CSS for consistent sizing --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#needs-approval').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                autoWidth: false, // prevent width auto-calcs that may overflow
                language: {
                    emptyTable: 'No submissions found.'
                }
            });
        });
    </script>

@endsection
