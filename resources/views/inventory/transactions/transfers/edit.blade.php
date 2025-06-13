@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Transfer - {{ $transferitem->TransferID }}</h2>

    {{-- Fix for the action URL: Use $transferitem->Id directly without quotes --}}
    <form method="POST" action="{{ route('transactionstransfers.update', $transferitem->Id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="TransferDate" class="form-label">Transfer Date</label>
            <input type="date" name="TransferDate" class="form-control" value="{{ old('TransferDate', $transferitem->TransferDate) }}">
        </div>

        <div class="mb-3">
            <label for="transferredBy" class="form-label">Transferred By</label>
            <input type="text" name="TransferredBy" class="form-control" value="{{ old('TransferredBy', $transferitem->TransferredBy) }}">
        </div>

        <h5 class="mb-3">Transferred Items</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Item Code</th> {{-- New: Added Item Code column --}}
                        <th>Approved Qty</th>
                        <th>Dispatched Qty</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transferitem->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{-- Display Item Name --}}
                                <input type="hidden" name="items[{{ $index }}][item]" value="{{ $item->Item }}">
                                <input type="text" class="form-control" value="{{ $item->item->ItemName ?? 'N/A' }}" readonly>
                            </td>
                            <td>
                                {{-- Display Item Code --}}
                                <input type="text" class="form-control" value="{{ $item->item->ItemCode ?? 'N/A' }}" readonly>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][ApprovedQty]" class="form-control" value="{{ old("items.$index.ApprovedQty", $item->ApprovedQty) }}" required>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][DispatchedQty]" class="form-control" value="{{ old("items.$index.DispatchedQty", $item->DispatchedQty) }}" required>
                            </td>
                                <input type="text" name="items[{{ $index }}][Remarks]" class="form-control" value="{{ old("items.$index.Remarks", $item->Remarks) }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary">Update Transfer</button>
        <a href="{{ route('transactionstransfers.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection