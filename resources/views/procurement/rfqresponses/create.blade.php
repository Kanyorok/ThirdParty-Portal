@extends('layouts.app')

@section('title', 'Create RFQ Response')

@section('content')
<div class="container">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container mt-3">
        <form method="POST" action="{{ route('rfqresponses.store') }}">
            @csrf
            <div class="card mb-3">
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label>RFQ Code</label>
                        <select name="RFQId" id="rfq-code" class="form-control" required>
                            <option value="">-- Select RFQ --</option>
                            @foreach($rfqs as $rfq)
                                @php
                                    $hasResponse = false;
                                    try {
                                        // If relation is preloaded as collection/array
                                        if (isset($rfq->rfqResponses) && (is_array($rfq->rfqResponses) || (is_object($rfq->rfqResponses) && method_exists($rfq->rfqResponses, 'count')))) {
                                            $hasResponse = (is_array($rfq->rfqResponses) ? count($rfq->rfqResponses) : $rfq->rfqResponses->count()) > 0;
                                        } elseif (is_object($rfq) && method_exists($rfq, 'rfqResponses')) {
                                            $hasResponse = $rfq->rfqResponses()->exists();
                                        } else {
                                            // Fallback to direct DB check when $rfq is a plain object
                                            $hasResponse = \Illuminate\Support\Facades\DB::table('t_RFQResponse')->where('RFQId', $rfq->Id)->exists();
                                        }
                                    } catch (\Throwable $e) {
                                        $hasResponse = false;
                                    }
                                @endphp
                                @if (!$hasResponse)
                                    <option value="{{ $rfq->Id }}">{{ $rfq->RFQNumber }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="RFQNumber" id="rfq-number" value="">
                    <div class="form-group mb-3">
                        <label>Supplier Name</label>
                        <select name="SupplierName" id="supplier-select" class="form-control" required>
                            <option value="">-- Select Supplier --</option>
                        </select>
                    </div>
                    <input type="hidden" name="SupplierId" id="supplier-id" value=""/>
                    <div id="existing-response-alert" class="alert alert-info d-none">Existing response found. Fields are locked.</div>
                    <h5>Requisition Items Details:</h5>
                    <div id="requisition-items-container"></div>

                    <button class="btn btn-success" type="submit">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rfqCodeSelect = document.getElementById('rfq-code');
    const requisitionItemsContainer = document.getElementById('requisition-items-container');
    const rfqNumberInput = document.getElementById('rfq-number');
    const supplierSelect = document.getElementById('supplier-select');
    const supplierIdInput = document.getElementById('supplier-id'); // Hidden input for SupplierId

    function calculateAggregateTotal() {
        let aggregateTotal = 0;

        document.querySelectorAll('.quotedprice').forEach(function (input) {
            const index = input.getAttribute('data-index');
            const quantity = document.querySelector(`input[name="RequisitionItems[${index}][quantity]"]`).value;
            const totalPayableInput = document.querySelector(`input[name="RequisitionItems[${index}][totalpayable]"]`);

            const quotedPrice = parseFloat(input.value) || 0;
            const totalPayable = (parseFloat(quantity) || 0) * quotedPrice;

            totalPayableInput.value = totalPayable.toFixed(2);
            aggregateTotal += totalPayable;
        });

        document.getElementById('aggregate-total').value = aggregateTotal.toFixed(2);
    }

    rfqCodeSelect.addEventListener('change', function () {
        const rfqId = this.value;
        const selectedOption = rfqCodeSelect.options[rfqCodeSelect.selectedIndex];
        rfqNumberInput.value = selectedOption.textContent.trim();

        if (!rfqId) {
            requisitionItemsContainer.innerHTML = '';
            supplierSelect.innerHTML = '<option value="">-- Select Supplier --</option>';
            supplierIdInput.value = ''; // Clear the hidden SupplierId field
            return;
        }

        // Load suppliers (excludes those whose ThirdParty already responded)
        fetch(`/procurement/rfq-suppliers/${rfqId}`)
            .then(response => response.json())
            .then(data => {
                supplierSelect.innerHTML = '<option value="">-- Select Supplier --</option>';
                if (Array.isArray(data)) {
                    data.forEach(supplier => {
                        supplierSelect.innerHTML += `
                            <option value="${supplier.Id}" data-id="${supplier.Id}">${supplier.SupplierName}</option>
                        `;
                    });
                } else {
                    supplierSelect.innerHTML = '<option value="">No suppliers available</option>';
                }
            })
            .catch(() => {
                supplierSelect.innerHTML = '<option value="">Failed to load suppliers</option>';
            });

        // Load requisition items
        fetch(`/procurement/rfqs/${rfqId}/requisition-items`)
            .then(response => response.json())
            .then(data => {
                if (data.requisitionItems && data.requisitionItems.length > 0) {
                    let tableHtml = `
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Name</th>
                                    <th>UOM</th>
                                    <th>Quantity</th>
                                    <th>Quoted Price</th>
                                    <th>Duration Days</th>
                                    <th>Currency</th>
                                    <th>Total Payable</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    data.requisitionItems.forEach((item, index) => {
                        tableHtml += `
                            <tr>
                                <td>${index + 1}</td>
                                <td><input type="text" name="RequisitionItems[${index}][name]" value="${item.ItemName}" class="form-control" readonly></td>
                                <td>
                                    <input type="hidden" name="RequisitionItems[${index}][uom_id]" value="${item.UOM}">
                                    <input type="text" class="form-control" value="${item.UOMName}" readonly>
                                </td>
                                <td><input type="number" name="RequisitionItems[${index}][quantity]" value="${item.Quantity}" class="form-control" readonly></td>
                                <td><input type="number" name="RequisitionItems[${index}][quotedprice]" data-index="${index}" class="form-control quotedprice" required></td>
                                <td><input type="number" name="DurationDays" class="form-control" required></td>
                                <td>
                                    <select name="Currency" class="form-control" required>
                                        <option value="">-- Select Currency --</option>
                                        @foreach($currencies as $curr)
                                            <option value="{{ $curr->Code }}" {{ $curr->Symbol === 'Ksh' ? 'selected' : '' }}>
                                                {{ $curr->Symbol }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="RequisitionItems[${index}][totalpayable]" class="form-control totalpayable" readonly></td>
                            </tr>
                        `;
                    });

                    tableHtml += `
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-center"><strong>Total</strong></td>
                                    <td colspan="2"><input type="number" id="aggregate-total" name="TotalPayable" class="form-control" readonly></td>
                                </tr>
                            </tfoot>
                        </table>
                    `;

                    requisitionItemsContainer.innerHTML = tableHtml;

                    document.querySelectorAll('.quotedprice').forEach(input => {
                        input.addEventListener('input', calculateAggregateTotal);
                    });

                } else {
                    requisitionItemsContainer.innerHTML = '<p>No requisition items found for this RFQ.</p>';
                }
            })
            .catch(() => {
                requisitionItemsContainer.innerHTML = '<p>Error loading requisition items.</p>';
            });
    });

    // Update hidden SupplierId field when a supplier is selected
    supplierSelect.addEventListener('change', async function () {
        const selectedOption = supplierSelect.options[supplierSelect.selectedIndex];
        supplierIdInput.value = selectedOption.value || '';

        // If both RFQ and Supplier are selected, check for existing response
        const rfqId = rfqCodeSelect.value;
        const supplierId = supplierIdInput.value;
        if (rfqId && supplierId) {
            try {
                const res = await fetch(`/procurement/rfqresponses/find-existing?rfqId=${encodeURIComponent(rfqId)}&supplierId=${encodeURIComponent(supplierId)}`);
                const data = await res.json();
                const alertBox = document.getElementById('existing-response-alert');
                if (data && data.exists) {
                    alertBox.classList.remove('d-none');
                    // Fill header fields if present
                    if (data.header) {
                        // DurationDays: same input name appears per row; set first occurrence
                        const durationInput = document.querySelector('input[name="DurationDays"]');
                        if (durationInput && data.header.durationDays) durationInput.value = data.header.durationDays;

                        // Currency: set selected option by code
                        const currencySelect = document.querySelector('select[name="Currency"]');
                        if (currencySelect && data.header.currency) currencySelect.value = data.header.currency;
                    }
                    // Fill items by matching names and quantities where possible
                    if (Array.isArray(data.items)) {
                        data.items.forEach((it, idx) => {
                            const priceInput = document.querySelector(`input[name="RequisitionItems[${idx}][quotedprice]"]`);
                            const totInput = document.querySelector(`input[name="RequisitionItems[${idx}][totalpayable]"]`);
                            if (priceInput && typeof it.quotedPrice !== 'undefined') priceInput.value = it.quotedPrice;
                            if (totInput && typeof it.totalPayable !== 'undefined') totInput.value = Number(it.totalPayable).toFixed(2);
                        });
                    }
                    // Disable all inputs and save button (except RFQ & Supplier selects)
                    document.querySelectorAll('input, select, button[type="submit"]').forEach(el => {
                        if (el === rfqCodeSelect || el === supplierSelect) return;
                        el.setAttribute('disabled', 'disabled');
                    });
                } else {
                    alertBox.classList.add('d-none');
                    // Re-enable fields if previously disabled
                    document.querySelectorAll('input, select, button[type="submit"]').forEach(el => {
                        if (el === rfqCodeSelect || el === supplierSelect) return;
                        el.removeAttribute('disabled');
                    });
                }
            } catch (e) {
                // On error, do nothing special
            }
        }
    });
});
</script>
@endsection
