@extends('layouts.app')

@section('title', 'View Stock Transaction')

@section('content')
<div class="container">
    <h4 class="mb-4">Transaction Details</h4>

    <a href="{{ route('transactionsapproval.index') }}" class="btn btn-secondary mb-3">← Back to Approvals</a>

    <div class="card">
        <div class="card-header">
            {{ $transactionType }} Details
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    @if($transactionType === 'Stock Transfer')
                        <tr>
                            <th>Reference No</th>
                            <td>{{ $record->TransferID ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>From Branch</th>
                            <td>{{ $record->fromBranch->Name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>To Branch</th>
                            <td>{{ $record->toBranch->Name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Initiated By</th>
                            <td>{{ $record->transferredBy->Name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ \Carbon\Carbon::parse($record->CreatedOn)->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @php
                                    $statusEnum = \App\Enums\Inventory\Transfers::tryFrom($record->Status);
                                @endphp
                                <span class="badge bg-{{ $statusEnum?->badgeColor() }}">{{ $statusEnum?->label() ?? $record->Status }}</span>
                            </td>
                        </tr>
                    @elseif($transactionType === 'Stock Adjustment')
                        <tr>
                            <th>Reference No</th>
                            <td>{{ $record->AdjustmentId ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Branch</th>
                            <td>{{ $record->branch->Name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Adjusted By</th>
                            <td>{{ $record->adjustedBy->Name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ \Carbon\Carbon::parse($record->CreatedOn)->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>{{ $record->Status ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Remarks</th>
                            <td>{{ $record->Remarks ?? 'None' }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            @if(isset($record->items) && count($record->items) > 0)
                <h5 class="mt-4">Items</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>UOM</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($record->items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
                                    <td>{{ $item->Quantity ?? 'N/A' }}</td>
                                    <td>{{ $item->item->uom->Name ?? 'N/A' }}</td>
                                    <td>{{ $item->Remarks ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-3 text-muted">No items listed for this transaction.</p>
            @endif
        </div>
    </div>
</div>
@endsection
