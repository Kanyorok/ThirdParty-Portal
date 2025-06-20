@extends('layouts.app')

@section('title', 'Edit Receipt')

@section('content')
    <div class="container">
        <h4 class="mb-4">Edit Receipt - {{ $receipt->ReceiptId }}</h4>

        <form action="{{ route('transactionsreceipts.update', $receipt->Id) }}" method="POST">
            @csrf
            @method('PUT')

            <input type="hidden" name="TransferID" value="{{ $receipt->TransferID }}">

            <div class="card mb-3 shadow">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="ReceivedBy" class="form-label">Received By</label>
                        <input type="text" class="form-control" name="ReceivedBy"
                               value="{{ old('ReceivedBy', $receipt->ReceivedBy) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="ReceivedDate" class="form-label">Received Date</label>
                        <input type="date" class="form-control" name="ReceivedDate"
                               value="{{ old('ReceivedDate', $receipt->ReceivedDate) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="GeneralRemarks" class="form-label">General Remarks</label>
                        <textarea class="form-control" name="GeneralRemarks"
                                  rows="3">{{ old('GeneralRemarks', $receipt->GeneralRemarks) }}</textarea>
                    </div>
                </div>
            </div>

            <h5 class="mb-3">Items</h5>
            <table class="table table-bordered" id="items-table">
                <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th>Dispatched Qty</th>
                    <th>Received Qty</th>
                    <th>Discrepancy</th>
                    <th>Damaged Qty</th>
                    <th>Remarks</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($receipt->items as $index => $item)
                    @php
                        $dispatchedQty = $receipt->transfer->items->firstWhere('Item', $item->Item)?->DispatchedQty ?? 0;
                        $discrepancy = $dispatchedQty - $item->ReceivedQty;
                    @endphp
                    <tr>
                        <td>
                            <select name="items[{{ $index }}][item]" class="form-control" required>
                                @foreach ($receipt->transfer->items as $transferItem)
                                    <option
                                        value="{{ $transferItem->Item }}" {{ $item->Item == $transferItem->Item ? 'selected' : '' }}>
                                        {{ $transferItem->item->ItemName ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control dispatched-qty" value="{{ $dispatchedQty }}"
                                   readonly>
                            <input type="hidden" name="items[{{ $index }}][dispatched_qty]"
                                   value="{{ $dispatchedQty }}">
                        </td>
                        <td>
                            <input type="number" name="items[{{ $index }}][received_qty]"
                                   class="form-control received-qty" value="{{ $item->ReceivedQty }}" required>
                        </td>
                        <td>
                            <input type="number" class="form-control discrepancy" value="{{ $discrepancy }}" readonly>
                            <input type="hidden" name="items[{{ $index }}][discrepancy]" value="{{ $discrepancy }}">
                        </td>
                        <td>
                            <input type="number" name="items[{{ $index }}][damaged_qty]" class="form-control"
                                   value="{{ $item->DamagedQty }}">
                        </td>
                        <td>
                            <input type="text" name="items[{{ $index }}][remarks]" class="form-control"
                                   value="{{ $item->Remarks }}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-item">🗑️</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-between">
                <a href="{{ route('transactionsreceipts.index') }}" class="btn btn-outline-secondary">Back</a>
                <button type="submit" class="btn btn-primary">Update Receipt</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-item')) {
                e.target.closest('tr').remove();
            }
        });

        // Update discrepancy dynamically
        document.addEventListener('input', function (event) {
            if (event.target.classList.contains('received-qty')) {
                const row = event.target.closest('tr');
                const dispatched = parseFloat(row.querySelector('.dispatched-qty').value) || 0;
                const received = parseFloat(event.target.value) || 0;
                const discrepancy = dispatched - received;
                row.querySelector('.discrepancy').value = discrepancy;
                row.querySelector('input[name*="[discrepancy]"]').value = discrepancy;
            }
        });
    </script>
@endsection
