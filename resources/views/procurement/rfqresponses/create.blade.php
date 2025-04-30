@extends('layouts.app')
@section('title', 'Create RFQ Response')
@section('content')
<div class="container">
    <h3>Create RFQ Response</h3>

    <div class="container mt-3">
        <h3>RFQ Response Details</h3>
        <form method="POST" action="{{ route('rfqresponses.store') }}">
            @csrf

            <div class="card mb-3">
                <div class="card-body">
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
                            <div class="form-group mb-3">
                                <label>Supplier Name</label>
                                <select name="SupplierId" class="form-control" required>
                                    <option value="">-- Select Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->Id }}">{{ $supplier->SupplierName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <h5>Requisition Items Details:</h5>
                    <div id="requisition-items-container">
                        <!-- Requisition items table will be dynamically loaded here -->
                    </div>
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

        rfqCodeSelect.addEventListener('change', function () {
            const rfqId = this.value;
            
            // Clear the requisition items container if no RFQ is selected
            if (!rfqId) {
                requisitionItemsContainer.innerHTML = '';
                return;
            }

            // Fetch requisition items for the selected RFQ
            fetch(`/rfqs/${rfqId}/requisition-items`)
                .then(response => response.json())
                .then(data => {
                    if (data.requisitionItems && data.requisitionItems.length > 0) {
                        let tableHtml = `
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Name</th>
                                        <th>Description</th>
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
                                    <td>
                                        <input type="text" class="form-control" name="RequisitionItems[${index}][name]" value="${item.name}" readonly>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="RequisitionItems[${index}][description]" value="${item.description}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control quantity" name="RequisitionItems[${index}][quantity]" value="${item.quantity}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control quotedprice" name="RequisitionItems[${index}][quotedprice]" data-index="${index}" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="RequisitionItems[${index}][durationdays]" required>
                                    </td>
                                    <td>
                                        <select name="RequisitionItems[${index}][currency]" class="form-control" required>
                                            <option value="">-- Select Currency --</option>
                                            @foreach($currencies as $currency => $name)
                                                <option value="{{ $currency }}">{{ $currency }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control totalpayable" name="RequisitionItems[${index}][totalpayable]" readonly>
                                    </td>
                                </tr>
                            `;
                        });

                        tableHtml += `
                                </tbody>
                            </table>
                        `;

                        requisitionItemsContainer.innerHTML = tableHtml;

                        // Add event listeners for quoted price inputs
                        document.querySelectorAll('.quotedprice').forEach(function (input) {
                            input.addEventListener('input', function () {
                                const index = this.getAttribute('data-index');
                                const quantity = document.querySelector(`input[name="RequisitionItems[${index}][quantity]"]`).value;
                                const totalPayableInput = document.querySelector(`input[name="RequisitionItems[${index}][totalpayable]"]`);

                                // Calculate total payable
                                const quotedPrice = parseFloat(this.value) || 0;
                                const totalPayable = quotedPrice * parseFloat(quantity);

                                // Update total payable input
                                totalPayableInput.value = totalPayable.toFixed(2);
                            });
                        });
                    } else {
                        requisitionItemsContainer.innerHTML = '<p>No requisition items found for the selected RFQ.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching requisition items:', error);
                    requisitionItemsContainer.innerHTML = '<p>Failed to load requisition items. Please try again.</p>';
                });
        });
    });
</script>
@endsection