@extends('layouts.app')

@section('title', 'Receipt Details')

@section('content')
    <div class="container">
        <h4 class="mb-4">Receipt No. - {{ $receipt->ReceiptId ?? 'N/A' }}</h4>

        <div class="card mb-4 shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <strong>Received
                            Date:</strong><br> {{ \Carbon\Carbon::parse($receipt->ReceivedDate)->format('d/m/Y') }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Received By:</strong> {{ $receipt->receivedBy->Name ?? 'N/A' }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Transfer Ref:</strong><br> {{ $receipt->transfer->TransferID ?? 'N/A' }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>From Branch:</strong><br> {{ $receipt->transfer->fromBranch->Name ?? 'N/A' }}
                    </div>
                    <div class="col-md-8 mb-2">
                        <strong>General Remarks:</strong><br> {{ $receipt->GeneralRemarks ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mb-3">Received Items</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Dispatched Qty</th>
                    <th>Received Qty</th>
                    <th>Discrepancy</th>
                    <th>Damaged Qty</th>
                    <th>Remarks</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($receipt->items as $index => $item)
                    @php
                        $dispatchedQty = $item->transferItem->DispatchedQty ?? 0;
                        $receivedQty = $item->ReceivedQty ?? 0;
                        $discrepancy = $dispatchedQty - $receivedQty;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
                        <td>{{ $dispatchedQty }}</td>
                        <td>{{ $receivedQty }}</td>
                        <td>{{ $discrepancy }}</td>
                        <td>{{ $item->DamagedQty ?? 0 }}</td>
                        <td>{{ $item->Remarks ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No items recorded for this receipt.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('transactionsreceipts.index') }}" class="btn btn-primary">Back</a>

        </div>
    </div>
@endsection
