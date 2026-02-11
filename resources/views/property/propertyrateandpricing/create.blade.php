@extends('layouts.app')
@section('title', 'Property Rate & Pricing Setup')

@section('content')

<div class="container py-4">

@if ($errors->any())
<div class="alert alert-danger shadow-sm">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('propertyrateandpricing.store') }}" method="POST">
@csrf

<div class="card shadow border-0">
<div class="card-body">

{{-- PROPERTY STRUCTURE --}}
<h6 class="text-muted text-uppercase fw-semibold border-bottom pb-2 mb-3">Property Structure</h6>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">Property *</label>
        <select name="PropertyId" id="property-select" class="form-select" required>
            <option value="">Select Property</option>
            @foreach ($property as $item)
                <option value="{{ $item->Id }}">{{ $item->PropertyName }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Block *</label>
        <select name="BlockId" id="block-select" class="form-select" required></select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Floor *</label>
        <select name="FloorId" id="floor-select" class="form-select" required></select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Unit *</label>
        <select name="UnitId" id="unit-select" class="form-select" required></select>
    </div>
</div>

{{-- MONTHLY CHARGES --}}
<h6 class="text-muted text-uppercase fw-semibold border-bottom pb-2 mb-3">Monthly Charges</h6>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label>Rent</label>
        <input type="number" class="form-control charge" name="Rent" required>
    </div>
    <div class="col-md-3">
        <label>Parking</label>
        <input type="number" class="form-control charge" name="ParkingFee" required>
    </div>
    <div class="col-md-3">
        <label>Service Charge</label>
        <input type="number" class="form-control charge" name="ServiceCharge" required>
    </div>
    <div class="col-md-3">
        <label>Other Charges</label>
        <input type="number" class="form-control charge" name="OtherCharges" required>
    </div>
</div>

{{-- TAX & DEPOSIT --}}
<h6 class="text-muted text-uppercase fw-semibold border-bottom pb-2 mb-3">Tax & Deposit</h6>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label>Deposit</label>
        <input type="number" id="deposit" name="DepositAmount" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label>Currency</label>
        <select class="form-select" name="CurrencyId" required>
            <option value="">Select Currency</option>
            @foreach($currencies as $cur)
                <option value="{{ $cur->Id }}">{{ $cur->Code }} ({{ $cur->Symbol }})</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label>Tax</label>
        <select id="taxRate" class="form-select" required>
            <option value="0">Select Tax</option>
            @foreach($Taxes as $tax)
                <option value="{{ $tax->Rate }}">
                    {{ $tax->taxType->TaxTypeName }} ({{ $tax->Rate }}%)
                </option>
            @endforeach
        </select>
        <input type="hidden" name="TaxId" id="TaxIdHidden">
    </div>
</div>

{{-- TOTAL SUMMARY --}}
<div class="card bg-light border-success">
<div class="card-body">

<div class="row">

<div class="col-md-6">
    <small class="text-muted">MONTHLY (INCL TAX)</small>
    <h3 class="fw-bold text-success" id="monthlyTotal">0.00</h3>
</div>

<div class="col-md-6 text-md-end">
    <p>Subtotal: <strong id="subtotal">0.00</strong></p>
    <p>Tax: <strong id="taxAmount">0.00</strong></p>
    <p>Deposit: <strong id="depositDisplay">0.00</strong></p>
    <hr>
    <h4>Total Payable: <strong id="grandTotal">0.00</strong></h4>
</div>

</div>
</div>
</div>

<div class="mt-4 d-flex justify-content-between">
    <a href="{{ route('propertyrateandpricing.index') }}" class="btn btn-outline-secondary">Back</a>
    <button class="btn btn-success px-4">Save Pricing</button>
</div>

</div>
</div>
</form>
</div>

{{-- CALCULATION SCRIPT --}}
<script>
function calc() {
    let subtotal = 0;
    document.querySelectorAll('.charge').forEach(i => subtotal += Number(i.value || 0));

    let taxRate = Number(document.getElementById('taxRate').value);
    let tax = subtotal * taxRate / 100;
    let deposit = Number(document.getElementById('deposit').value || 0);

    let monthlyWithTax = subtotal + tax;
    let grandTotal = monthlyWithTax + deposit;

    document.getElementById('subtotal').innerText = subtotal.toFixed(2);
    document.getElementById('taxAmount').innerText = tax.toFixed(2);
    document.getElementById('monthlyTotal').innerText = monthlyWithTax.toFixed(2);
    document.getElementById('depositDisplay').innerText = deposit.toFixed(2);
    document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);
}

document.querySelectorAll('.charge, #deposit, #taxRate')
    .forEach(el => el.addEventListener('input', calc));

document.getElementById('taxRate').addEventListener('change', e => {
    document.getElementById('TaxIdHidden').value = e.target.selectedIndex;
    calc();
});
</script>

@endsection
