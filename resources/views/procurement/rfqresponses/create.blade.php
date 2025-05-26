@extends('layouts.app')

@section('title', 'Create RFQ Response')

@section('content')
<div class="container">
    <h3>Create RFQ Response</h3>

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
                                <option value="{{ $rfq->Id }}">{{ $rfq->RFQNumber }}</option>
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

        // Load suppliers
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
                                <td><input type="text" name="RequisitionItems[${index}][uom]" value="${item.UOM || ''}" class="form-control" readonly></td>
                                <td><input type="number" name="RequisitionItems[${index}][quantity]" value="${item.Quantity}" class="form-control" readonly></td>
                                <td><input type="number" name="RequisitionItems[${index}][quotedprice]" data-index="${index}" class="form-control quotedprice" required></td>
                                <td><input type="number" name="DurationDays" class="form-control" required></td>
                                <td>
                                    <select name="Currency" class="form-control" required>
                                        <option value="">-- Select Currency --</option>
                                        @foreach($currencies as $code => $name)
                                            <option value="{{ $code }}">{{ $code }}</option>
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
    supplierSelect.addEventListener('change', function () {
        const selectedOption = supplierSelect.options[supplierSelect.selectedIndex];
        supplierIdInput.value = selectedOption.value || ''; // Set the hidden SupplierId field
    });
});
</script>
@endsection
