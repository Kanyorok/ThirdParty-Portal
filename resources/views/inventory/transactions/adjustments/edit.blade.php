@php use Carbon\Carbon; @endphp
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
        <h4 class="mb-4">Edit Adjustment - {{ $adjustment->AdjustmentId }}</h4>

        <form method="POST" action="{{ route('transactionsadjustment.update', $adjustment->Id) }}">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="adjustmentDate" class="form-label">Adjustment Date</label>
                    <input type="date" class="form-control @error('AdjustmentDate') is-invalid @enderror"
                           id="adjustmentDate" name="AdjustmentDate"
                           value="{{ old('AdjustmentDate', $adjustment->AdjustmentDate ? \Carbon\Carbon::parse($adjustment->AdjustmentDate)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                           required>
                    @error('AdjustmentDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="branch" class="form-label">Branch</label>
                    <select id="branch" name="Branch" class="form-select @error('Branch') is-invalid @enderror" required>
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->Id }}"
                                {{ (old('Branch', $adjustment->Branch) == $branch->Id) ? 'selected' : '' }}>
                                {{ $branch->Name }}
                            </option>
                        @endforeach
                    </select>
                    @error('Branch')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                    @php
                        $itemsToDisplay = old('items', $adjustment->items->map(function($item) {
                            return [
                                'Item' => $item->Item,
                                'ItemCode' => $item->item?->ItemCode ?? '',
                                'ItemName' => $item->item?->ItemName ?? '',
                                'UOM' => $item->UOM ?? '',
                                'UnitCost' => $item->UnitCost ?? 0,
                                'CurrentQty' => $item->current_stock_qty ?? 0,
                                'AdjustmentQty' => $item->AdjustmentQty,
                                'Reason' => $item->Reason,
                                'Remarks' => $item->Remarks,
                            ];
                        })->toArray());
                    @endphp

                    @foreach ($itemsToDisplay as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <input type="text" class="form-control" value="{{ $item['ItemCode'] ?? '' }}" readonly>
                                <input type="hidden" name="items[{{ $index }}][Item]" value="{{ $item['Item'] ?? '' }}">
                                <input type="hidden" name="items[{{ $index }}][ItemCode]" value="{{ $item['ItemCode'] ?? '' }}">
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{ $item['ItemName'] ?? '' }}" readonly>
                                <input type="hidden" name="items[{{ $index }}][ItemName]" value="{{ $item['ItemName'] ?? '' }}">
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{ $item['UOM'] ?? '' }}" readonly>
                                <input type="hidden" name="items[{{ $index }}][UOM]" value="{{ $item['UOM'] ?? '' }}">
                            </td>
                            <td>
                                <input type="number" class="form-control" value="{{ $item['UnitCost'] ?? 0 }}" readonly>
                                <input type="hidden" name="items[{{ $index }}][UnitCost]" value="{{ $item['UnitCost'] ?? 0 }}">
                            </td>
                            <td>
                                <input type="number" class="form-control current-qty" value="{{ $item['CurrentQty'] ?? 0 }}" readonly>
                                <input type="hidden" name="items[{{ $index }}][CurrentQty]" value="{{ $item['CurrentQty'] ?? 0 }}">
                            </td>
                            <td>
                                <input type="number" step="any" name="items[{{ $index }}][AdjustmentQty]"
                                       class="form-control adjustment-qty @error('items.' . $index . '.AdjustmentQty') is-invalid @enderror"
                                       value="{{ $item['AdjustmentQty'] ?? '' }}" onchange="calculateNewQty(this)">
                                <div class="invalid-feedback adjustment-qty-feedback">
                                    @error('items.' . $index . '.AdjustmentQty') {{ $message }} @enderror
                                </div>
                            </td>
                            <td>
                                <input type="number" class="form-control new-qty" readonly>
                            </td>
                            <td>
                                <select name="items[{{ $index }}][Reason]" class="form-select @error('items.' . $index . '.Reason') is-invalid @enderror" required>
                                    <option value="">Select Reason</option>
                                    @foreach($reasons as $reason)
                                        <option value="{{ $reason->ID }}" {{ (old("items.$index.Reason", $item['Reason'] ?? '') == $reason->ID) ? 'selected' : '' }}>
                                            {{ $reason->Description }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][Remarks]"
                                       class="form-control @error('items.' . $index . '.Remarks') is-invalid @enderror"
                                       value="{{ old('items.' . $index . '.Remarks', $item['Remarks'] ?? '') }}" placeholder="Optional remarks">
                                @error('items.' . $index . '.Remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-row">Remove</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <label class="form-label">Adjusted By</label>
                <select name="AdjustedBy" class="form-select select2 @error('AdjustedBy') is-invalid @enderror" required>
                    <option value="">-- Select User --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->Id }}" {{ (old('AdjustedBy', $adjustment->AdjustedBy) == $user->Id) ? 'selected' : '' }}>
                            {{ $user->Name }}
                        </option>
                    @endforeach
                </select>
                @error('AdjustedBy')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('transactionsadjustment.index') }}" class="btn btn-outline-secondary">Back</a>
                <button type="submit" class="btn btn-success"
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Update Adjustment
                </button>
            </div>
        </form>
    </div>

    {{-- Scripts: mirror create --}}
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

        // handle dynamic branch reload (mirror create behavior)
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

            // trigger branch change to reload items when branch changed or on initial load if needed
            const selectedBranch = "{{ old('Branch', $adjustment->Branch) }}";
            if (selectedBranch) {
                $('#branch').val(selectedBranch).trigger('change');
            }

            // run initial calculations for existing rows
            document.querySelectorAll('.adjustment-qty').forEach(input => calculateNewQty(input));
        });
    </script>
@endsection
