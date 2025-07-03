@extends('layouts.app')

@section('title', 'Transfer Details')

@section('content')
    <div class="container">
        <h4 class="mb-4">Transfer No. - {{ $transferitem->TransferID }}</h4>

        <div class="card mb-4 shadow">
            <div class="card-body">
                <p><strong>Transfer
                        Date:</strong>{{ $transferitem->TransferDate ? \Carbon\Carbon::parse($transferitem->TransferDate)->format('d/m/Y') : 'N/A' }}
                </p>
                <p><strong>From Branch:</strong> {{ $transferitem->fromBranch->Name ?? 'N/A' }}</p>
                <p><strong>To Branch:</strong> {{ $transferitem->toBranch->Name ?? 'N/A' }}</p>
                <p><strong>Transferred By:</strong> {{ $transferitem->transferredBy->Name ?? 'N/A' }}</p>
            </div>
        </div>

        <h4 class="mb-3">Transferred Items</h4>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Approved Qty</th>
                    <th>Dispatched Qty</th>
                    <th>UOM</th>
                    <th>Remarks</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($transferitem->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
                        <td>{{ $item->ApprovedQty ?? 'N/A' }}</td>
                        <td>{{ $item->DispatchedQty ?? 'N/A' }}</td>
                        <td>{{ $item->uom->Code ?? 'N/A' }}</td>
                        <td>{{ $item->Remarks ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No items found for this transfer.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('transactionstransfers.index') }}" class="btn btn-secondary mt-3">Back</a>
        <a href="{{ route('transactionstransfers.edit', $transferitem->Id) }}" class="btn btn-warning mt-3">Edit</a>
        <form action="{{ route('transactionstransfers.destroy', $transferitem->Id) }}" method="POST"
              style="display:inline;">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger mt-3" onclick="return confirm('Delete this transfer?')">Delete</button>
        </form>
    </div>
@endsection
