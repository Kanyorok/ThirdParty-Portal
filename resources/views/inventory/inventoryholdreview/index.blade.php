@extends('layouts.app')

@section('title', 'Inventory Review')
@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

@section('content')
<div class="container bg-white p-4 rounded shadow">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Inventory Review List</h4>
        <a href="{{ route('inventoryholdreview.create') }}" class="btn btn-primary">Review New Item</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <div class="table-responsive">
                <table id="reviewTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>InventoryHold ID</th>  
                    <th>Item</th>
                    <th>UOM</th>
                    <th>From Branch</th>
                    <th>Quantity</th>
                    <th>Defect</th>
                    <th>Status</th>
                    <th>Condition</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holds as $hold)
                   <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                            {{ $hold->inventoryHold->InventoryHoldID ?? $hold->InventoryHoldID }}
                        </td>

                    <td>{{ $hold->item->ItemName ?? '-' }}</td>
                    <td>{{ $hold->item->uom->Code ?? '-' }}</td>
                    <td>{{ $hold->fromBranch->Name ?? '-' }}</td>
                    <td>{{ number_format($hold->Quantity, 2) }}</td>
                    <td>{{ $hold->defectDetail->Description ?? 'N/A' }}</td>
                    <td>
                        @php
                            $statusEnum = \App\Enums\Inventory\Transfers::tryFrom($hold->Status);
                        @endphp
                        @if($statusEnum)
                            <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                {{ $statusEnum->label() }}
                            </span>
                        @else
                            <span class="badge bg-warning">{{ $hold->Status ?? 'Unknown' }}</span>
                        @endif
                    </td>
                    <td>{{ $hold->Condition ?? '-' }}</td>
                     <td>{{ $hold->Actions }}
                                    <a href="{{ route('inventoryholdreview.show', $hold->Id) }}"
                                       class="btn btn-warning btn-sm">View</a>
                    </td>
                </tr>

                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No inventory reviews found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
       $(document).ready(function () {
        @if(!$holds->isEmpty())
        $('#reviewTable').DataTable({
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
</div>  
@endsection
