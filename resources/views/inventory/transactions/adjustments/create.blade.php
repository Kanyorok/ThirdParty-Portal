@extends('layouts.app')
@section('title', 'Stock Adjustment')

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
    <h4 class="mb-4">Stock Adjustment Form</h4>

    <form method="POST" action="{{ route('transactionsadjustment.store') }}">
        @csrf
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="adjustmentDate" class="form-label">Adjustment Date</label>
                {{-- Use old() helper to repopulate on validation failure --}}
                <input type="date" class="form-control @error('AdjustmentDate') is-invalid @enderror" id="adjustmentDate" name="AdjustmentDate" value="{{ old('AdjustmentDate') }}" required>
                @error('AdjustmentDate')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="branch" class="form-label">Branch</label>
                {{-- Use old() helper and is-invalid class --}}
                <select class="form-select @error('Branch') is-invalid @enderror" id="branch" name="Branch" required>
                    <option selected disabled>Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->Id }}" {{ old('Branch') == $branch->Id ? 'selected' : '' }}>
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
                {{-- Use old() helper and is-invalid class --}}
                <select class="form-select @error('Reason') is-invalid @enderror" id="reason" name="Reason" required>
                    <option value="Damage" {{ old('Reason') == 'Damage' ? 'selected' : '' }}>Damage</option>
                    <option value="Expired" {{ old('Reason') == 'Expired' ? 'selected' : '' }}>Expired</option>
                    <option value="Shrinkage" {{ old('Reason') == 'Shrinkage' ? 'selected' : '' }}>Shrinkage</option>
                    <option value="Stock Found" {{ old('Reason') == 'Stock Found' ? 'selected' : '' }}>Stock Found</option>
                    <option value="Other" {{ old('Reason') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('Reason')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
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
                        <th>Current Qty</th>
                        <th>Adjustment Qty</th>
                        <th>New Qty</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @if(old('items'))
                        {{-- If validation fails, repopulate items from old input --}}
                        @foreach(old('items') as $index => $itemData)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{-- You'll need to fetch ItemCode and ItemName based on $itemData['Item'] if you want to display them --}}
                                    {{-- For simplicity, let's assume you'd fetch it client-side or pass it from controller --}}
                                    <input type="text" class="form-control" value="{{ $itemData['Item'] }}" readonly> {{-- Displays Item ID for now --}}
                                    <input type="hidden" name="items[{{ $index }}][Item]" value="{{ $itemData['Item'] }}">
                                </td>
                                <td><input type="text" class="form-control" value="" readonly></td> {{-- Item Name not available in old() directly, might need re-fetch or pass from backend --}}
                                <td><input type="number" class="form-control current-qty" value="0" readonly></td> {{-- Current Qty not available in old() directly, need to re-fetch via JS --}}
                                <td>
                                    <input type="number" class="form-control adjustment-qty @error('items.' . $index . '.AdjustmentQty') is-invalid @enderror"
                                            name="items[{{ $index }}][AdjustmentQty]"
                                            value="{{ old('items.' . $index . '.AdjustmentQty') }}"
                                            placeholder="+/-"
                                            onchange="calculateNewQty(this)">
                                    @error('items.' . $index . '.AdjustmentQty')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </td>
                                <td>
                                    <input type="number" class="form-control new-qty" value="" readonly>
                                </td>
                                <td>
                                    <input type="text" class="form-control @error('items.' . $index . '.Remarks') is-invalid @enderror"
                                            name="items[{{ $index }}][Remarks]"
                                            value="{{ old('items.' . $index . '.Remarks') }}"
                                            placeholder="Optional remarks">
                                    @error('items.' . $index . '.Remarks')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    {{-- Initial empty state or populated by AJAX on branch change --}}
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <label class="form-label">Adjusted By</label>
            {{-- Use old() helper and is-invalid class --}}
            <select name="AdjustedBy" class="form-select select2 @error('AdjustedBy') is-invalid @enderror" required>
                <option value="">-- Select User --</option>
                @foreach ($users as $user)
                    <option value="{{ $user->Id }}" {{ old('AdjustedBy') == $user->Id ? 'selected' : '' }}>
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

        <button type="submit" class="btn btn-primary">✅ Submit Adjustment</button>
    </form>
</div>

{{-- This script assumes jQuery for select2; ensure it's loaded --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
    document.getElementById('branch').addEventListener('change', function () {
        const branchId = this.value;
        if (!branchId) {
            document.querySelector('tbody').innerHTML = ''; 
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
                                    value="" {{-- Start empty for new form, will be populated by old() on error --}}
                                    placeholder="+/-"
                                    onchange="calculateNewQty(this)">
                            {{-- Placeholder for server-side validation error --}}
                            <div class="invalid-feedback adjustment-qty-feedback"></div>
                        </td>
                        <td>
                            <input type="number" class="form-control new-qty" readonly>
                        </td>

                        <td>
                            <input type="text" class="form-control" name="items[${index}][Remarks]" placeholder="Optional remarks">
                            {{-- Placeholder for server-side validation error --}}
                            <div class="invalid-feedback remarks-feedback"></div>
                        </td>
                    `;
                    tbody.appendChild(newRow);

                    newRow.querySelector('.adjustment-qty').addEventListener('input', function() {
                        calculateNewQty(this);
                    });

                    const oldAdjustmentQty = @json(old('items')) ? @json(old('items'))[index]?.AdjustmentQty : null;
                    if (oldAdjustmentQty !== null) {
                         newRow.querySelector('.adjustment-qty').value = oldAdjustmentQty;
                         calculateNewQty(newRow.querySelector('.adjustment-qty'));
                    }


                    const errors = @json($errors->toArray());
                    if (errors.messages && errors.messages[`items.${index}.AdjustmentQty`]) {
                        newRow.querySelector('.adjustment-qty').classList.add('is-invalid');
                        newRow.querySelector('.adjustment-qty-feedback').innerHTML = errors.messages[`items.${index}.AdjustmentQty`][0];
                    }
                    if (errors.messages && errors.messages[`items.${index}.Remarks`]) {
                        newRow.querySelector('[name="items[' + index + '][Remarks]"]').classList.add('is-invalid');
                        newRow.querySelector('.remarks-feedback').innerHTML = errors.messages[`items.${index}.Remarks`][0];
                    }
                });
            })
            .catch(error => console.error('Error fetching items:', error));
    });

    function calculateNewQty(input) {
        const row = input.closest('tr');
        const currentQtyInput = row.querySelector('.current-qty');
        const currentQty = parseFloat(currentQtyInput.value) || 0;
        const adjustmentQty = parseFloat(input.value) || 0;
        const newQty = currentQty + adjustmentQty;
        row.querySelector('.new-qty').value = newQty;

        if (newQty < 0) {
            input.classList.add('is-invalid');
            let feedbackDiv = row.querySelector('.adjustment-qty-feedback');
            if (!feedbackDiv) {
                feedbackDiv = document.createElement('div');
                feedbackDiv.classList.add('invalid-feedback', 'adjustment-qty-feedback');
                input.parentNode.appendChild(feedbackDiv);
            }
            feedbackDiv.innerHTML = `New quantity (${newQty}) cannot be negative.`;
        } else {
            input.classList.remove('is-invalid');
            let feedbackDiv = row.querySelector('.adjustment-qty-feedback');
            if (feedbackDiv) {
                feedbackDiv.innerHTML = ''; 
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        $('.select2').select2({
            placeholder: 'Select user',
            allowClear: true
        });

        const oldBranchId = "{{ old('Branch') }}";
        if (oldBranchId) {
            document.getElementById('branch').value = oldBranchId;
            document.getElementById('branch').dispatchEvent(new Event('change'));
        }
    });

    // Function to re-apply calculateNewQty to all items after AJAX load completes or on page load
    function reapplyCalculationsAndErrors() {
        document.querySelectorAll('.adjustment-qty').forEach(input => {
            calculateNewQty(input); 
        });


        const errors = @json($errors->toArray());
        if (errors.messages) {
            document.querySelectorAll('tbody tr').forEach((row, index) => {
                const adjustmentQtyInput = row.querySelector('.adjustment-qty');
                const remarksInput = row.querySelector('[name="items[' + index + '][Remarks]"]');

                if (adjustmentQtyInput && errors.messages[`items.${index}.AdjustmentQty`]) {
                    adjustmentQtyInput.classList.add('is-invalid');
                    let feedbackDiv = row.querySelector('.adjustment-qty-feedback');
                    if (!feedbackDiv) {
                        feedbackDiv = document.createElement('div');
                        feedbackDiv.classList.add('invalid-feedback', 'adjustment-qty-feedback');
                        adjustmentQtyInput.parentNode.appendChild(feedbackDiv);
                    }
                    feedbackDiv.innerHTML = errors.messages[`items.${index}.AdjustmentQty`][0];
                }

                if (remarksInput && errors.messages[`items.${index}.Remarks`]) {
                    remarksInput.classList.add('is-invalid');
                    let feedbackDiv = row.querySelector('.remarks-feedback');
                    if (!feedbackDiv) {
                        feedbackDiv = document.createElement('div');
                        feedbackDiv.classList.add('invalid-feedback', 'remarks-feedback');
                        remarksInput.parentNode.appendChild(feedbackDiv);
                    }
                    feedbackDiv.innerHTML = errors.messages[`items.${index}.Remarks`][0];
                }
            });
        }
    }

    // Call reapplyCalculationsAndErrors after the DOM is fully loaded,
    // and potentially after AJAX content is rendered if a branch was pre-selected due to old input.
    window.addEventListener('load', reapplyCalculationsAndErrors); 

    // Also call it after the AJAX content is loaded for the branch change
    document.getElementById('branch').addEventListener('change', function () {
        fetch(`/inventory/branch-stock/${this.value}`)
            .then(response => response.json())
            .then(data => {
                reapplyCalculationsAndErrors(); 
            });
    });

</script>
@endsection
