@extends('layouts.app')

@section('title', 'Receipt Details')

@section('content')
<div class="container">
    <h4 class="mb-4">Receipt No. - {{ $receipt->ReceiptId ?? 'N/A' }}</h4>

    <div class="card mb-4 shadow">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <strong>Received Date:</strong><br> {{ $receipt->ReceivedDate ?? 'N/A' }}
                </div>
                <div class="col-md-4 mb-2">
                    <strong>Received By:</strong><br> {{ $receipt->ReceivedBy ?? 'N/A' }}
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
                    <th>Discrepancy</th> {{-- ✅ Added --}}
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
        <a href="{{ route('transactionsreceipts.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('transactionsreceipts.edit', $receipt->Id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('transactionsreceipts.destroy', $receipt->Id) }}" method="POST" onsubmit="return confirm('Delete this receipt?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>
@endsection
