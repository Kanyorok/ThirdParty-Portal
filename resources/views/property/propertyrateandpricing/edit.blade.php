@extends('layouts.app')
@section('title', 'Edit Property Rate & Pricing')

@section('content')

<div class="container py-4" style="max-width:1100px;">

{{-- ================= ERROR SUMMARY ================= --}}
@if ($errors->any())
<div class="alert alert-danger shadow-sm">
    <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('propertyrateandpricing.update', $pricing->Id) }}" method="POST">
@csrf
@method('PUT')

<div class="card shadow-sm border-0 rounded-4">
<div class="card-body p-4">

{{-- ================= PROPERTY STRUCTURE ================= --}}
<h6 class="fw-semibold border-bottom pb-2 mb-3">Property Structure</h6>

<div class="row g-3 mb-4">
@foreach([
    'Property' => $pricing->property->PropertyName ?? 'N/A',
    'Block' => $pricing->block->BlockName ?? 'N/A',
    'Floor' => $pricing->floor->FloorLabel ?? 'N/A',
    'Unit' => $pricing->unit->UnitCode ?? 'N/A'
] as $label => $value)
<div class="col-md-3">
    <label class="form-label">{{ $label }}</label>
    <div class="form-control bg-light fw-semibold">{{ $value }}</div>
</div>
@endforeach
</div>

<input type="hidden" name="PropertyId" value="{{ $pricing->PropertyId }}">
<input type="hidden" name="BlockId" value="{{ $pricing->BlockId }}">
<input type="hidden" name="FloorId" value="{{ $pricing->FloorId }}">
<input type="hidden" name="UnitId" value="{{ $pricing->UnitId }}">

{{-- ================= FINANCIAL SETTINGS ================= --}}
<h6 class="fw-semibold border-bottom pb-2 mb-3">Financial Settings</h6>

<div class="row g-3 mb-4">

<div class="col-md-6">
<label class="form-label">Currency</label>
<select class="form-select" name="CurrencyId" required>
@foreach ($currencies as $cur)
<option value="{{ $cur->Id }}" {{ $pricing->CurrencyId==$cur->Id?'selected':'' }}>
    {{ $cur->Code }} — {{ $cur->Symbol }}
</option>
@endforeach
</select>
</div>

<div class="col-md-6">
<label class="form-label">Tax</label>
<select id="taxRate" class="form-select" required>
@foreach ($Taxes as $tax)
<option value="{{ $tax->Rate }}"
        data-id="{{ $tax->Id }}"
        {{ $pricing->TaxId==$tax->Id?'selected':'' }}>
    {{ $tax->taxType->TaxTypeName }} ({{ $tax->Rate }}%)
</option>
@endforeach
</select>
<input type="hidden" name="TaxId" id="TaxIdHidden" value="{{ $pricing->TaxId }}">
</div>

</div>

{{-- ================= CHARGES ================= --}}
<h6 class="fw-semibold border-bottom pb-2 mb-3">Charges</h6>

<div class="row g-3 mb-4">

<div class="col-md-3">
<label class="form-label">Rent</label>
<input type="number" name="Rent" class="form-control charge" value="{{ $pricing->Rent }}" required>
</div>

<div class="col-md-3">
<label class="form-label">Parking Fee</label>
<input type="number" name="ParkingFee" class="form-control charge" value="{{ $pricing->ParkingFee }}">
</div>

<div class="col-md-3">
<label class="form-label">Service Charge</label>
<input type="number" name="ServiceCharge" class="form-control charge" value="{{ $pricing->ServiceCharge }}">
</div>

<div class="col-md-3">
<label class="form-label">Other Charges</label>
<input type="number" name="OtherCharges" class="form-control charge" value="{{ $pricing->OtherCharges }}">
</div>

<div class="col-md-4">
<label class="form-label">Deposit Amount</label>
<input type="number" id="deposit" name="DepositAmount" class="form-control" value="{{ $pricing->DepositAmount }}">
</div>

</div>

{{-- ================= TOTAL SUMMARY ================= --}}
<div class="card bg-light border-0 shadow-sm rounded-4 mb-4">
<div class="card-body">

<div class="row align-items-center">

<div class="col-md-6">
<small class="text-muted">MONTHLY (INCLUDING TAX)</small>
<h2 class="fw-bold text-success mt-1" id="monthlyTotal">0.00</h2>
</div>

<div class="col-md-6 text-md-end small">
<div>Subtotal: <strong id="subtotal">0.00</strong></div>
<div>Tax: <strong id="taxAmount">0.00</strong></div>
<div>Deposit: <strong id="depositDisplay">0.00</strong></div>
<hr class="my-2">
<div class="fs-6">
Total Payable: <strong id="grandTotal">0.00</strong>
</div>
</div>

</div>
</div>
</div>

{{-- ================= ACTIONS ================= --}}
<div class="d-flex justify-content-between pt-3 border-top">
<a href="{{ route('propertyrateandpricing.index') }}" class="btn btn-outline-secondary">
Back
</a>
<button type="submit" class="btn btn-primary px-4">
Update Pricing
</button>
</div>

</div>
</div>
</form>
</div>

{{-- ================= CALCULATIONS ================= --}}
<script>
function calculate() {

let subtotal = 0;
document.querySelectorAll('.charge').forEach(i => {
    subtotal += Number(i.value || 0);
});

let taxRate = Number(document.getElementById('taxRate').value || 0);
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
.forEach(el => el.addEventListener('input', calculate));

document.getElementById('taxRate').addEventListener('change', function () {
let selected = this.options[this.selectedIndex];
document.getElementById('TaxIdHidden').value = selected.dataset.id || '';
calculate();
});

calculate();
</script>

@endsection
