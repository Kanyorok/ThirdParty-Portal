@extends('layouts.app')
@section('title', 'Create Stock Adjustment')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some issues with your submission:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<div class="container bg-white shadow rounded p-4">
    <h4 class="mb-4">Stock Adjustment Form</h4>

    <form method="POST" action="{{ route('transactionsadjustment.store') }}">
        @csrf
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="adjustmentDate" class="form-label">Adjustment Date</label>
                <input type="date" 
                    class="form-control @error('AdjustmentDate') is-invalid @enderror"
                    id="adjustmentDate" 
                    name="AdjustmentDate" 
                    value="{{ old('AdjustmentDate', now()->format('Y-m-d')) }}" 
                    required>
                @error('AdjustmentDate')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="branch" class="form-label">Branch</label>
                <input type="hidden" id="branch" name="Branch" value="{{ $branch->Id }}">
                <input type="text" class="form-control" value="{{ $branch->Name }}" readonly>
                @error('Branch')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>UOM</th>
                    <th>Unit Cost</th>
                    <th>Current Qty</th>
                    <th>Adjustment Qty</th>
                    <th>New Qty</th>
                    <th>Adjustment Reason</th>
                    <th>Remarks</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
    @php $oldItems = old('items'); @endphp

    @if ($oldItems)
        {{-- repopulate old values on validation failure --}}
        @foreach ($oldItems as $index => $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <input type="text" class="form-control" value="{{ $item['ItemCode'] ?? '' }}" readonly>
                    <input type="hidden" name="items[{{ $index }}][Item]" value="{{ $item['Item'] ?? '' }}">
                    <input type="hidden" name="items[{{ $index }}][ItemCode]" value="{{ $item['ItemCode'] ?? '' }}">
                </td>
                <td>
                    <input type="text" class="form-control" value="{{ $item['ItemName'] ?? '' }}" readonly>
                </td>
                <td>
                    <input type="text" class="form-control" value="{{ $item['UOMCode'] ?? '' }}" readonly>
                    <input type="hidden" name="items[{{ $index }}][UOM]" value="{{ $item['UOM'] ?? '' }}">
                </td>
                <td>
                    <input type="number" class="form-control" value="{{ $item['UnitCost'] ?? '' }}" readonly>
                </td>
                <td>
                    <input type="number" class="form-control current-qty" value="{{ $item['CurrentQty'] ?? 0 }}" readonly>
                </td>
                <td>
                    <input type="number" step="any" name="items[{{ $index }}][AdjustmentQty]"
                           class="form-control adjustment-qty @error("items.$index.AdjustmentQty") is-invalid @enderror"
                           value="{{ $item['AdjustmentQty'] ?? '' }}" onchange="calculateNewQty(this)">
                    <div class="invalid-feedback adjustment-qty-feedback">
                        @error("items.$index.AdjustmentQty") {{ $message }} @enderror
                    </div>
                </td>
                <td>
                    <input type="number" class="form-control new-qty" readonly>
                </td>
                <td>
                    <select name="items[{{ $index }}][Reason]" 
                            class="form-select @error("items.$index.Reason") is-invalid @enderror" required>
                        <option value="">Select Reason</option>
                        @foreach($reasons as $reason)
                            <option value="{{ $reason->ID }}" 
                                {{ (old("items.$index.Reason") ?? $item['Reason'] ?? '') == $reason->ID ? 'selected' : '' }}>
                                {{ $reason->Description }}
                            </option>
                        @endforeach
                    </select>
                    @error("items.$index.Reason")
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </td>
                <td>
                    <input type="text" name="items[{{ $index }}][Remarks]"
                           class="form-control @error("items.$index.Remarks") is-invalid @enderror"
                           value="{{ $item['Remarks'] ?? '' }}" placeholder="Optional remarks">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-row">Remove</button>
                </td>
            </tr>
        @endforeach
    @else
        {{-- preload branch stock items on first load --}}
        @foreach ($stockItems as $index => $stock)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <input type="text" class="form-control" value="{{ $stock->item->ItemCode ?? '' }}" readonly>
                    <input type="hidden" name="items[{{ $index }}][Item]" value="{{ $stock->ItemID }}">
                    <input type="hidden" name="items[{{ $index }}][ItemCode]" value="{{ $stock->item->ItemCode ?? '' }}">
                </td>
                <td>
                    <input type="text" class="form-control" value="{{ $stock->item->ItemName ?? '' }}" readonly>
                    <input type="hidden" name="items[{{ $index }}][ItemName]" value="{{ $stock->item->ItemName ?? '' }}">
                </td>
                <td>
                    <input type="text" class="form-control" value="{{ $stock->uom->Code ?? '' }}" readonly>
                    <input type="hidden" name="items[{{ $index }}][UOM]" value="{{ $stock->uom->Id ?? '' }}">
                </td>
                <td>
                    <input type="number" class="form-control" value="{{ $stock->UnitCost }}" readonly>
                    <input type="hidden" name="items[{{ $index }}][UnitCost]" value="{{ $stock->UnitCost }}">
                </td>
                <td>
                    <input type="number" class="form-control current-qty" value="{{ $stock->CurrentQty }}" readonly>
                    <input type="hidden" name="items[{{ $index }}][CurrentQty]" value="{{ $stock->CurrentQty }}">
                </td>
                <td>
                    <input type="number" step="any" class="form-control adjustment-qty"
                           name="items[{{ $index }}][AdjustmentQty]" onchange="calculateNewQty(this)">
                    <div class="invalid-feedback adjustment-qty-feedback"></div>
                </td>
                <td>
                    <input type="number" class="form-control new-qty" readonly>
                </td>
                <td>
                    <select name="items[{{ $index }}][Reason]" class="form-select" required>
                        <option value="">Select Reason</option>
                        @foreach($reasons as $reason)
                            <option value="{{ $reason->ID }}">{{ $reason->Description }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control" name="items[{{ $index }}][Remarks]" placeholder="Optional remarks">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-row">Remove</button>
                </td>
            </tr>
        @endforeach
    @endif
</tbody>
            </table>
        </div>

        <div class="mb-3">
            <label class="form-label">Adjusted By</label>
            <select name="AdjustedBy" class="form-select select2 @error('AdjustedBy') is-invalid @enderror" required>
                <option value="">-- Select User --</option>
                @foreach ($users as $user)
                    <option value="{{ $user->Id }}" {{ old('AdjustedBy') == $user->Id ? 'selected' : '' }}>
                        {{ $user->Name }}
                    </option>
                @endforeach
            </select>
            @error('AdjustedBy')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">✅ Submit Adjustment</button>
    </form>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

<script>
    function calculateNewQty(input) {
        const row = input.closest('tr');
        const currentQty = parseFloat(row.querySelector('.current-qty')?.value) || 0;
        const adjustmentQty = parseFloat(input.value) || 0;
        const newQty = currentQty + adjustmentQty;

        row.querySelector('.new-qty').value = newQty;

        const feedbackDiv = row.querySelector('.adjustment-qty-feedback');
        if (newQty < 0) {
            input.classList.add('is-invalid');
            feedbackDiv.textContent = `New quantity (${newQty}) cannot be negative.`;
        } else {
            input.classList.remove('is-invalid');
            feedbackDiv.textContent = '';
        }
    }

    // remove row button
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
    });

    // handle dynamic branch reload
    document.getElementById('branch').addEventListener('change', function () {
        const branchId = this.value;
        if (!branchId) return;

        fetch(`/inventory/branch-stock/${branchId}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('tbody');
                tbody.innerHTML = '';

                data.forEach((stock, index) => {
                    const item = stock.item || {};
                    const uom = stock.uom || {}; 

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>
                            <input type="text" class="form-control" value="${item.ItemCode ?? ''}" readonly>
                            <input type="hidden" name="items[${index}][Item]" value="${stock.ItemID}">
                            <input type="hidden" name="items[${index}][ItemCode]" value="${item.ItemCode ?? ''}">
                        </td>
                        <td>
                            <input type="text" class="form-control" value="${item.ItemName ?? ''}" readonly>
                            <input type="hidden" name="items[${index}][ItemName]" value="${item.ItemName ?? ''}">
                        </td>
                        <td>
                            <input type="text" class="form-control" value="${uom.Code ?? ''}" readonly>
                            <input type="hidden" name="items[${index}][UOM]" value="${uom.Id ?? ''}">
                        </td>
                        <td>
                            <input type="number" class="form-control" value="${stock.UnitCost}" readonly>
                            <input type="hidden" name="items[${index}][UnitCost]" value="${stock.UnitCost}">
                        </td>
                        <td>
                            <input type="number" class="form-control current-qty" value="${stock.CurrentQty}" readonly>
                            <input type="hidden" name="items[${index}][CurrentQty]" value="${stock.CurrentQty}">
                        </td>
                        <td>
                            <input type="number" step="any" class="form-control adjustment-qty" name="items[${index}][AdjustmentQty]" placeholder="+/-" onchange="calculateNewQty(this)">
                            <div class="invalid-feedback adjustment-qty-feedback"></div>
                        </td>
                        <td>
                            <input type="number" class="form-control new-qty" readonly>
                        </td>
                        <td>
                            <select name="items[${index}][Reason]" class="form-select" required>
                                <option value="">Select Reason</option>
                                @foreach($reasons as $reason)
                                    <option value="{{ $reason->ID }}">{{ $reason->Description }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="items[${index}][Remarks]" placeholder="Optional remarks">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-row">Remove</button>
                        </td>
                    `;
                    tbody.appendChild(row);

                    row.querySelector('.adjustment-qty').addEventListener('input', function () {
                        calculateNewQty(this);
                    });
                });
            })
            .catch(error => console.error('Error fetching stock:', error));
    });

    $(document).ready(function () {
        $('.select2').select2({ placeholder: 'Select user', allowClear: true });

        const oldBranchId = "{{ old('Branch') }}";
        const oldItems = @json(old('items'));

        if (oldBranchId && !oldItems) {
            $('#branch').val(oldBranchId).trigger('change');
        } else {
            document.querySelectorAll('.adjustment-qty').forEach(input => {
                calculateNewQty(input);
            });
        }
    });
</script>
@endsection
