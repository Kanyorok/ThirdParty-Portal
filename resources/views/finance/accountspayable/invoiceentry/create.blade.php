@extends('layouts.app')
@section('title', 'Create Payables Invoice')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">🧾 Create Payables Invoice</h4>


        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Invoice Number</label>
                <input type="text" name="InvoiceNumber" class="form-control" placeholder="e.g. INV-1001" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Invoice Date</label>
                <input type="date" name="InvoiceDate" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Vendor</label>
                <select name="VendorID" class="form-control">
                    <option value="">-- Select Vendor --</option>
                    <option value="1">ABC Supplies Ltd</option>
                    <option value="2">Global Traders Inc.</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Currency</label>
                <select name="CurrencyCode" class="form-control">
                    <option value="KES">KES</option>
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Exchange Rate</label>
                <input type="number" step="0.0001" name="ExchangeRate" class="form-control" value="1.0000">
            </div>
            <div class="col-md-4">
                <label class="form-label">PO / GRN Reference</label>
                <select name="ReferenceID" class="form-control">
                    <option value="">-- None (Manual Entry) --</option>
                    <option value="PO-101">PO-101 - ABC Supplies</option>
                    <option value="GRN-204">GRN-204 - Global Traders</option>
                </select>
            </div>
        </div>

        <hr>

        <h5 class="mb-3">📦 Line Items</h5>
        <table class="table table-bordered" id="lineItemsTable">
            <thead class="table-light">
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Cost</th>
                    <th>Tax</th>
                    <th>GL Account</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input name="lines[0][Description]" class="form-control" value="Stationery - Pens"></td>
                    <td><input name="lines[0][Quantity]" type="number" step="0.01" class="form-control" value="10"></td>
                    <td><input name="lines[0][UnitCost]" type="number" step="0.01" class="form-control" value="100"></td>
                    <td>
                        <select name="lines[0][TaxID]" class="form-control">
                            <option value="">-- None --</option>
                            <option value="1">VAT 16%</option>
                            <option value="2">WHT 5%</option>
                        </select>
                    </td>
                    <td>
                        <select name="lines[0][GLAccountID]" class="form-control">
                            <option value="5001">5001 - Office Supplies</option>
                            <option value="5002">5002 - Admin Expenses</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">🗑️</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-secondary" onclick="addRow()">➕ Add Line</button>
        </div>

        <hr>

        <h5 class="mb-3">📤 Upload EDI File (Optional)</h5>
        <div class="mb-3">
            <input type="file" name="edi_file" class="form-control">
            <small class="form-text text-muted">Supports CSV/Excel import. Parse and map lines in controller.</small>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">💾 Save Invoice</button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
let lineIndex = 1;

function addRow() {
    const table = document.querySelector("#lineItemsTable tbody");
    const row = `
        <tr>
            <td><input name="lines[${lineIndex}][Description]" class="form-control"></td>
            <td><input name="lines[${lineIndex}][Quantity]" type="number" step="0.01" class="form-control" value="1"></td>
            <td><input name="lines[${lineIndex}][UnitCost]" type="number" step="0.01" class="form-control"></td>
            <td>
                <select name="lines[${lineIndex}][TaxID]" class="form-control">
                    <option value="">-- None --</option>
                    <option value="1">VAT 16%</option>
                    <option value="2">WHT 5%</option>
                </select>
            </td>
            <td>
                <select name="lines[${lineIndex}][GLAccountID]" class="form-control">
                    <option value="5001">5001 - Office Supplies</option>
                    <option value="5002">5002 - Admin Expenses</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">🗑️</button>
            </td>
        </tr>
    `;
    table.insertAdjacentHTML("beforeend", row);
    lineIndex++;
}

function removeRow(button) {
    button.closest("tr").remove();
}
</script>
@endsection
