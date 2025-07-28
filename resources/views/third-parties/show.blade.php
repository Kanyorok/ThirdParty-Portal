@extends('layouts.app')

@section('title', 'View Third Party - ' . ($party->ThirdPartyName ?? 'N/A'))

@section('styles')
<style>
    .card {
        border-radius: 0.75rem;
        border: 1px solid #dee2e6;
        transition: box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .card-header,
    .card-footer {
        background-color: #fff;
        padding: 1.25rem 1.5rem;
    }

    .card-header {
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .card-footer {
        border-top: 1px solid #e9ecef;
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }

    .detail-item dt {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 600;
    }

    .detail-item dd {
        font-weight: 500;
        color: #212529;
        margin-bottom: 0;
        word-break: break-word;
    }

    .btn i {
        margin-right: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                Third Party: {{ $party->ThirdPartyName ?? 'N/A' }}
            </h5>
            <a href="{{ route('web.parties.index') }}" class="btn btn-sm btn-outline-secondary" aria-label="Go back to list">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <div class="row">
                @php
                $details = [
                'Company Name' => $party->ThirdPartyName,
                'Trading Name' => $party->TradingName,
                'Business Type' => $party->BusinessType?->label(),
                'Registration Number' => $party->RegistrationNumber,
                'Tax PIN' => $party->TaxPIN,
                'VAT Number' => $party->VATNumber,
                'Country' => $party->Country,
                'Physical Address' => $party->PhysicalAddress,
                'Email' => $party->Email,
                'Phone' => $party->Phone,
                'Website' => $party->Website,
                'Approval Status' => $party->ApprovalStatus?->label(),
                'Status' => $party->Status?->value,
                'Third Party Type' => $party->ThirdPartyType?->label(),
                'Created On' => $party->CreatedOn?->format('Y-m-d H:i:s'),
                'Modified On' => $party->ModifiedOn?->format('Y-m-d H:i:s'),
                ];
                @endphp

                @foreach ($details as $label => $value)
                <div class="col-md-6 mb-3 detail-item">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">{{ $label }}:</dt>
                        <dd class="col-sm-7">{{ $value ?: 'N/A' }}</dd>
                    </dl>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('web.parties.edit', ['party' => $party->Id]) }}" class="btn btn-primary" title="Edit this third party">
                <i class="fas fa-edit"></i> Edit
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal" title="Delete this third party">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('web.parties.destroy', ['party' => $party->Id]) }}" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" title="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong>{{ $party->ThirdPartyName ?? 'this third party' }}</strong>? This action is <span class="text-danger fw-bold">irreversible</span>.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Yes, Delete
                </button>
            </div>
        </form>
    </div>
</div>
@endsection