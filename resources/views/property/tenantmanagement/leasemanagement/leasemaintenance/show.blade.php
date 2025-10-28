@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            {{-- Lease Information --}}
            <h6 class="mb-3 text-dark">Lease Information</h6>
            <hr>
            <div class="row g-3 text-dark">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lease Number</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ $newlease->LeaseNumber ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tenant</label>
                    <input type="text" class="form-control bg-light text-dark"
                           value="{{ $newlease->tenant->thirdParty->ThirdPartyName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Property</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ $newlease->property->PropertyName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Block</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ $newlease->block->BlockName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Floor</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ $newlease->floor->FloorLabel ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Unit</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ $newlease->unit->UnitCode ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input type="text" class="form-control bg-light text-dark"
                           value="{{ $newlease->StartDate ? Carbon::parse($newlease->StartDate)->format('d/m/Y') : '-' }}"
                           readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">End Date</label>
                    <input type="text" class="form-control bg-light text-dark"
                           value="{{ $newlease->EndDate ? Carbon::parse($newlease->EndDate)->format('d/m/Y') : '-' }}"
                           readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Payment Frequency</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ $newlease->code->Description ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Due Day</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ $newlease->DueDay ?? '-' }}" readonly>
                </div>
            </div><br>


            {{-- Financial Information --}}
            <h6 class="mb-3 text-dark">Financial Information</h6>
            <hr>
            <div class="row g-3 text-dark">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rent Amount</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ number_format($newlease->MonthlyRent, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Deposit</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ number_format($newlease->Deposit, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Service Charge</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ number_format($newlease->ServiceCharge, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Parking Fee</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ number_format($newlease->ParkingFee, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Other Charges</label>
                    <input type="text" class="form-control bg-light text-dark" value="{{ number_format($newlease->OtherCharges, 2) }}" readonly>
                </div>
            </div>

            <hr class="my-4">

            {{-- Special Terms --}}
            <h6 class="mb-3 text-dark">Special Terms</h6>
            <div class="mb-3">
                <textarea class="form-control bg-light text-dark" rows="3" readonly>{{ $newlease->SpecialTerms ?? '—' }}</textarea>
            </div>

            {{-- Attached Documents --}}
            <h6 class="mb-3 text-dark">Attached Documents</h6>
            <div class="p-3 border rounded bg-light text-dark">
                @forelse($newlease->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                    {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                @empty
                    <span>No documents attached.</span>
                @endforelse
            </div>
        </div>

        {{-- Footer with Audit Info + Actions --}}
        <div class="card-footer d-flex justify-content-between align-items-center py-2 bg-light small text-dark">
            <div>
                Created by <strong>{{ $newlease->createdByUser->Name ?? '-' }}</strong>
                on <strong>{{ $newlease->CreatedOn ? Carbon::parse($newlease->CreatedOn)->format('d/m/Y') : '-' }}</strong>
                | Modified by <strong>{{ $newlease->modifiedByUser->Name ?? '-' }}</strong>
                on <strong>{{ $newlease->ModifiedOn ? Carbon::parse($newlease->ModifiedOn)->format('d/m/Y') : '-' }}</strong>
            </div>
            <div>
                <a href="{{ route('addlease.edit', $newlease->Id) }}" class="btn btn-sm btn-dark">Edit</a>
                <a href="{{ route('addlease.index') }}" class="btn btn-sm btn-dark">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
 @include('snippets.actions.preview-files')
@endsection
