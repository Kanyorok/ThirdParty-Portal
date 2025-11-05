@extends('layouts.app')

@section('title', 'View Third Party - ' . ($party->ThirdPartyName ?? 'N/A'))

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card border-0 rounded-0">

                {{-- HEADER --}}
                <div class="card-header bg-light p-5 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="card-title h3 fw-bold mb-1">
                            Third Party Profile
                        </h1>
                        <p class="text-muted mb-0">Review details for <strong>{{ $party->ThirdPartyName ?? 'N/A' }}</strong>.</p>
                    </div>
                    <a href="{{ route('thirdparty.parties.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>

                {{-- COMPANY INFORMATION --}}
                <div class="bg-light p-5 mb-5 border rounded-3">
                    <h2 class="h5 fw-bold mb-3">Company Information</h2>
                    <p class="text-muted mb-4">General company registration and identification details.</p>
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Company Name</p>
                            <p class="fw-bold">{{ $party->ThirdPartyName ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Trading Name</p>
                            <p class="fw-bold">{{ $party->TradingName ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Business Type</p>
                            <p class="fw-bold">{{ $party->BusinessType?->label() ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Registration Number</p>
                            <p class="fw-bold">{{ $party->RegistrationNumber ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Tax PIN</p>
                            <p class="fw-bold">{{ $party->TaxPIN ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">VAT Number</p>
                            <p class="fw-bold">{{ $party->VATNumber ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                {{-- CONTACT DETAILS --}}
                <div class="bg-light p-5 mb-5 border rounded-3">
                    <h2 class="h5 fw-bold mb-3">Contact Details</h2>
                    <p class="text-muted mb-4">Registered location and official contact details.</p>
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Country</p>
                            <p class="fw-bold">{{ $party->Country ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Email</p>
                            <p class="fw-bold">{{ $party->Email ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Phone</p>
                            <p class="fw-bold">{{ $party->Phone ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Website</p>
                            @if ($party->Website)
                                <a href="{{ $party->Website }}" target="_blank" class="fw-bold text-primary text-decoration-none">
                                    {{ $party->Website }} <i class="fas fa-external-link-alt ms-1"></i>
                                </a>
                            @else
                                <p class="fw-bold">N/A</p>
                            @endif
                        </div>

                        <div class="col-12">
                            <p class="text-muted mb-1 fw-semibold">Physical Address</p>
                            <p class="fw-bold">{{ $party->PhysicalAddress ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                {{-- PRIMARY USER --}}
                <div class="bg-light p-5 mb-5 border rounded-3">
                    <h2 class="h5 fw-bold mb-3">Primary User</h2>
                    <p class="text-muted mb-4">Main contact person assigned to this third party.</p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <p class="text-muted mb-1 fw-semibold">Full Name</p>
                            <p class="fw-bold">{{ $primaryUser?->FullName ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted mb-1 fw-semibold">Email</p>
                            <p class="fw-bold">{{ $primaryUser?->Email ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                {{-- STATUS & CLASSIFICATION --}}
                <div class="bg-light p-5 mb-5 border rounded-3">
                    <h2 class="h5 fw-bold mb-3">Status & Classification</h2>
                    <p class="text-muted mb-4">Approval status and operational details of this third party.</p>
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Approval Status</p>
                            <span class="badge {{ strtolower($party->ApprovalStatus?->label()) === 'approved' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $party->ApprovalStatus?->label() ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Operational Status</p>
                            <span class="badge {{ strtolower($party->Status?->label()) === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $party->Status?->label() ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Is Prequalified</p>
                            <span class="badge {{ $party->IsPrequalified ? 'bg-success' : 'bg-danger' }}">
                                {{ $party->IsPrequalified ? 'Yes' : 'No' }}
                            </span>
                        </div>

                        <div class="col-md-12">
                            <p class="text-muted mb-1 fw-semibold">Third Party Types</p>
                            <p class="fw-bold">
                                {{ ($party->types && $party->types->count()) 
                                    ? $party->types->pluck('Code')->filter()->unique()->join(', ') 
                                    : ($party->ThirdPartyType?->label() ?? 'N/A') }}
                            </p>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Created On</p>
                            <p class="fw-bold">{{ $party->CreatedOn?->format('d/m/Y') ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <p class="text-muted mb-1 fw-semibold">Modified On</p>
                            <p class="fw-bold">{{ $party->ModifiedOn?->format('d/m/Y') ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="card-footer bg-light p-5 border-top d-flex flex-column flex-sm-row-reverse gap-3">
                    <a href="{{ route('thirdparty.parties.edit', ['party' => $party->Id]) }}" class="btn btn-primary rounded-pill px-5 py-3 flex-grow-1 flex-sm-grow-0">
                        <i class="fas fa-edit me-2"></i> Review / Edit
                    </a>
                    <button type="button" class="btn btn-danger rounded-pill px-5 py-3 flex-grow-1 flex-sm-grow-0" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal">
                        <i class="fas fa-trash-alt me-2"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('thirdparty.parties.destroy', ['party' => $party->Id]) }}" class="modal-content border-0 rounded-4 shadow">
            @csrf
            @method('DELETE')
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title text-danger fw-bold" id="deleteConfirmationModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-3">
                    Are you sure you want to delete 
                    <strong class="text-dark">{{ $party->ThirdPartyName ?? 'this third party' }}</strong>?
                </p>
                <p class="text-muted small mb-0">
                    This action is <span class="text-danger fw-bold">irreversible</span>.
                </p>
            </div>
            <div class="modal-footer border-0 d-flex justify-content-center gap-3 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i> Cancel
                </button>
                <button type="submit" class="btn btn-danger rounded-pill px-4">
                    <i class="fas fa-trash-alt me-2"></i> Yes, Delete
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
