@extends('layouts.app')

@section('title', 'New Lease Agreement')

@section('content')
<div class="container mt-4">

<form method="POST" action="{{ route('addlease.store') }}" enctype="multipart/form-data">
@csrf

<div class="card shadow-sm border-0 rounded-3">
<div class="card-header bg-primary fw-bold py-3">
    Lease Details
</div>

<div class="card-body">

{{-- ================= Tenant & Property ================= --}}

<h5 class="fw-bold border-bottom pb-2 mb-3">Tenant & Property Information</h5>

<div class="row g-3 mb-4">

<div class="row g-3 mb-4">
    <div class="col-md-12">
        <label class="form-label">Select Interest</label>
        <select name="InterestId" id="interest-select"
                class="form-select @error('InterestId') is-invalid @enderror">

            <option value="">-- Select Interest (Optional) --</option>

            @foreach ($interests as $i)
                <option value="{{ $i->Id }}"
                    {{ old('InterestId')==$i->Id?'selected':'' }}>
                    {{ $i->tenant->thirdParty->ThirdPartyName ?? '-' }}
                    | {{ $i->property->PropertyName ?? '-' }}
                    | {{ $i->unit->UnitCode ?? '-' }}
                </option>
            @endforeach

        </select>

        @error('InterestId')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>


<div class="col-md-6">
<label class="form-label">Tenant <span class="text-danger">*</span></label>
<select name="Tenant" class="form-select @error('Tenant') is-invalid @enderror">
<option value="">-- Select Tenant --</option>
@foreach ($newtenants as $t)
<option value="{{ $t->Id }}" {{ old('Tenant')==$t->Id?'selected':'' }}>
{{ $t->thirdParty->ThirdPartyName ?? '-' }}
</option>
@endforeach
</select>
@error('Tenant')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="col-md-6">
<label class="form-label">Property <span class="text-danger">*</span></label>
<select name="PropertyID" id="property-select"
class="form-select @error('PropertyID') is-invalid @enderror">
<option value="">-- Select Property --</option>
@foreach ($properties as $p)
<option value="{{ $p->Id }}" {{ old('PropertyID')==$p->Id?'selected':'' }}>
{{ $p->PropertyName }}
</option>
@endforeach
</select>
@error('PropertyID')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

</div>

{{-- ================= Block / Floor / Unit ================= --}}

<h5 class="fw-bold border-bottom pb-2 mb-3">Block / Floor / Unit</h5>

<div class="row g-3 mb-4">

<div class="col-md-4">
<label class="form-label">Block <span class="text-danger">*</span></label>
<select name="BlockID" id="block-select"
class="form-select @error('BlockID') is-invalid @enderror">
<option value="">-- Select Block --</option>
</select>
@error('BlockID')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="col-md-4">
<label class="form-label">Floor <span class="text-danger">*</span></label>
<select name="FloorID" id="floor-select"
class="form-select @error('FloorID') is-invalid @enderror">
<option value="">-- Select Floor --</option>
</select>
@error('FloorID')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="col-md-4">
<label class="form-label">Unit <span class="text-danger">*</span></label>
<select name="Unit" id="unit-select"
class="form-select @error('Unit') is-invalid @enderror">
<option value="">-- Select Unit --</option>
</select>
@error('Unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

</div>

{{-- ================= Lease Dates ================= --}}

<h5 class="fw-bold border-bottom pb-2 mb-3">Lease Duration</h5>

<div class="row g-3 mb-4">

<div class="col-md-4">
<label class="form-label">Start Date <span class="text-danger">*</span></label>
<input type="date" name="StartDate"
value="{{ old('StartDate') }}"
class="form-control @error('StartDate') is-invalid @enderror">
@error('StartDate')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="col-md-4">
<label class="form-label">End Date <span class="text-danger">*</span></label>
<input type="date" name="EndDate"
value="{{ old('EndDate') }}"
class="form-control @error('EndDate') is-invalid @enderror">
@error('EndDate')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="col-md-4">
<label class="form-label">Payment Frequency <span class="text-danger">*</span></label>
<select name="PaymentFrequency"
class="form-select @error('PaymentFrequency') is-invalid @enderror">
<option value="">-- Select Frequency --</option>
@foreach($codes as $c)
<option value="{{ $c->ID }}" {{ old('PaymentFrequency')==$c->ID?'selected':'' }}>
{{ $c->Description }}
</option>
@endforeach
</select>
@error('PaymentFrequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

</div>

{{-- ================= Financial ================= --}}

<h5 class="fw-bold border-bottom pb-2 mb-3">Financial Details</h5>

<div class="row g-3 mb-4">

<div class="col-md-6">
<label class="form-label">Currency <span class="text-danger">*</span></label>
<select name="CurrencyId"
class="form-select @error('CurrencyId') is-invalid @enderror">
<option value="">-- Select Currency --</option>
@foreach ($Currencies as $cur)
<option value="{{ $cur->Id }}" {{ old('CurrencyId')==$cur->Id?'selected':'' }}>
{{ $cur->Code }} - {{ $cur->Symbol }}
</option>
@endforeach
</select>
@error('CurrencyId')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="col-md-6">
<label class="form-label">Tax Rule <span class="text-danger">*</span></label>
<select name="TaxId"
class="form-select @error('TaxId') is-invalid @enderror">
<option value="">-- Select Tax Rule --</option>
@foreach ($taxtypes as $t)
<option value="{{ $t->Id }}" {{ old('TaxId')==$t->Id?'selected':'' }}>
{{ $t->taxType->TaxTypeName }} {{ $t->Rate }}
</option>
@endforeach
</select>
@error('TaxId')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

</div>

{{-- ================= Charges ================= --}}

<div class="row g-3 mb-4">

@php
$fields = [
'MonthlyRent'=>'Rent',
'Deposit'=>'Deposit',
'ServiceCharge'=>'Service Charge',
'ParkingFee'=>'Parking Fee',
'OtherCharges'=>'Other Charges'
];
@endphp

@foreach($fields as $name=>$label)
<div class="col-md-4">
<label class="form-label">{{ $label }} <span class="text-danger">*</span></label>
<input type="number" name="{{ $name }}"
value="{{ old($name) }}"
class="form-control charge-field @error($name) is-invalid @enderror">
@error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
@endforeach

<div class="col-md-4">
<label class="form-label">Total Payable (Excl. Deposit)</label>
<input type="number" id="TotalPayable" class="form-control" readonly>
</div>

</div>

{{-- ================= Due Day ================= --}}

<h5 class="fw-bold border-bottom pb-2 mb-3">Payment Due Date <span class="text-danger">*</span></h5>

<div class="col-md-4 mb-4">
<input type="number" name="DueDay"
value="{{ old('DueDay') }}"
class="form-control @error('DueDay') is-invalid @enderror"
min="1" max="28" required placeholder="Enter day of month (1-28)">
@error('DueDay')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- ================= Special Terms ================= --}}

<h5 class="fw-bold border-bottom pb-2 mb-3">Special Terms</h5>

<textarea name="SpecialTerms"
class="form-control mb-3 @error('SpecialTerms') is-invalid @enderror"
rows="3">{{ old('SpecialTerms') }}</textarea>

@error('SpecialTerms')<div class="invalid-feedback mb-3">{{ $message }}</div>@enderror

{{-- ================= Documents ================= --}}

<h5 class="fw-bold border-bottom pb-2 mb-3">Upload Documents <span class="text-danger">*</span></h5>

<input type="file" name="Document[]" multiple
class="form-control mb-4 @error('Document') is-invalid @enderror" required>

@error('Document')<div class="invalid-feedback mb-3">{{ $message }}</div>@enderror

<div class="d-flex justify-content-between gap-3">
<a href="{{ route('addlease.index') }}" class="btn btn-outline-secondary">Cancel</a>
<button class="btn btn-success">Save Lease</button>
</div>

</div>
</div>
</form>
</div>

{{-- ================= JS ================= --}}

<script>
document.addEventListener('DOMContentLoaded', () => {

    // =============================
    // Elements
    // =============================
    const interestSelect = document.getElementById('interest-select');
    const propertySelect = document.getElementById('property-select');
    const blockSelect = document.getElementById('block-select');
    const floorSelect = document.getElementById('floor-select');
    const unitSelect = document.getElementById('unit-select');

    const rentInput = document.querySelector('input[name="MonthlyRent"]');
    const depositInput = document.querySelector('input[name="Deposit"]');
    const serviceInput = document.querySelector('input[name="ServiceCharge"]');
    const parkingInput = document.querySelector('input[name="ParkingFee"]');
    const otherInput = document.querySelector('input[name="OtherCharges"]');

    const taxSelect = document.querySelector('select[name="TaxId"]');
    const currencySelect = document.querySelector('select[name="CurrencyId"]');

    const startDateInput = document.querySelector('input[name="StartDate"]');
    const endDateInput = document.querySelector('input[name="EndDate"]');
    const paymentFreqSelect = document.querySelector('select[name="PaymentFrequency"]');

    const chargeFields = document.querySelectorAll('.charge-field');
    const totalField = document.getElementById('TotalPayable');

    const routes = {
        getBlocks: "{{ route('getblockbyproperty.lease', ['PropertyId' => '__ID__']) }}",
        getFloors: "{{ route('getfloorbyblock.lease', ['BlockId' => '__ID__']) }}",
        getUnits: "{{ route('getunitbyfloor.lease', ['FloorId' => '__ID__']) }}",
        getPricing: "{{ route('getpricingunit.lease', ['UnitId' => '__ID__']) }}"
    };

    // =============================
    // Helper Functions
    // =============================
    const resetOptions = (select, label) => {
        select.innerHTML = `<option value="">-- ${label} --</option>`;
    };

    const loadBlocks = async (propertyId) => {
        resetOptions(blockSelect, 'Select Block');
        resetOptions(floorSelect, 'Select Floor');
        resetOptions(unitSelect, 'Select Unit');
        if (!propertyId) return;

        const res = await fetch(routes.getBlocks.replace('__ID__', propertyId));
        const blocks = await res.json();
        blocks.forEach(b => {
            blockSelect.insertAdjacentHTML('beforeend',
                `<option value="${b.Id}">${b.BlockName}</option>`
            );
        });
    };

    const loadFloors = async (blockId) => {
        resetOptions(floorSelect, 'Select Floor');
        resetOptions(unitSelect, 'Select Unit');
        if (!blockId) return;

        const res = await fetch(routes.getFloors.replace('__ID__', blockId));
        const floors = await res.json();
        floors.forEach(f => {
            floorSelect.insertAdjacentHTML('beforeend',
                `<option value="${f.Id}">${f.FloorLabel}</option>`
            );
        });
    };

    const loadUnits = async (floorId) => {
        resetOptions(unitSelect, 'Select Unit');
        if (!floorId) return;

        const res = await fetch(routes.getUnits.replace('__ID__', floorId));
        const units = await res.json();
        units.forEach(u => {
            unitSelect.insertAdjacentHTML('beforeend',
                `<option value="${u.Id}">${u.UnitCode}</option>`
            );
        });
    };

    const loadPricing = async (unitId) => {
        if (!unitId) return;

        const res = await fetch(routes.getPricing.replace('__ID__', unitId));
        const p = await res.json();
        if (!p) return;

        rentInput.value = p.Rent ?? '';
        depositInput.value = p.DepositAmount ?? '';
        serviceInput.value = p.ServiceCharge ?? '';
        parkingInput.value = p.ParkingFee ?? '';
        otherInput.value = p.OtherCharges ?? '';

        taxSelect.value = p.TaxId ?? '';
        currencySelect.value = p.CurrencyId ?? '';

        calculateTotal();
    };

    const calculateTotal = () => {
        let total = 0;
        chargeFields.forEach(f => total += parseFloat(f.value) || 0);
        totalField.value = total;
    };

    chargeFields.forEach(f => f.addEventListener('input', calculateTotal));

    // =============================
    // Event Listeners
    // =============================

    // Cascading dropdowns
    propertySelect.addEventListener('change', () => loadBlocks(propertySelect.value));
    blockSelect.addEventListener('change', () => loadFloors(blockSelect.value));
    floorSelect.addEventListener('change', () => loadUnits(floorSelect.value));
    unitSelect.addEventListener('change', () => loadPricing(unitSelect.value));

    // Interest select autofill
    interestSelect?.addEventListener('change', async function () {
        if (!this.value) return;

        const res = await fetch(
            "{{ route('lease.getInterest', '__ID__') }}".replace('__ID__', this.value)
        );
        const data = await res.json();

        // =============================
        // Basic Info
        // =============================
        document.querySelector('[name="Tenant"]').value = data.TenantId ?? '';
        startDateInput.value = data.StartDate ?? '';
        endDateInput.value = data.EndDate ?? '';
        paymentFreqSelect.value = data.PaymentFrequency ?? '';
        propertySelect.value = data.PropertyId ?? '';

        // =============================
        // Cascading selects
        // =============================
        await loadBlocks(data.PropertyId);
        blockSelect.value = data.BlockId ?? '';
        await loadFloors(data.BlockId);
        floorSelect.value = data.FloorId ?? '';
        await loadUnits(data.FloorId);
        unitSelect.value = data.UnitId ?? '';

        // =============================
        // Financials
        // =============================
        if (data.pricing) {
            rentInput.value = data.pricing.Rent ?? '';
            depositInput.value = data.pricing.DepositAmount ?? '';
            serviceInput.value = data.pricing.ServiceCharge ?? '';
            parkingInput.value = data.pricing.ParkingFee ?? '';
            otherInput.value = data.pricing.OtherCharges ?? '';

            taxSelect.value = data.pricing.TaxId ?? '';
            currencySelect.value = data.pricing.CurrencyId ?? '';

            calculateTotal();
        } else {
            await loadPricing(data.UnitId);
        }
    });

});
</script>

@endsection
