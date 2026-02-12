@extends('layouts.app')
@section('title', 'Property Rate & Pricing Setup')

@section('content')

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

<div class="container mt-4" style="max-width:1100px;">

<form action="{{ route('propertyrateandpricing.store') }}" method="POST">
@csrf

<div class="card shadow-sm border-0 rounded-4">
<div class="card-body p-4">

{{-- ================= PROPERTY STRUCTURE ================= --}}
<h6 class="fw-semibold border-bottom pb-2 mb-3">Property Structure</h6>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">Property <span class="text-danger">*</span></label>
        <select name="PropertyId" id="property-select" class="form-select" required>
            <option value="">-- Select Property --</option>
            @foreach ($property as $item)
                <option value="{{ $item->Id }}">{{ $item->PropertyName }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Block <span class="text-danger">*</span></label>
        <select name="BlockId" id="block-select" class="form-select" required>
            <option value="">-- Select Block --</option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Floor <span class="text-danger">*</span></label>
        <select name="FloorId" id="floor-select" class="form-select" required>
            <option value="">-- Select Floor --</option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Unit <span class="text-danger">*</span></label>
        <select name="UnitId" id="unit-select" class="form-select" required>
            <option value="">-- Select Unit --</option>
        </select>
    </div>
</div>

{{-- ================= MONTHLY CHARGES ================= --}}
<h6 class="fw-semibold border-bottom pb-2 mb-3">Monthly Charges</h6>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">Rent <span class="text-danger">*</span></label>
        <input type="number" name="Rent" class="form-control charge" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Parking Fee <span class="text-danger">*</span></label>
        <input type="number" name="ParkingFee" class="form-control charge" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Service Charge <span class="text-danger">*</span></label>
        <input type="number" name="ServiceCharge" class="form-control charge" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Other Charges <span class="text-danger">*</span></label>
        <input type="number" name="OtherCharges" class="form-control charge" required>
    </div>
</div>

{{-- ================= FINANCIAL SETTINGS ================= --}}
<h6 class="fw-semibold border-bottom pb-2 mb-3">Financial Settings</h6>

<div class="row g-3 mb-4">

    <div class="col-md-4">
        <label class="form-label">Deposit Amount <span class="text-danger">*</span></label>
        <input type="number" id="deposit" name="DepositAmount" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Currency <span class="text-danger">*</span></label>
        <select class="form-select" name="CurrencyId" required>
            <option value="">-- Select Currency --</option>
            @foreach($currencies as $cur)
                <option value="{{ $cur->Id }}">
                    {{ $cur->Code }} — {{ $cur->Symbol }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Tax <span class="text-danger">*</span></label>
        <select id="taxRate" class="form-select" required>
            <option value="0">-- Select Tax --</option>
            @foreach($Taxes as $tax)
                <option value="{{ $tax->Rate }}" data-id="{{ $tax->Id }}">
                    {{ $tax->taxType->TaxTypeName }} ({{ $tax->Rate }}%)
                </option>
            @endforeach
        </select>
        <input type="hidden" name="TaxId" id="TaxIdHidden">
    </div>

</div>

{{-- ================= TOTAL SUMMARY ================= --}}
<div class="card bg-light border-0 shadow-sm mb-4 rounded-4">
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
            Total Payable:
            <strong id="grandTotal">0.00</strong>
        </div>
    </div>
</div>

</div>
</div>

{{-- ================= ACTION ================= --}}
<div class="d-flex justify-content-end">
<button type="submit"
        class="btn btn-success px-4"
        onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
    Save Pricing
</button>
</div>

</div>
</div>
</form>
</div>

{{-- ================= CALCULATIONS ================= --}}
<script>
function calcTotals() {

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
    .forEach(el => el.addEventListener('input', calcTotals));

document.getElementById('taxRate').addEventListener('change', function () {
    let selected = this.options[this.selectedIndex];
    document.getElementById('TaxIdHidden').value = selected.dataset.id || '';
    calcTotals();
});
</script>

{{-- ================= DYNAMIC DROPDOWNS ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const propertySelect = document.getElementById('property-select');
    const blockSelect = document.getElementById('block-select');
    const floorSelect = document.getElementById('floor-select');
    const unitSelect = document.getElementById('unit-select');

    function reset(select, label) {
        select.innerHTML = `<option value="">-- ${label} --</option>`;
    }

    propertySelect.addEventListener('change', function () {
        reset(blockSelect,'Select Block');
        reset(floorSelect,'Select Floor');
        reset(unitSelect,'Select Unit');

        if (!this.value) return;

        fetch(`{{ route('getblockbyproperty.rate', ':id') }}`.replace(':id', this.value))
            .then(r => r.json())
            .then(data => data.forEach(b =>
                blockSelect.innerHTML += `<option value="${b.Id}">${b.BlockName}</option>`
            ));
    });

    blockSelect.addEventListener('change', function () {
        reset(floorSelect,'Select Floor');
        reset(unitSelect,'Select Unit');

        if (!this.value) return;

        fetch(`{{ route('getfloorbyblock.rate', ':id') }}`.replace(':id', this.value))
            .then(r => r.json())
            .then(data => data.forEach(f =>
                floorSelect.innerHTML += `<option value="${f.Id}">${f.FloorLabel}</option>`
            ));
    });

    floorSelect.addEventListener('change', function () {
        reset(unitSelect,'Select Unit');

        if (!this.value) return;

        fetch(`{{ route('getunitsbyfloor.rate', ':id') }}`.replace(':id', this.value))
            .then(r => r.json())
            .then(data => data.forEach(u =>
                unitSelect.innerHTML += `<option value="${u.Id}">${u.UnitCode}</option>`
            ));
    });

});
</script>

@endsection
