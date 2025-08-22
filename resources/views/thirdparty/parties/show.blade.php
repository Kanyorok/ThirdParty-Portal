@extends('layouts.app')

@section('title', 'View Third Party - ' . ($party->ThirdPartyName ?? 'N/A'))

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            <div class="card border-0 rounded-4">

                <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="card-title h3 fw-bold mb-1">
                            Third Party Profile: {{ $party->ThirdPartyName ?? 'N/A' }}
                        </h1>
                        <p class="text-muted mb-0">Detailed information about this third-party company.</p>
                    </div>
                    <a href="{{ route('thirdparty.parties.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4">
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
                        'Operational Status' => $party->Status?->value,
                        'Third Party Type' => $party->ThirdPartyType?->label(),
                        'Is Prequalified' => $party->IsPrequalified,
                        'Created On' => $party->CreatedOn?->format('Y-m-d H:i:s'),
                        'Modified On' => $party->ModifiedOn?->format('Y-m-d H:i:s'),
                        ];
                        @endphp

                        @foreach ($details as $label => $value)
                        <div class="col-md-6">
                            <p class="text-muted mb-1 fw-semibold">{{ $label }}</p>
                            <h6 class="mb-0 text-dark">
                                @if ($label === 'Website' && $value)
                                <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="text-primary">{{ $value }} <i class="fas fa-external-link-alt ms-1"></i></a>
                                @elseif ($label === 'Is Prequalified')
                                <span class="badge {{ $value ? 'bg-success' : 'bg-danger' }}">
                                    {{ $value ? 'Yes' : 'No' }}
                                </span>
                                @elseif ($label === 'Operational Status')
                                <span class="badge {{ $value === 'Active' ? 'bg-success' : 'bg-warning' }}">
                                    {{ $value ?: 'N/A' }}
                                </span>
                                @else
                                {{ $value ?: 'N/A' }}
                                @endif
                            </h6>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-footer bg-light p-4 border-top d-flex justify-content-end gap-3">
                    <a href="{{ route('thirdparty.parties.edit', ['party' => $party->Id]) }}" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-edit me-2"></i> Review
                    </a>
                    <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal">
                        <i class="fas fa-trash-alt me-2"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('thirdparty.parties.destroy', ['party' => $party->Id]) }}" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong>{{ $party->ThirdPartyName ?? 'this third party' }}</strong>? This action is <span class="text-danger fw-bold">irreversible</span>.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger rounded-pill">
                    <i class="fas fa-trash-alt me-2"></i> Yes, Delete
                </button>
            </div>
        </form>
    </div>
</div>
@endsection