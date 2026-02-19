@extends('layouts.app')

@section('title','Interest Details')

@section('content')
<div class="container mt-4" style="max-width:950px">

    <div class="card shadow border-0">

        {{-- Header --}}
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Tenant Interest Details</h5>
        </div>

        <div class="card-body">

            {{-- General Info --}}
            <h6 class="fw-bold mb-3 border-bottom pb-2">
                General Information
            </h6>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Tenant</label>
                    <input type="text" class="form-control" value="{{ $interest->tenant->thirdParty->ThirdPartyName ?? 'Name' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Property</label>
                    <input type="text" class="form-control" value="{{ $interest->property->PropertyName ?? 'Property Name'}}" readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Block</label>
                    <input type="text" class="form-control" value="{{ $interest->block->BlockName ?? 'Block'}}" readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Floor</label>
                    <input type="text" class="form-control" value="{{ $interest->floor->FloorLabel ?? 'Floor'}}" readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Unit</label>
                    <input type="text" class="form-control" value="{{ $interest->unit->UnitCode ?? 'Code'}}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="text" class="form-control" 
                        value="{{ \Carbon\Carbon::parse($interest->InterestedStartDate)->format('d M Y') }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="text" class="form-control" 
                        value="{{ \Carbon\Carbon::parse($interest->InterestedEndDate)->format('d M Y') }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Payment Mode</label>
                    <input type="text" class="form-control" value="{{ $interest->code->Description }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Notes</label>
                    <input type="text" class="form-control" 
                        value="{{ $interest->AdditionalInformation ?? '—' }}" readonly>
                </div>

            </div>

            {{-- Pricing --}}
            @if($interest->unit && $interest->price)

            <h6 class="fw-bold mt-4 mb-3 border-bottom pb-2">
                Costing & Pricing
            </h6>

            @php
                $rent = $interest->price->Rent ?? 0;
                $parking = $interest->price->ParkingFee ?? 0;
                $service = $interest->price->ServiceCharge ?? 0;
                $other = $interest->price->OtherCharges ?? 0;
                $deposit = $interest->price->DepositAmount ?? 0;

                $totalExcl = $rent + $parking + $service + $other;
                $totalIncl = $totalExcl + $deposit;
            @endphp

            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label">Rent</label>
                    <input class="form-control" value="{{ number_format($rent) }}" readonly>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Parking</label>
                    <input class="form-control" value="{{ number_format($parking) }}" readonly>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Service Charge</label>
                    <input class="form-control" value="{{ number_format($service) }}" readonly>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Other Charges</label>
                    <input class="form-control" value="{{ number_format($other) }}" readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Deposit</label>
                    <input class="form-control" value="{{ number_format($deposit) }}" readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Total (Excl. Deposit)
                    </label>
                    <input class="form-control fw-bold" 
                        value="{{ number_format($totalExcl) }}" readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Total (Incl. Deposit)
                    </label>
                    <input class="form-control fw-bold" 
                        value="{{ number_format($totalIncl) }}" readonly>
                </div>

            </div>

            @else
                <div class="alert alert-warning mt-3 text-center">
                    Pricing information not available.
                </div>
            @endif

            {{-- Footer --}}
            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('property-interest.index') }}" class="btn btn-outline-secondary px-4">
                    Back
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
