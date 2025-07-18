@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Details')

@section('content')
    <div class="container mt-5" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Lease Agreement Details</h3>
            <a href="{{ route('addlease.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
        </div>

        <form>
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Lease Number</label>
                        <input type="text" class="form-control" value="{{ $newlease->LeaseNumber ?? '-' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tenant</label>
                        <input type="text" class="form-control" value="{{ $newlease->tenant->TenantName }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Property</label>
                        <input type="text" class="form-control" value="{{ $newlease->property->PropertyName ?? '-' }}"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Block</label>
                        <input type="text" class="form-control" value="{{ $newlease->block->BlockName ?? '-' }}"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Floor</label>
                        <input type="text" class="form-control" value="{{ $newlease->floor->FloorLabel ?? '-' }}"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" value="{{ $newlease->unit->UnitCode ?? '-' }}" readonly>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="text" class="form-control"
                                   value="{{ Carbon::parse($newlease->StartDate)->format('d/m/Y') }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="text" class="form-control"
                                   value="{{ Carbon::parse($newlease->EndDate)->format('d/m/Y') }}" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Frequency</label>
                        <input type="text" class="form-control" value="{{ $newlease->code->Description ?? '-' }}"
                               readonly>
                    </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Monthly Rent (KES)</label>
                        <input type="text" class="form-control" value="{{ number_format($newlease->MonthlyRent, 2) }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Deposit (KES)</label>
                        <input type="text" class="form-control" value="{{ number_format($newlease->Deposit, 2) }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Service Charge (KES)</label>
                        <input type="text" class="form-control" value="{{ number_format($newlease->ServiceCharge, 2) }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Parking Fee (KES)</label>
                        <input type="text" class="form-control" value="{{ number_format($newlease->ParkingFee, 2) }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Other charges (KES)</label>
                        <input type="text" class="form-control" value="{{ number_format($newlease->OtherCharges, 2) }}" readonly>
                    </div>
                </div>

                    <div class="mb-3">
                        <label class="form-label">Due Day</label>
                        <input type="text" class="form-control" value="{{ $newlease->DueDay }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Special Terms</label>
                        <textarea class="form-control" rows="3" readonly>{{ $newlease->SpecialTerms ?? '—' }}</textarea>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ route('addlease.edit', $newlease->Id) }}" class="btn btn-outline-primary"><i
                            class="bi bi-pencil-square"></i> Edit</a>
                    <a href="{{ route('addlease.index') }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
@endsection
