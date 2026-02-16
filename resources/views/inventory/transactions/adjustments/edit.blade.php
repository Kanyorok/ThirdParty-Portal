@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Edit Stock Adjustment')

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
        <h4 class="mb-4">Edit Adjustment - {{ $adjustment->AdjustmentId }}</h4>

        <form method="POST" action="{{ route('transactionsadjustment.update', $adjustment->Id) }}">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="adjustmentDate" class="form-label">Adjustment Date</label>
                    <input type="hidden" id="adjustmentDate" name="AdjustmentDate" value="{{ now()->format('Y-m-d') }}">
                    <input type="text" class="form-control" value="{{ now()->format('m/d/Y') }}" readonly>
                    <small class="text-muted">Current date (non-editable)</small>
                    @error('AdjustmentDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="branch" class="form-label">Branch</label>
                    @php
                        $currentUserBranchId = auth()->user()->branch->Id ?? null;
                    @endphp
                    
                    @if($currentUserBranchId)
                        <input type="hidden" id="branch" name="Branch" value="{{ $currentUserBranchId }}">
                        <input type="text" class="form-control" value="{{ auth()->user()->branch->Name ?? 'N/A' }}" readonly>
                        <small class="text-muted">Your branch (non-editable)</small>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Unable to determine your branch. Please contact administrator.
                        </div>
                        <select id="branch" name="Branch" class="form-select @error('Branch') is-invalid @enderror" required disabled>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->Id }}"
                                    {{ (old('Branch', $adjustment->Branch) == $branch->Id) ? 'selected' : '' }}>
                                    {{ $branch->Name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    
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
                        <th>Item Code<span class="text-danger">*</span></th>
                        <th>Item Name<span class="text-danger">*</span></th>
                        <th>UOM<span class="text-danger">*</span></th>
                        <th>Unit Cost</th>
                        <th>Current Qty<span class="text-danger">*</span></th>
                        <th>Adjustment Qty<span class="text-danger">*</span></th>
                        <th>New Qty</th>
                        <th>Adjustment Reason<span class="text-danger">*</span></th>
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
                <input type="hidden" name="AdjustedBy" value="{{ auth()->user()->Id }}">
                <input type="text" class="form-control" value="{{ auth()->user()->Name }}" readonly>
                @error('AdjustedBy')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('transactionsadjustment.index') }}" class="btn btn-outline-secondary">Back</a>
                <button type="submit" class="btn btn-success"
                        onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">Update Adjustment
                </button>
            </div>
        </form>
    </div>

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

        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
        });

        $(document).ready(function () {
            document.querySelectorAll('.adjustment-qty').forEach(input => calculateNewQty(input));
        });
    </script>
@endsection