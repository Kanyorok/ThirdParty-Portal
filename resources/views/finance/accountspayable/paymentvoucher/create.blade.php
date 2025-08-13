@extends('layouts.app')
@section('title', 'Create Payment Voucher')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">🧾 Create Payment Voucher</h4>
    <form>
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Voucher Number</label>
                <input type="text" class="form-control" value="VCH-2025-0003" readonly>
            </div>
            <div class="col-md-4">
                <label>Supplier</label>
                <select class="form-control">
                    <option>ABC Suppliers Ltd</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Invoice Ref</label>
                <select class="form-control">
                    <option>INV-2025-0150</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Total Amount</label>
                <input type="number" class="form-control" value="150000.00">
            </div>
            <div class="col-md-3">
                <label>Currency</label>
                <input type="text" class="form-control" value="KES">
            </div>
            <div class="col-md-3">
                <label>Payment Method</label>
                <select class="form-control">
                    <option>Bank Transfer</option>
                    <option>Cheque</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Payment Type</label>
                <select class="form-control" id="paymentType" onchange="toggleSchedule()">
                    <option value="Full">Full</option>
                    <option value="Partial">Partial</option>
                    <option value="Scheduled">Scheduled</option>
                </select>
            </div>
        </div>

        <div id="scheduleOptions" style="display: none;">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Start Date</label>
                    <input type="date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Frequency</label>
                    <select class="form-control">
                        <option value="Monthly">Monthly</option>
                        <option value="Biweekly">Biweekly</option>
                        <option value="Weekly">Weekly</option>
                        <option value="Custom">Custom</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Amount per Installment</label>
                    <input type="number" class="form-control" value="50000.00">
                </div>
            </div>
        </div>

        <div class="text-end">
            <button class="btn btn-primary">Save Voucher</button>
        </div>
    </form>
</div>

<script>
    function toggleSchedule() {
        const type = document.getElementById('paymentType').value;
        document.getElementById('scheduleOptions').style.display = (type === 'Scheduled') ? 'block' : 'none';
    }
</script>
@endsection
