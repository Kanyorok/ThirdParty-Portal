@extends('layouts.app')
@section('title', 'Raise Need')
@section('styles') <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">@endsection
@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-4">📂 My Department's Procurement Needs</h4>
        <a href="{{ route('procurementdepartmentalplan.create') }}" class="btn btn-success">+ Add Need</a>
    </div>

    <table id="departmentneedsTable" class="table table-bordered table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Est. Cost</th>
                <th>Status</th>
                <th>Required By</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
                @forelse ($departmentneedviews as $index => $departmentneedview)
        <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $departmentneedview->item->ItemName ?? 'N/A' }}</td>
        <td>{{ $departmentneedview->item->category->Name ?? 'N/A' }}</td>
        <td>{{ $departmentneedview->RequestedQty }}</td>
        <td>{{ $departmentneedview->EstimatedUnitCost }}</td>
        <td>{{ $departmentneedview->Status }}</td>
        <td>{{ $departmentneedview->creator->Name}}</td>
        <td>
        <a href="{{ route('procurementdepartmentalplan.view', $departmentneedview->NeedID) }}" class="btn btn-sm btn-outline-info">View</a>
        <a href="{{ route('procurementdepartmentalplan.updateLine', $departmentneedview->NeedID) }}" class="btn btn-sm btn-outline-primary">Edit</a>
        </td>
        </tr>
                        @empty
        <tr>
        <td colspan="9" class="text-center">No submissions found.</td>
        </tr>
                @endforelse
</tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
 
<script>

    $(document).ready(function () {

        $('#departmentneedsTable').DataTable({

            pageLength: 10,

            ordering: true,

            searching: true,

            lengthChange: true

        });

    });
</script>
 
@endpush
