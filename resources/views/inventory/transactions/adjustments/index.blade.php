@extends('layouts.app')

@section('title', 'Stock Adjustments List')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
    @php
        use App\Enums\Inventory\Transfers;
        use Carbon\Carbon;
    @endphp

    <div class="container bg-white shadow-sm rounded p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">

        <a href="{{ route('transactionsadjustment.create') }}" class="btn btn-success">➕ New Adjustment</a>
    </div>

    <div class="table-responsive">
        <table id="adjustmentTable" class="table table-bordered table-striped align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Adjustment Id</th>
                <th>Date</th>
                <th>Store</th>
                <th>Reason</th>
                <th>Adjusted By</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($adjustments as $index => $adjustment)
                @php
                    $statusEnum = Transfers::tryFrom($adjustment->Status);
                @endphp
                <tr>
                    <td>{{ $adjustments->firstItem() + $index }}</td>
                    <td>{{ $adjustment->AdjustmentId}}</
                    >
                    <td>{{ Carbon::parse($adjustment->AdjustmentDate)->format('d/m/Y') }}</td>
                    <td>{{ optional($adjustment->branch)->Name ?? 'N/A' }}</td>
                    <td>{{ optional($adjustment->reason)->Description ?? 'N/A' }}</td>
                    <td>{{$adjustment->adjustedBy->Name ?? 'N/A'}}</td>
                    <td>
                        @if($statusEnum)
                            <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                    {{ $statusEnum->label() }}
                                </span>
                        @else
                            <span class="badge bg-secondary">Unknown</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('transactionsadjustment.show', $adjustment->Id) }}"
                           class="btn btn-sm btn-primary">View</a>

                        @if($statusEnum === Transfers::Pending)
                            <a href="{{ route('transactionsadjustment.edit', $adjustment->Id) }}"
                               class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('transactionsadjustment.destroy', $adjustment->Id) }}" method="POST"
                                  class="d-inline" onsubmit="return confirm('Delete this adjustment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No stock adjustments found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

        <div class="mt-3">
            {{ $adjustments->links() }}
        </div>
    </div>

    @section('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                @if(!$adjustments->isEmpty())
                $('#adjustmentTable').DataTable({
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

@endsection
