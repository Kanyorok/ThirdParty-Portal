@extends('layouts.app')
@section('title', 'Lease Schedule Generator')

@section('content')

@if (session('error'))
<script>alert("{{ session('error') }}");</script>
@endif

<div class="container mt-4">

<form action="{{ route('schedulelease.store') }}" method="POST">
@csrf

<div class="card shadow">

<div class="card-header bg-primary fw-bold">
Generate Billing Periods
</div>

<div class="card-body">

{{-- Lease selector --}}
<div class="row g-3 mb-3">
<div class="col-md-6">

<label class="form-label">Select Lease Number *</label>

<select id="lease-select" name="LeaseId" class="form-select" required>
<option value="">-- Select Lease --</option>

@foreach ($newleases as $lease)
<option value="{{ $lease->Id }}"
data-leasenumber="{{ $lease->LeaseNumber }}"
data-tenant-name="{{ $lease->tenant->thirdParty->ThirdPartyName ?? '' }}"
data-property-name="{{ $lease->property->PropertyName ?? '' }}"
data-frequency-name="{{ $lease->code->Description ?? '' }}"
data-baserent="{{ $lease->MonthlyRent ?? 0 }}"
data-servicecharge="{{ $lease->ServiceCharge ?? 0 }}"
data-parkingfee="{{ $lease->ParkingFee ?? 0 }}"
data-othercharges="{{ $lease->OtherCharges ?? 0 }}"
data-currency="{{ $lease->currency->SymbolNative ?? 'KES' }}"
data-tax="{{ $lease->taxRule->Rate ?? 0 }}"
>
{{ $lease->LeaseNumber }}
</option>
@endforeach

</select>
</div>
</div>

{{-- Lease details --}}
<div id="lease-details" class="d-none">

<div class="row g-3 mb-3">

<div class="col-md-6">
<label>Lease Number</label>
<input id="lease-display" class="form-control" readonly>
</div>

<div class="col-md-6">
<label>Payment Frequency</label>
<input id="frequency-display" class="form-control" readonly>
</div>

<div class="col-md-6">
<label>Tenant</label>
<input id="tenant-display" class="form-control" readonly>
</div>

<div class="col-md-6">
<label>Property</label>
<input id="property-display" class="form-control" readonly>
</div>

<div class="col-md-6">
<label>Currency</label>
<input id="currency-display" class="form-control" readonly>
</div>

<div class="col-md-6">
<label>Tax Rate (%)</label>
<input id="tax-display" class="form-control" readonly>
</div>

</div>

{{-- Dates --}}
<div class="row g-3 mb-3">

<div class="col-md-4">
<label>Start Date</label>
<input type="date" name="StartDate" class="form-control" required>
</div>

<div class="col-md-4">
<label>End Date</label>
<input type="date" name="EndDate" class="form-control" required>
</div>

</div>

{{-- Charges --}}
<div class="row g-3 mb-3">

<div class="col-md-3">
<label>Base Rent</label>
<input type="number" id="baserent" name="BaseRent" class="form-control" required>
</div>

<div class="col-md-3">
<label>Service Charge</label>
<input type="number" id="servicecharge" name="ServiceCharge" class="form-control" required>
</div>

<div class="col-md-3">
<label>Parking Fee</label>
<input type="number" id="parkingfee" name="ParkingFee" class="form-control" required>
</div>

<div class="col-md-3">
<label>Other Charges</label>
<input type="number" id="othercharges" name="OtherCharges" class="form-control" required>
</div>

</div>

{{-- Totals --}}
<div class="row g-3 mb-3">

<div class="col-md-4">
<label>Subtotal</label>
<input id="subtotal" class="form-control" readonly>
</div>

<div class="col-md-4">
<label>Tax Amount</label>
<input id="tax-amount" class="form-control" readonly>
</div>

<div class="col-md-4">
<label class="fw-bold text-success">Grand Total</label>
<input id="grand-total" class="form-control fw-bold" readonly>
</div>

</div>

</div>

<div class="d-flex justify-content-between">
<a href="{{ route('schedulelease.index') }}" class="btn btn-secondary">Cancel</a>

<button class="btn btn-success">
Generate Schedule
</button>
</div>

</div>
</div>
</form>
</div>

<script>
const leaseDetails = document.getElementById('lease-details');
const leaseSelect = document.getElementById('lease-select');

const ids = {
 lease:'lease-display',
 tenant:'tenant-display',
 property:'property-display',
 frequency:'frequency-display',
 currency:'currency-display',
 tax:'tax-display',
 base:'baserent',
 service:'servicecharge',
 parking:'parkingfee',
 other:'othercharges',
 subtotal:'subtotal',
 taxAmount:'tax-amount',
 total:'grand-total'
};

function el(id){ return document.getElementById(id); }

leaseSelect.addEventListener('change', function(){

 if(!this.value){
   leaseDetails.classList.add('d-none');
   return;
 }

 leaseDetails.classList.remove('d-none');

 const s = this.options[this.selectedIndex].dataset;

 el(ids.lease).value = s.leasenumber;
 el(ids.tenant).value = s.tenantName;
 el(ids.property).value = s.propertyName;
 el(ids.frequency).value = s.frequencyName;
 el(ids.currency).value = s.currency;
 el(ids.tax).value = s.tax;

 el(ids.base).value = s.baserent;
 el(ids.service).value = s.servicecharge;
 el(ids.parking).value = s.parkingfee;
 el(ids.other).value = s.othercharges;

 calculate();
});

function calculate(){

 const base = parseFloat(el(ids.base).value)||0;
 const service = parseFloat(el(ids.service).value)||0;
 const parking = parseFloat(el(ids.parking).value)||0;
 const other = parseFloat(el(ids.other).value)||0;
 const taxRate = parseFloat(el(ids.tax).value)||0;

 const subtotal = base + service + parking + other;
 const tax = subtotal * taxRate / 100;
 const total = subtotal + tax;

 el(ids.subtotal).value = subtotal.toFixed(2);
 el(ids.taxAmount).value = tax.toFixed(2);
 el(ids.total).value = total.toFixed(2);
}

['baserent','servicecharge','parkingfee','othercharges']
.forEach(id => el(id).addEventListener('input', calculate));
</script>

@endsection
