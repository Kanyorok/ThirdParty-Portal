@extends('layouts.app')
@section('title', 'Create Invoice - Accounts Receivable')
@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">
                            <i class="fas fa-file-invoice me-2"></i> Create New Invoice
                        </h4>
                    </div>
                    <div class="card-body">

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('invoicegeneration.store') }}" id="invoiceForm">
                            @csrf

                            {{-- Customer Information --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="CustomerID" class="form-label">Customer <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="CustomerID" name="CustomerID" required>
                                        <option value="">-- Select Customer --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->Id }}"
                                                    data-credit-limit="{{ $customer->CreditLimit ?? 0 }}"
                                                    data-has-credit="{{ $customer->CreditLimit ? 'true' : 'false' }}"
                                                {{ old('CustomerID') == $customer->Id ? 'selected' : '' }}>
                                                {{ $customer->ThirdPartyName }}
                                                @if($customer->CreditLimit)
                                                    (Credit: KSh {{ number_format($customer->CreditLimit, 2) }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="InvoiceTitle" class="form-label">Invoice Title <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="InvoiceTitle" name="InvoiceTitle"
                                           value="{{ old('InvoiceTitle') }}" required>
                                </div>
                            </div>

                            {{-- Credit Information Panel --}}
                            <div id="creditPanel" class="alert alert-info" style="display: none;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="alert-heading">Customer Credit Information</h6>
                                        <div id="creditInfo">
                                            <div>Credit Limit: <span id="creditLimit">-</span></div>
                                            <div>Available Credit: <span id="availableCredit">-</span></div>
                                            <div>Current Utilization: <span id="utilization">-</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="use_credit"
                                                   name="use_credit" value="1">
                                            <label class="form-check-label" for="use_credit">
                                                <strong>Use Customer Credit</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted">Check this to apply invoice against customer's credit
                                            limit</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Invoice Details --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="DueDate" class="form-label">Due Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="DueDate" name="DueDate"
                                           value="{{ old('DueDate') }}" min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="Description" class="form-label">Description</label>
                                    <textarea class="form-control" id="Description" name="Description"
                                              rows="2">{{ old('Description') }}</textarea>
                                </div>
                            </div>

                            {{-- Invoice Lines --}}
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Invoice Items</h6>
                                </div>
                                <div class="card-body">
                                    <div id="invoiceLines">
                                        <div class="invoice-line mb-3">
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <label class="form-label">Description <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="lines[0][description]"
                                                           required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Quantity <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" class="form-control quantity"
                                                           name="lines[0][quantity]"
                                                           min="0.01" step="0.01" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Unit Cost <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" class="form-control unit-cost"
                                                           name="lines[0][unit_cost]"
                                                           min="0" step="0.01" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Total</label>
                                                    <input type="number" class="form-control line-total"
                                                           name="lines[0][total]"
                                                           readonly>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button"
                                                            class="btn btn-outline-danger w-100 remove-line" disabled>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-outline-primary" id="addLine">
                                        <i class="fas fa-plus me-1"></i> Add Line Item
                                    </button>
                                </div>
                            </div>

                            {{-- Totals --}}
                            <div class="row justify-content-end mb-4">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col">Subtotal:</div>
                                                <div class="col text-end">KSh <span id="subtotal">0.00</span></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col">Tax:</div>
                                                <div class="col text-end">
                                                    <input type="number" class="form-control form-control-sm"
                                                           id="TaxAmount" name="TaxAmount" value="0" min="0"
                                                           step="0.01">
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col"><strong>Total:</strong></div>
                                                <div class="col text-end"><strong>KSh <span
                                                            id="total">0.00</span></strong></div>
                                            </div>
                                            <input type="hidden" id="TotalAmount" name="TotalAmount" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Credit Check Status --}}
                            <div id="creditCheckStatus" class="alert" style="display: none;"></div>

                            {{-- Form Actions --}}
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('invoicegeneration.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> Create Invoice
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let lineIndex = 1;
            const customerSelect = document.getElementById('CustomerID');
            const creditPanel = document.getElementById('creditPanel');
            const useCreditCheck = document.getElementById('use_credit');
            const creditCheckStatus = document.getElementById('creditCheckStatus');

            // Customer selection change
            customerSelect.addEventListener('change', function () {
                const hasCredit = this.selectedOptions[0]?.dataset.hasCredit === 'true';

                if (hasCredit) {
                    creditPanel.style.display = 'block';
                    updateCreditInfo();
                } else {
                    creditPanel.style.display = 'none';
                    useCreditCheck.checked = false;
                }
            });

            // Add line item
            document.getElementById('addLine').addEventListener('click', function () {
                const lineHtml = `
            <div class="invoice-line mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="lines[${lineIndex}][description]" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control quantity" name="lines[${lineIndex}][quantity]"
                               min="0.01" step="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control unit-cost" name="lines[${lineIndex}][unit_cost]"
                               min="0" step="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control line-total" name="lines[${lineIndex}][total]" readonly>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger w-100 remove-line">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
                document.getElementById('invoiceLines').insertAdjacentHTML('beforeend', lineHtml);
                lineIndex++;
                updateRemoveButtons();
            });

            // Remove line item
            document.addEventListener('click', function (e) {
                if (e.target.closest('.remove-line')) {
                    e.target.closest('.invoice-line').remove();
                    updateRemoveButtons();
                    calculateTotals();
                }
            });

            // Calculate line totals
            document.addEventListener('input', function (e) {
                if (e.target.classList.contains('quantity') || e.target.classList.contains('unit-cost')) {
                    const line = e.target.closest('.invoice-line');
                    const quantity = line.querySelector('.quantity').value || 0;
                    const unitCost = line.querySelector('.unit-cost').value || 0;
                    const total = quantity * unitCost;
                    line.querySelector('.line-total').value = total.toFixed(2);
                    calculateTotals();
                }

                if (e.target.id === 'TaxAmount') {
                    calculateTotals();
                }
            });

            // Credit check on total change
            useCreditCheck.addEventListener('change', function () {
                if (this.checked) {
                    checkCredit();
                } else {
                    creditCheckStatus.style.display = 'none';
                }
            });

            function updateRemoveButtons() {
                const lines = document.querySelectorAll('.invoice-line');
                lines.forEach((line, index) => {
                    const removeBtn = line.querySelector('.remove-line');
                    removeBtn.disabled = lines.length <= 1;
                });
            }

            function calculateTotals() {
                let subtotal = 0;
                document.querySelectorAll('.line-total').forEach(input => {
                    subtotal += parseFloat(input.value || 0);
                });

                const tax = parseFloat(document.getElementById('TaxAmount').value || 0);
                const total = subtotal + tax;

                document.getElementById('subtotal').textContent = subtotal.toFixed(2);
                document.getElementById('total').textContent = total.toFixed(2);
                document.getElementById('TotalAmount').value = total.toFixed(2);

                // Check credit if enabled
                if (useCreditCheck.checked) {
                    checkCredit();
                }
            }

            function updateCreditInfo() {
                const customerId = customerSelect.value;
                if (!customerId) return;

                // You can fetch current credit info via AJAX here if needed
                const option = customerSelect.selectedOptions[0];
                const creditLimit = option.dataset.creditLimit || 0;

                document.getElementById('creditLimit').textContent = 'KSh ' + parseFloat(creditLimit).toLocaleString();
                document.getElementById('availableCredit').textContent = 'Loading...';
                document.getElementById('utilization').textContent = 'Loading...';
            }

            function checkCredit() {
                const customerId = customerSelect.value;
                const amount = document.getElementById('TotalAmount').value;

                if (!customerId || !amount || amount <= 0) return;

                fetch('{{ route("invoicegeneration.check-credit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        customer_id: customerId,
                        amount: parseFloat(amount)
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        const status = document.getElementById('creditCheckStatus');

                        if (data.available) {
                            status.className = 'alert alert-success';
                            status.innerHTML = `<i class="fas fa-check-circle me-1"></i> ${data.message}`;
                            document.getElementById('submitBtn').disabled = false;
                        } else {
                            status.className = 'alert alert-danger';
                            status.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> ${data.message}`;
                            document.getElementById('submitBtn').disabled = true;
                        }

                        status.style.display = 'block';

                        // Update credit info
                        document.getElementById('availableCredit').textContent = 'KSh ' + parseFloat(data.available_amount).toLocaleString();
                        const utilization = data.credit_limit > 0 ? ((data.credit_limit - data.available_amount) / data.credit_limit * 100).toFixed(1) : 0;
                        document.getElementById('utilization').textContent = utilization + '%';
                    })
                    .catch(error => {
                        console.error('Credit check failed:', error);
                    });
            }

            // Initialize
            updateRemoveButtons();
        });
    </script>
@endsection
