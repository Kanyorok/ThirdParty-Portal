@extends('layouts.app')
@section('title', 'Create Contract-Based LPO')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">📄 Create Contract-Based LPO</h4>
                        <p class="text-muted mb-0">Create Local Purchase Order from
                            Contract: {{ $contract->ContractRef }}</p>
                    </div>
                    <div>
                        <a href="{{ route('lpo.origination.contract-based') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Contract Selection
                        </a>
                        <a href="{{ route('contracts.lifecycle.view', $contract->Id) }}" class="btn btn-outline-info">
                            <i class="fas fa-eye"></i> View Contract
                        </a>
                    </div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Contract Information Panel -->
                <div class="card mb-4 border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-file-contract"></i> Contract Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Contract Reference:</strong>
                                <div class="text-success">{{ $contract->ContractRef }}</div>
                            </div>
                            <div class="col-md-3">
                                <strong>Tender:</strong>
                                <div>{{ $contract->tender->TenderNo ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ Str::limit($contract->tender->Title ?? '', 50) }}</div>
                            </div>
                            <div class="col-md-3">
                                <strong>Supplier:</strong>
                                <div>{{ $contract->winningSupplier->SupplierName ?? 'N/A' }}</div>
                                <div
                                    class="small text-muted">{{ $contract->winningSupplier->ContactPerson ?? '' }}</div>
                            </div>
                            <div class="col-md-3">
                                <strong>Contract Value:</strong>
                                <div class="text-success">
                                    {{ number_format($contract->ContractValue ?? 0, 2) }}
                                    {{ is_object($contract->tender->Currency) ? $contract->tender->Currency->Code : ($contract->tender->Currency ?? 'KES') }}
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Contract Period:</strong>
                                <div>
                                    {{ $contract->ContractStartDate?->format('d/m/Y') ?? 'N/A' }} -
                                    {{ $contract->ContractEndDate?->format('d/m/Y') ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                <span class="badge {{ $contract->contract_status_badge['class'] }}">
                                    {{ $contract->contract_status_badge['text'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LPO Creation Form -->
                <form action="{{ route('lpo.store.contract') }}" method="POST" id="contractLPOForm">
                    @csrf
                    <input type="hidden" name="contract_id" value="{{ $contract->Id }}">
                    <input type="hidden" name="origination_type" value="contract">
                    <input type="hidden" name="supplier_id" value="{{ $contract->SupplierId }}">

                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-file-alt"></i> LPO Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Basic LPO Information -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">LPO Number <span class="text-danger">*</span></label>
                                    <input type="text" name="lpo_number"
                                           class="form-control @error('lpo_number') is-invalid @enderror"
                                           value="{{ old('lpo_number', $lpoData['lpo_number']) }}" readonly>
                                    @error('lpo_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">LPO Date <span class="text-danger">*</span></label>
                                    <input type="date" name="lpo_date"
                                           class="form-control @error('lpo_date') is-invalid @enderror"
                                           value="{{ old('lpo_date', now()->format('Y-m-d')) }}" required>
                                    @error('lpo_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select name="priority" class="form-select @error('priority') is-invalid @enderror"
                                            required>
                                        <option value="">Select Priority</option>
                                        <option value="High" {{ old('priority') === 'High' ? 'selected' : '' }}>High
                                        </option>
                                        <option
                                            value="Medium" {{ old('priority', 'Medium') === 'Medium' ? 'selected' : '' }}>
                                            Medium
                                        </option>
                                        <option value="Low" {{ old('priority') === 'Low' ? 'selected' : '' }}>Low
                                        </option>
                                    </select>
                                    @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Expected Delivery <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="expected_delivery_date"
                                           class="form-control @error('expected_delivery_date') is-invalid @enderror"
                                           value="{{ old('expected_delivery_date') }}" required>
                                    @error('expected_delivery_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Contract Terms Pre-filled -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Payment Terms</label>
                                    <textarea name="payment_terms" class="form-control" rows="3"
                                              placeholder="Payment terms as per contract">{{ old('payment_terms', $contract->PaymentTerms ?? '') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Delivery Terms</label>
                                    <textarea name="delivery_terms" class="form-control" rows="3"
                                              placeholder="Delivery terms and location">{{ old('delivery_terms', $contract->DeliveryTerms ?? '') }}</textarea>
                                </div>
                            </div>

                            <!-- LPO Items Section -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6><i class="fas fa-list"></i> LPO Items</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                                        <i class="fas fa-plus"></i> Add Item
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="lpoItemsTable">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%;">#</th>
                                            <th style="width: 25%;">Item Description <span class="text-danger">*</span>
                                            </th>
                                            <th style="width: 10%;">Quantity <span class="text-danger">*</span></th>
                                            <th style="width: 10%;">Unit</th>
                                            <th style="width: 15%;">Unit Price <span class="text-danger">*</span></th>
                                            <th style="width: 10%;">Tax %</th>
                                            <th style="width: 10%;">Discount %</th>
                                            <th style="width: 15%;">Total Amount</th>
                                            <th style="width: 5%;">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="itemsTableBody">
                                        <tr class="item-row">
                                            <td class="row-number">1</td>
                                            <td>
                                                <input type="text" name="items[0][description]"
                                                       class="form-control item-description" required
                                                       placeholder="Enter item description...">
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][quantity]"
                                                       class="form-control item-quantity" min="1" step="any" required>
                                            </td>
                                            <td>
                                                <input type="text" name="items[0][unit]"
                                                       class="form-control item-unit" placeholder="pcs, kg, etc.">
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][unit_price]"
                                                       class="form-control item-unit-price" min="0" step="0.01"
                                                       required>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][tax_percentage]"
                                                       class="form-control item-tax" min="0" max="100" step="0.01">
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][discount_percentage]"
                                                       class="form-control item-discount" min="0" max="100" step="0.01">
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][total_amount]"
                                                       class="form-control item-total" readonly>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger remove-item-btn"
                                                        title="Remove Item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- LPO Summary -->
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <label class="form-label">Additional Notes</label>
                                    <textarea name="notes" class="form-control" rows="3"
                                              placeholder="Any additional instructions or notes for this LPO...">{{ old('notes') }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">LPO Summary</h6>
                                            <div class="d-flex justify-content-between">
                                                <span>Subtotal:</span>
                                                <span id="subtotalAmount">0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Total Tax:</span>
                                                <span id="totalTaxAmount">0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Total Discount:</span>
                                                <span id="totalDiscountAmount">0.00</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <strong>Grand Total:</strong>
                                                <strong id="grandTotalAmount">0.00</strong>
                                            </div>
                                            <input type="hidden" name="total_amount" id="totalAmountHidden">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" name="action" value="save_draft" class="btn btn-outline-primary me-2">
                                <i class="fas fa-save"></i> Save as Draft
                            </button>
                            <button type="submit" name="action" value="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Create LPO
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let itemIndex = 1;

            // Add new item row
            document.getElementById('addItemBtn').addEventListener('click', function () {
                const tbody = document.getElementById('itemsTableBody');
                const newRow = createItemRow(itemIndex);
                tbody.appendChild(newRow);
                itemIndex++;
                updateRowNumbers();
            });

            // Remove item row
            document.addEventListener('click', function (e) {
                if (e.target.closest('.remove-item-btn')) {
                    const row = e.target.closest('tr');
                    if (document.querySelectorAll('.item-row').length > 1) {
                        row.remove();
                        updateRowNumbers();
                        calculateTotals();
                    } else {
                        alert('At least one item is required');
                    }
                }
            });

            // Calculate totals when values change
            document.addEventListener('input', function (e) {
                if (e.target.matches('.item-quantity, .item-unit-price, .item-tax, .item-discount')) {
                    calculateRowTotal(e.target.closest('tr'));
                    calculateTotals();
                }
            });

            function createItemRow(index) {
                const row = document.createElement('tr');
                row.className = 'item-row';
                row.innerHTML = `
                    <td class="row-number">${index + 1}</td>
                    <td>
                        <input type="text" name="items[${index}][description]"
                               class="form-control item-description" required
                               placeholder="Enter item description...">
                    </td>
                    <td>
                        <input type="number" name="items[${index}][quantity]"
                               class="form-control item-quantity" min="1" step="any" required>
                    </td>
                    <td>
                        <input type="text" name="items[${index}][unit]"
                               class="form-control item-unit" placeholder="pcs, kg, etc.">
                    </td>
                    <td>
                        <input type="number" name="items[${index}][unit_price]"
                               class="form-control item-unit-price" min="0" step="0.01" required>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][tax_percentage]"
                               class="form-control item-tax" min="0" max="100" step="0.01">
                    </td>
                    <td>
                        <input type="number" name="items[${index}][discount_percentage]"
                               class="form-control item-discount" min="0" max="100" step="0.01">
                    </td>
                    <td>
                        <input type="number" name="items[${index}][total_amount]"
                               class="form-control item-total" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-item-btn" title="Remove Item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                return row;
            }

            function updateRowNumbers() {
                document.querySelectorAll('.row-number').forEach((cell, index) => {
                    cell.textContent = index + 1;
                });
            }

            function calculateRowTotal(row) {
                const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                const unitPrice = parseFloat(row.querySelector('.item-unit-price').value) || 0;
                const taxRate = parseFloat(row.querySelector('.item-tax').value) || 0;
                const discountRate = parseFloat(row.querySelector('.item-discount').value) || 0;

                let subtotal = quantity * unitPrice;
                let taxAmount = subtotal * (taxRate / 100);
                let discountAmount = subtotal * (discountRate / 100);
                let total = subtotal + taxAmount - discountAmount;

                row.querySelector('.item-total').value = total.toFixed(2);
            }

            function calculateTotals() {
                let subtotal = 0;
                let totalTax = 0;
                let totalDiscount = 0;

                document.querySelectorAll('.item-row').forEach(row => {
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const unitPrice = parseFloat(row.querySelector('.item-unit-price').value) || 0;
                    const taxRate = parseFloat(row.querySelector('.item-tax').value) || 0;
                    const discountRate = parseFloat(row.querySelector('.item-discount').value) || 0;

                    let rowSubtotal = quantity * unitPrice;
                    let rowTax = rowSubtotal * (taxRate / 100);
                    let rowDiscount = rowSubtotal * (discountRate / 100);

                    subtotal += rowSubtotal;
                    totalTax += rowTax;
                    totalDiscount += rowDiscount;
                });

                let grandTotal = subtotal + totalTax - totalDiscount;

                document.getElementById('subtotalAmount').textContent = subtotal.toFixed(2);
                document.getElementById('totalTaxAmount').textContent = totalTax.toFixed(2);
                document.getElementById('totalDiscountAmount').textContent = totalDiscount.toFixed(2);
                document.getElementById('grandTotalAmount').textContent = grandTotal.toFixed(2);
                document.getElementById('totalAmountHidden').value = grandTotal.toFixed(2);
            }

            // Form validation
            document.getElementById('contractLPOForm').addEventListener('submit', function (e) {
                const items = document.querySelectorAll('.item-row');
                if (items.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one item to the LPO');
                    return false;
                }

                // Check if all required fields are filled
                let hasErrors = false;
                items.forEach(item => {
                    const description = item.querySelector('.item-description').value.trim();
                    const quantity = item.querySelector('.item-quantity').value;
                    const unitPrice = item.querySelector('.item-unit-price').value;

                    if (!description || !quantity || !unitPrice) {
                        hasErrors = true;
                    }
                });

                if (hasErrors) {
                    e.preventDefault();
                    alert('Please fill in all required item fields (Description, Quantity, Unit Price)');
                    return false;
                }
            });

            // Initialize calculations on page load
            calculateTotals();
        });
    </script>
@endsection
