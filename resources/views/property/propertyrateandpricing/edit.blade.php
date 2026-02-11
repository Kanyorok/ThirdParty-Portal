@extends('layouts.app')
@section('title', 'Edit Property Rate & Pricing')

@section('content')

<div class="container py-4" style="max-width:1100px;">

@if ($errors->any())
<div class="alert alert-danger shadow-sm">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('propertyrateandpricing.update', $pricing->Id) }}" method="POST">
@csrf
@method('PUT')

<div class="card shadow border-0">
<div class="card-body">

{{-- PROPERTY INFO --}}
<h6 class="text-muted text-uppercase fw-semibold border-bottom pb-2 mb-3">Property Info</h6>

<div class="row g-3 mb-4">
@foreach([
    'Property' => $pricing->property->PropertyName ?? 'N/A',
    'Block' => $pricing->block->BlockName ?? 'N/A',
    'Floor' => $pricing->floor->FloorLabel ?? 'N/A',
    'Unit' => $pricing->unit->UnitCode ?? 'N/A'
] as $label => $value)
<div class="col-md-3">
    <label>{{ $label }}</label>
    <div class="form-control bg-light fw-semibold">{{ $value }}</div>
</div>
@endforeach
</div>

<input type="hidden" name="PropertyId" value="{{ $pricing->PropertyId }}">
<input type="hidden" name="BlockId" value="{{ $pricing->BlockId }}">
<input type="hidden" name="FloorId" value="{{ $pricing->FloorId }}">
<input type="hidden" name="UnitId" value="{{ $pricing->UnitId }}">

{{-- CURRENCY & TAX --}}
<h6 class="text-muted text-uppercase fw-semibold border-bottom pb-2 mb-3">Currency & Tax</h6>

<div class="row g-3 mb-4">

<div class="col-md-6">
<select class="form-select" name="CurrencyId" required>
@foreach ($currencies as $cur)
<option value="{{ $cur->Id }}" {{ $pricing->CurrencyId==$cur->Id?'selected':'' }}>
    {{ $cur->Code }} ({{ $cur->Symbol }})
</option>
@endforeach
</select>
</div>

<div class="col-md-6">
<select id="taxRate" class="form-select" required>
@foreach ($Taxes as $tax)
<option value="{{ $tax->Rate }}" {{ $pricing->TaxId==$tax->Id?'selected':'' }}>
    {{ $tax->taxType->TaxTypeName }} ({{ $tax->Rate }}%)
</option>
@endforeach
</select>
<input type="hidden" name="TaxId" id="TaxIdHidden" value="{{ $pricing->TaxId }}">
</div>

</div>

{{-- PRICING --}}
<h6 class="text-muted text-uppercase fw-semibold border-bottom pb-2 mb-3">Pricing</h6>

<div class="row g-3 mb-4">

@php
$fields = [
 'Rent' => $pricing->Rent,
 'ServiceCharge' => $pricing->ServiceCharge,
 'ParkingFee' => $pricing->ParkingFee,
 'OtherCharges' => $pricing->OtherCharges,
 'DepositAmount' => $pricing->DepositAmount
];
@endphp

@foreach($fields as $name => $value)
<div class="col-md-4">
    <label>{{ str_replace('Amount','', $name) }}</label>
    <input type="number"
           id="{{ $name }}"
           class="form-control {{ $name!='DepositAmount'?'charge':'' }}"
           name="{{ $name }}"
           value="{{ $value }}"
           required>
</div>
@endforeach

</div>

{{-- TOTAL SUMMARY --}}
<div class="card bg-light border-success mb-4">
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

<div class="d-flex justify-content-between pt-3 border-top">
<a href="{{ route('propertyrateandpricing.index') }}" class="btn btn-outline-secondary">
Back
</a>
<button class="btn btn-primary px-4">Update Pricing</button>
</div>

</div>
</div>
</form>
</div>

<script>
function calculate() {

let subtotal = 0;
document.querySelectorAll('.charge').forEach(i => subtotal += Number(i.value || 0));

let taxRate = Number(document.getElementById('taxRate').value);
let tax = subtotal * taxRate / 100;
let deposit = Number(document.getElementById('DepositAmount').value || 0);

let monthlyWithTax = subtotal + tax;
let grandTotal = monthlyWithTax + deposit;

subtotalEl.innerText = subtotal.toFixed(2);
taxAmount.innerText = tax.toFixed(2);
monthlyTotal.innerText = monthlyWithTax.toFixed(2);
depositDisplay.innerText = deposit.toFixed(2);
grandTotal.innerText = grandTotal.toFixed(2);
}

const subtotalEl = document.getElementById('subtotal');
const taxAmount = document.getElementById('taxAmount');
const monthlyTotal = document.getElementById('monthlyTotal');
const depositDisplay = document.getElementById('depositDisplay');
const grandTotal = document.getElementById('grandTotal');

document.querySelectorAll('.charge, #DepositAmount, #taxRate')
.forEach(el => el.addEventListener('input', calculate));

document.getElementById('taxRate').addEventListener('change', e => {
document.getElementById('TaxIdHidden').value =
    e.target.options[e.target.selectedIndex].value;
calculate();
});

calculate();
</script>

@endsection
