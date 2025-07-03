@extends('layouts.app')
@section('title', 'Edit Stock Adjustment')

@section('content')
{{-- This section displays general validation errors from $errors->all() --}}
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
    <h4 class="mb-4">Edit Stock Adjustment - {{ $adjustment->AdjustmentId }}</h4>

    {{-- Ensure the form action points to the correct update route with the adjustment ID --}}
    <form method="POST" action="{{ route('transactionsadjustment.update', $adjustment->Id) }}">
        @csrf
        @method('PUT') {{-- Essential for PUT requests --}}

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="adjustmentDate" class="form-label">Adjustment Date</label>
                <input type="date" class="form-control @error('AdjustmentDate') is-invalid @enderror" id="adjustmentDate" name="AdjustmentDate"
                       value="{{ old('AdjustmentDate', $adjustment->AdjustmentDate ? \Carbon\Carbon::parse($adjustment->AdjustmentDate)->format('Y-m-d') : '') }}"
                       required>
                @error('AdjustmentDate')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="branch" class="form-label">Branch</label>
                <select class="form-select @error('Branch') is-invalid @enderror" id="branch" name="Branch" required>
                    <option disabled>Select Branch</option> {{-- Removed 'selected' from disabled option --}}
                    @foreach($branches as $branch)
                        <option value="{{ $branch->Id }}" {{ old('Branch', $adjustment->Branch) == $branch->Id ? 'selected' : '' }}>
                            {{ $branch->Name }}
                        </option>
                    @endforeach
                </select>
                @error('Branch')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="col-md-4">
            <label for="reason" class="form-label">Adjustment Reason</label>
            <select class="form-select @error('Reason') is-invalid @enderror" id="reason" name="Reason" required>
                <option disabled selected>Select Reason</option>
                @foreach($reasons as $reason)
                    <option value="{{ $reason->ID }}" {{ old('Reason', $adjustment->Reason) == $reason->ID ? 'selected' : '' }}>
                        {{ $reason->Description }}
                    </option>
                @endforeach
            </select>
            @error('Reason')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
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
                    {{-- Loop through items either from old input (if validation failed) or from the $adjustment object --}}
                    @php
                        $itemsToDisplay = old('items', $adjustment->items->map(function($item) {
                            // Map existing items to a format consistent with old() for easier repopulation
                            return [
                                'Item' => $item->Item,
                                'AdjustmentQty' => $item->AdjustmentQty,
                                'Remarks' => $item->Remarks,
                                'item_code' => $item->item->ItemCode ?? '', // Store for display
                                'item_name' => $item->item->ItemName ?? '', // Store for display
                                'current_stock_qty' => $item->stockItem->CurrentQty ?? 0, // Store for display/calculation
                            ];
                        })->toArray());
                    @endphp

                    @foreach ($itemsToDisplay as $index => $itemData)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <input type="text" class="form-control" value="{{ $itemData['item_code'] ?? ($itemData['Item'] ?? '') }}" readonly>
                                <input type="hidden" name="items[{{ $index }}][Item]" value="{{ $itemData['Item'] }}">
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{ $itemData['item_name'] ?? '' }}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control current-qty" value="{{ $itemData['current_stock_qty'] ?? 0 }}" readonly>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][AdjustmentQty]"
                                       class="form-control adjustment-qty @error('items.' . $index . '.AdjustmentQty') is-invalid @enderror"
                                       value="{{ old('items.' . $index . '.AdjustmentQty', $itemData['AdjustmentQty']) }}"
                                       onchange="calculateNewQty(this)" required>
                                @error('items.' . $index . '.AdjustmentQty')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </td>
                            <td>
                                {{-- New Qty will be calculated by JS on load and input change --}}
                                <input type="number" class="form-control new-qty" readonly>
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][Remarks]"
                                       class="form-control @error('items.' . $index . '.Remarks') is-invalid @enderror"
                                       value="{{ old('items.' . $index . '.Remarks', $itemData['Remarks']) }}">
                                @error('items.' . $index . '.Remarks')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        <div class="mb-3">
            <label for="AdjustedBy" class="form-label">Adjusted By</label>
            <select name="AdjustedBy" id="AdjustedBy" class="form-select select2 @error('AdjustedBy') is-invalid @enderror" required>
                <option value="">-- Select User --</option>
                @foreach ($users as $user)
                    <option value="{{ $user->Id }}"
                        {{ old('AdjustedBy', $adjustment->AdjustedBy) == $user->Id ? 'selected' : '' }}>
                        {{ $user->Name }}
                    </option>
                @endforeach
            </select>
            @error('AdjustedBy')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('transactionsadjustment.index') }}" class="btn btn-outline-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Update Adjustment</button>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
    function calculateNewQty(input) {
        const row = input.closest('tr');
        const currentQtyInput = row.querySelector('.current-qty');
        const currentQty = parseFloat(currentQtyInput.value) || 0;
        const adjustmentQty = parseFloat(input.value) || 0;
        const newQty = currentQty + adjustmentQty;
        row.querySelector('.new-qty').value = newQty;

        let feedbackDiv = row.querySelector('.adjustment-qty-feedback');
        if (!feedbackDiv) {
            feedbackDiv = document.createElement('div');
            feedbackDiv.classList.add('invalid-feedback', 'adjustment-qty-feedback');
            input.parentNode.appendChild(feedbackDiv);
        }

        if (newQty < 0) {
            input.classList.add('is-invalid');
            feedbackDiv.style.display = 'block';
            feedbackDiv.innerHTML = `New quantity (${newQty}) cannot be negative.`;
        } else {
            input.classList.remove('is-invalid');
            feedbackDiv.style.display = 'none';
            feedbackDiv.innerHTML = ''; 
        }
    }

    function reapplyCalculationsAndErrors() {
        document.querySelectorAll('tbody tr').forEach((row, index) => {
            const adjustmentQtyInput = row.querySelector('.adjustment-qty');
            const remarksInput = row.querySelector('[name="items[' + index + '][Remarks]"]');

            if (adjustmentQtyInput) {
                calculateNewQty(adjustmentQtyInput);

                const errors = @json($errors->toArray()); 
                if (errors.messages && errors.messages[`items.${index}.AdjustmentQty`]) {
                    adjustmentQtyInput.classList.add('is-invalid');
                    let feedbackDiv = row.querySelector('.adjustment-qty-feedback');
                    if (!feedbackDiv) {
                        feedbackDiv = document.createElement('div');
                        feedbackDiv.classList.add('invalid-feedback', 'adjustment-qty-feedback');
                        adjustmentQtyInput.parentNode.appendChild(feedbackDiv);
                    }
                    feedbackDiv.style.display = 'block';
                    feedbackDiv.innerHTML = errors.messages[`items.${index}.AdjustmentQty`][0];
                }
            }

            if (remarksInput) {
                 const errors = @json($errors->toArray());
                if (errors.messages && errors.messages[`items.${index}.Remarks`]) {
                    remarksInput.classList.add('is-invalid');
                    let feedbackDiv = row.querySelector('.remarks-feedback');
                    if (!feedbackDiv) {
                        feedbackDiv = document.createElement('div');
                        feedbackDiv.classList.add('invalid-feedback', 'remarks-feedback');
                        remarksInput.parentNode.appendChild(feedbackDiv);
                    }
                    feedbackDiv.style.display = 'block';
                    feedbackDiv.innerHTML = errors.messages[`items.${index}.Remarks`][0];
                }
            }
        });
    }

    document.getElementById('branch').addEventListener('change', function () {
        const branchId = this.value;
        if (!branchId) {
            document.querySelector('tbody').innerHTML = ''; // Clear items if no branch selected
            return;
        }

        fetch(`/inventory/branch-stock/${branchId}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('tbody');
                tbody.innerHTML = ''; 

                data.forEach((stock, index) => {
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
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
                            <input type="number" class="form-control adjustment-qty"
                                    name="items[${index}][AdjustmentQty]"
                                    value="" {{-- Start empty for new items from AJAX --}}
                                    placeholder="+/-"
                                    onchange="calculateNewQty(this)">
                            <div class="invalid-feedback adjustment-qty-feedback"></div>
                        </td>
                        <td>
                            <input type="number" class="form-control new-qty" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="items[${index}][Remarks]" placeholder="Optional remarks">
                            <div class="invalid-feedback remarks-feedback"></div>
                        </td>
                    `;
                    tbody.appendChild(newRow);

                    newRow.querySelector('.adjustment-qty').addEventListener('input', function() {
                        calculateNewQty(this);
                    });
                });
                reapplyCalculationsAndErrors();
            })
            .catch(error => console.error('Error fetching items:', error));
    });


    document.addEventListener('DOMContentLoaded', function () {
        $('.select2').select2({
            placeholder: 'Select user',
            allowClear: true
        });

        const oldBranchId = "{{ old('Branch') }}";
        if (oldBranchId && oldBranchId !== document.getElementById('branch').value) {
            document.getElementById('branch').value = oldBranchId;
            document.getElementById('branch').dispatchEvent(new Event('change'));
        } else {
             reapplyCalculationsAndErrors();
        }
    });

</script>
@endsection
