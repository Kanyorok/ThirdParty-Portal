@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card shadow rounded-4 border-0">
            <!-- Header -->
            <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center rounded-top-4">
                <h4 class="mb-0 text-primary">
                    <i class="fas fa-university me-2"></i> Bank Details
                </h4>
                <button class="btn btn-sm btn-outline-dark" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>

            <!-- Body -->
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase mb-2">General Info</h6>
                        <dl class="row mb-0">
                            <dt class="col-5 fw-semibold">Bank Name</dt>
                            <dd class="col-7">{{ $bank->BankName }}</dd>

                            <dt class="col-5 fw-semibold">Short Name</dt>
                            <dd class="col-7">{{ $bank->ShortName ?? '—' }}</dd>

                            <dt class="col-5 fw-semibold">Bank Code</dt>
                            <dd class="col-7">{{ $bank->BankCode ?? '—' }}</dd>

                            <dt class="col-5 fw-semibold">SWIFT Code</dt>
                            <dd class="col-7">{{ $bank->SwiftCode ?? '—' }}</dd>

                            <dt class="col-5 fw-semibold">Clearing Code</dt>
                            <dd class="col-7">{{ $bank->ClearingCode ?? '—' }}</dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase mb-2">Contact Info</h6>
                        <dl class="row mb-0">
                            <dt class="col-5 fw-semibold">Country</dt>
                            <dd class="col-7">{{ $bank->country?->Name ?? '—' }}</dd>

                            <dt class="col-5 fw-semibold">Email</dt>
                            <dd class="col-7">{{ $bank->EmailID ?? '—' }}</dd>

                            <dt class="col-5 fw-semibold">Phone</dt>
                            <dd class="col-7">{{ $bank->Phone ?? '—' }}</dd>

                            <dt class="col-5 fw-semibold">Website</dt>
                            <dd class="col-7">
                                @if($bank->Website)
                                    <a href="{{ $bank->Website }}" target="_blank">{{ $bank->Website }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase mb-2">Audit Info</h6>
                        <dl class="row mb-0">
                            <dt class="col-5 fw-semibold">Created On</dt>
                            <dd class="col-7">{{ $bank->CreatedOn }}</dd>

                            <dt class="col-5 fw-semibold">Created By</dt>
                            <dd class="col-7">{{ $bank->createdByUser?->Name ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="card-footer bg-light border-0 d-flex justify-content-end gap-2 rounded-bottom-4">
                <a href="{{ route('finance.bank.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <a href="{{ route('finance.bank.edit', $bank->BankID) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
        </div>
    </div>
@endsection
