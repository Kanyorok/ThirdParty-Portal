@extends('layouts.app')

@section('title', 'View Property Rate & Pricing')

@section('content')
<div class="container py-4" style="max-width: 1000px;">

<div class="card shadow border-0">
<div class="card-body">

{{-- PROPERTY INFO --}}
<h6 class="fw-semibold text-black border-bottom pb-2 mb-3 text-uppercase">
Property Information
</h6>

<div class="row g-3 mb-4">
@foreach([
 'Property' => $pricing->property->PropertyName ?? 'N/A',
 'Block' => $pricing->block->BlockName ?? 'N/A',
 'Floor' => $pricing->floor->FloorLabel ?? 'N/A',
 'Unit' => $pricing->unit->UnitCode ?? 'N/A'
] as $label => $value)

<div class="col-md-3">
<label class="text-muted small">{{ $label }}</label>
<div class="form-control bg-light fw-semibold">
{{ $value }}
</div>
</div>
@endforeach
</div>

{{-- CURRENCY & TAX --}}
<h6 class="fw-semibold text-black border-bottom pb-2 mb-3 text-uppercase">
Currency & Tax
</h6>

<div class="row g-3 mb-4">

<div class="col-md-6">
<label class="text-muted small">Currency</label>
<div class="form-control bg-light fw-semibold">
{{ $pricing->currency->Code ?? 'N/A' }} ({{ $pricing->currency->Symbol ?? '' }})
</div>
</div>

<div class="col-md-6">
<label class="text-muted small">Tax Rule</label>
<div class="form-control bg-light fw-semibold">
{{ $pricing->tax->taxType->TaxTypeName ?? 'N/A' }} — {{ $pricing->tax->Rate ?? 0 }}%
</div>
</div>

</div>

{{-- PRICING --}}
<h6 class="fw-semibold text-black border-bottom pb-2 mb-3 text-uppercase">
Pricing Breakdown
</h6>

<div class="row g-3 mb-4">

@php
$charges = [
 'Rent' => $pricing->Rent,
 'Service Charge' => $pricing->ServiceCharge,
 'Parking Fee' => $pricing->ParkingFee,
 'Other Charges' => $pricing->OtherCharges
];
@endphp

@foreach($charges as $label => $amount)
<div class="col-md-3">
<label class="text-muted small">{{ $label }}</label>
<div class="form-control bg-light fw-semibold">
{{ number_format($amount, 2) }}
</div>
</div>
@endforeach

<div class="col-md-3">
<label class="text-muted small">Deposit</label>
<div class="form-control bg-light fw-semibold">
{{ number_format($pricing->DepositAmount, 2) }}
</div>
</div>

</div>

{{-- CALCULATIONS --}}
@php
$subtotal = $pricing->Rent + $pricing->ServiceCharge + $pricing->ParkingFee + $pricing->OtherCharges;
$taxRate = $pricing->tax->Rate ?? 0;
$taxAmount = $subtotal * $taxRate / 100;
$monthlyWithTax = $subtotal + $taxAmount;
$grandTotal = $monthlyWithTax + $pricing->DepositAmount;
@endphp

<h6 class="fw-semibold text-black border-bottom pb-2 mb-3 text-uppercase">
Payment Summary
</h6>

<div class="card bg-light border-success mb-4">
<div class="card-body">

<div class="row">

<div class="col-md-6">
<small class="text-muted">MONTHLY (INCL TAX)</small>
<h3 class="fw-bold text-success mb-0">
{{ number_format($monthlyWithTax, 2) }}
</h3>
</div>

<div class="col-md-6 text-md-end">
<p>Subtotal: <strong>{{ number_format($subtotal, 2) }}</strong></p>
<p>Tax ({{ $taxRate }}%): <strong>{{ number_format($taxAmount, 2) }}</strong></p>
<p>Deposit: <strong>{{ number_format($pricing->DepositAmount, 2) }}</strong></p>
<hr>
<h4>Total Payable: <strong>{{ number_format($grandTotal, 2) }}</strong></h4>
</div>

</div>
</div>
</div>

{{-- ACTIONS --}}
<div class="d-flex justify-content-between pt-3 border-top">

<a href="{{ route('propertyrateandpricing.index') }}" class="btn btn-outline-secondary">
Back
</a>

<a href="{{ route('propertyrateandpricing.edit', $pricing->Id) }}" class="btn btn-primary px-4">
Edit Pricing
</a>

</div>

</div>
</div>

</div>
@endsection
