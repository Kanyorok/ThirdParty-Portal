@extends('layouts.app')
@section('title', 'Edit Stock Adjustment')

@section('content')
<div class="container bg-white shadow rounded p-4">
    <h4 class="mb-4">Edit Stock Adjustment - {{ $adjustment->AdjustmentId }}</h4>

    <form method="POST" action="{{ route('transactionsadjustment.update', $adjustment->Id) }}">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="adjustmentDate" class="form-label">Adjustment Date</label>
                <input type="date" class="form-control" id="adjustmentDate" name="AdjustmentDate"
                       value="{{ old('AdjustmentDate', $adjustment->AdjustmentDate ? \Carbon\Carbon::parse($adjustment->AdjustmentDate)->format('Y-m-d') : '') }}"
                       required>
            </div>
            <div class="col-md-4">
                <label for="branch" class="form-label">Branch</label>
                <select class="form-select" id="branch" name="Branch" required>
                    <option selected disabled>Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->Id }}" {{ $adjustment->Branch == $branch->Id ? 'selected' : '' }}>
                            {{ $branch->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="reason" class="form-label">Adjustment Reason</label>
                <select class="form-select" id="reason" name="Reason" required>
                    <option {{ $adjustment->Reason == 'Damage' ? 'selected' : '' }}>Damage</option>
                    <option {{ $adjustment->Reason == 'Expired' ? 'selected' : '' }}>Expired</option>
                    <option {{ $adjustment->Reason == 'Shrinkage' ? 'selected' : '' }}>Shrinkage</option>
                    <option {{ $adjustment->Reason == 'Stock Found' ? 'selected' : '' }}>Stock Found</option>
                    <option {{ $adjustment->Reason == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
        </div>

        <h5 class="mb-3">Adjustment Items</h5>

        <div class="table-responsive mb-3">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Current Qty</th>
                        <th>Adjustment Qty</th>
                        <th>New Qty</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($adjustment->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <input type="text" class="form-control" value="{{ $item->item->ItemCode ?? '' }}" readonly>
                                <input type="hidden" name="items[{{ $index }}][Item]" value="{{ $item->Item }}">
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{ $item->item->ItemName ?? '' }}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control current-qty" value="{{ $item->stockItem->CurrentQty ?? 0 }}" readonly>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][AdjustmentQty]" class="form-control adjustment-qty"
                                       value="{{ $item->AdjustmentQty }}" onchange="calculateNewQty(this)" required>
                            </td>
                            <td>
                                <input type="number" class="form-control new-qty" readonly>
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][Remarks]" class="form-control" value="{{ $item->Remarks }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mb-3">
                    <label for="AdjustedBy" class="form-label">Adjusted By</label>
                    <select name="AdjustedBy" id="AdjustedBy" class="form-select select2" required>
                        <option value="">-- Select User --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->Id }}"
                                {{ old('AdjustedBy', $adjustment->AdjustedBy) == $user->Id ? 'selected' : '' }}>
                                {{ $user->Name }}
                            </option>
                        @endforeach
                    </select>
                </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('transactionsadjustment.index') }}" class="btn btn-outline-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Update Adjustment</button>
        </div>
    </form>
</div>

<script>
document.getElementById('branch').addEventListener('change', function () {
    const branchId = this.value;
    if (!branchId) return;

    fetch(`/inventory/branch-stock/${branchId}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('tbody');
            tbody.innerHTML = ''; // Clear existing items

            data.forEach((stock, index) => {
                tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <input type="text" class="form-control" value="${stock.item?.ItemCode ?? ''}" readonly>
                            <input type="hidden" name="items[${index}][Item]" value="${stock.ItemID}">
                        </td>
                        <td>
                            <input type="text" class="form-control" value="${stock.item?.ItemName ?? ''}" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control current-qty" value="${stock.CurrentQty}" readonly>
                        </td>
                        <td>
                            <input type="number" name="items[${index}][AdjustmentQty]" class="form-control adjustment-qty"
                                   placeholder="+/-" onchange="calculateNewQty(this)">
                        </td>
                        <td>
                            <input type="number" class="form-control new-qty">
                        </td>
                        <td>
                            <input type="text" name="items[${index}][Remarks]" class="form-control" placeholder="Optional remarks">
                        </td>
                    </tr>
                `;
            });
        })
        .catch(error => console.error('Error fetching items:', error));
});

function calculateNewQty(input) {
    const row = input.closest('tr');
    const currentQty = parseFloat(row.querySelector('.current-qty').value) || 0;
    const adjustmentQty = parseFloat(input.value) || 0;
    const newQty = currentQty + adjustmentQty;
    row.querySelector('.new-qty').value = newQty;
}
</script>
@endsection
