@php use Carbon\Carbon; @endphp
<!-- Only the inner content for modal popup -->
<style>
    @media print {
        body * {
            visibility: hidden !important;
        }
        .modal, .modal * {
            visibility: visible !important;
        }
        .modal {
            position: absolute !important;
            left: 0;
            top: 0;
            width: auto !important;
            height: auto !important;
            margin: 0 auto !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            z-index: 9999 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .modal-dialog {
            width: 100vw !important;
            max-width: 100vw !important;
            margin: 0 auto !important;
            padding: 0 !important;
            box-shadow: 0 0 8px #ccc !important;
            background: #fff !important;
        }
        .modal-content, .container-fluid {
            background: #fff !important;
            box-shadow: 0 0 8px #ccc !important;
            border-radius: 8px !important;
            padding: 24px !important;
            margin: 0 !important;
            width: 100vw !important;
            max-width: 100vw !important;
        }
        .table-responsive, table {
            width: 100% !important;
            max-width: 100vw !important;
            overflow: visible !important;
        }
        .modal-header, .modal-footer, #printOrderModal {
            display: none !important;
        }
        /* Hide page nav, overlays, etc. */
        nav, footer, .navbar, .sidebar, .alert, .pagination, .modal-backdrop {
            display: none !important;
        }
    }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-info" id="printOrderModal"><i class="fa fa-print"></i> Print</button>
    </div>
    <form>
        
        <!-- Supplier & Details -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label>Supplier</label>
                <select class="form-control supplier" id="supplier" name="supplier" disabled>
                    <option selected>{{ $orderInfo->SupplierName ?? 'N/A' }}</option>
                </select>
            </div>
        </div>
        <!-- LPO Details -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label>LPO Number</label>
                <input type="text" name="LPONo" class="form-control" value="{{ $orderInfo->OrderNo ?? 'N/A' }}" readonly/>
            </div>
            <div class="col-md-4">
                <label>Date</label>
                <input type="date" class="form-control poDate" name="pODate" value="{{ isset($orderInfo->OrderDate) ? Carbon::parse($orderInfo->OrderDate)->format('Y-m-d') : '' }}" readonly/>
            </div>
            <div class="col-md-4">
                <label>Reference Number</label>
                <input type="text" class="form-control refNo" name="refNo" placeholder="RFQ Number" value="{{ $orderInfo->ExtOrdNum ?? 'N/A' }}" readonly/>
            </div>
            <div class="col-md-4 mt-2">
                <label>Priority</label>
                <select class="form-control priority" name="priority" disabled>
                    <option selected>{{ $orderInfo->Priority ?? 'N/A' }}</option>
                </select>
            </div>
            <div class="col-md-4 mt-2">
            <label>Payment Terms</label>
            <input type="text" class="form-control" value="{{ $orderInfo->terms_description ?? 'N/A' }}" readonly/>
            @if(!$orderInfo->terms_description && $orderInfo->terms_id)
                <small class="text-danger">Warning: Payment term ID {{ $orderInfo->terms_id }} not found in t_CodeDetails.</small>
            @endif
        </div>
        <!-- Line Items Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                <tr>
                    <th style="width: 1%; min-width: 10px;">#</th>
                    <th style="width: 10%; min-width: 100px;">Item Type</th>
                    <th style="width: 20%; min-width: 150px;">Item Name</th>
                    <th style="width: 20%; min-width: 200px;">Item Description</th>
                    <th style="width: 5%; min-width: 80px;">Quantity</th>
                    <th style="width: 15%; min-width: 100px;">Unit Price</th>
                    <th style="width: 9%; min-width: 80px;">Tax</th>
                    <th style="width: 5%; min-width: 80px;">Discount</th>
                    <th style="width: 14%; min-width: 150px;">Line Total</th>
                </tr>
                </thead>
                <tbody id="po-items">
                @foreach($lineInfo as $line)
                    <tr>
                        <td class="line-no">1.</td>
                        <td class="text-start">
                            <select class="form-select form-select-sm type" name="type[]" id="Type" disabled>
                                <option selected>{{ $line->ItemType }}</option>
                            </select>
                        </td>
                        <td class="text-start">
                            <select class="form-select form-select-sm itemCode" name="itemCode[]" id="Item" disabled>
                                <option selected>{{ $line->ItemName }}</option>
                            </select>
                        </td>
                        <td class="text-start">
                            <textarea class="form-control form-control-sm itemDescription" name="itemDescription[]" id="Description" readonly style="display: flex; align-items: center; justify-content: center; text-align: center; padding: 0; resize: none;">{{ $line->Description }}</textarea>
                        </td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm qty quantity" name="quantity[]" id="Quantity" value="{{ $line->fQuantity }}" readonly></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm unit-price " name="unitPrice[]" id="Price" value="{{ $line->fUnitPriceExcl }}" readonly></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm tax" name="tax[]" id="Tax" value="{{ $line->fTaxRate }}" readonly></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm discount" name="discount[]" id="Discount" value="{{ $line->fLineDiscount }}" readonly></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm line-total" name="lineTotal[]" id="lineTotal" step="" value="{{ $line->LineTotal }}" readonly></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!-- Optional Note -->
        <div class="mb-4">
            <label>Line Note</label>
            <textarea class="form-control" rows="3" placeholder="Optional message to supplier" readonly></textarea>
        </div>
        <!-- Totals -->
        <div class="row mb-4">
            <div class="col-md-4 offset-md-8">
                <div class="mb-2">
                    <label>Exclusive Total</label>
                    <input type="text" class="form-control" value="{{ $orderInfo->OrdTotExcl ?? 'N/A' }}" readonly/>
                </div>
                <div class="mb-2">
                    <label>Tax Amount</label>
                    <input type="text" class="form-control" value="{{ $orderInfo->OrdTotTax ?? 'N/A' }}" readonly/>
                </div>
                <div>
                    <label>Inclusive Total</label>
                    <input type="text" class="form-control" value="{{ $orderInfo->OrdTotIncl ?? 'N/A' }}" readonly/>
                </div>
            </div>
        </div>
    </form>
    <script>
        document.getElementById('printOrderModal').onclick = function() {
            window.print();
        };
    </script>
</div>
