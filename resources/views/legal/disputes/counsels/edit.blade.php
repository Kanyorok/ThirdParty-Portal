@extends('layouts.app')
@section('title', 'Edit Counsel')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm border rounded-4 overflow-hidden">

            {{-- Header --}}
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                <h5 class="text-info mb-0">
                    <i class="fas fa-user-edit"></i> Edit Counsel: <span
                        class="fw-semibold">{{ $counsel->CounselName }}</span>
                </h5>
                <a href="{{ route('legal.disputes.counsels.index', $case->Id) }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            {{-- Body --}}
            <div class="card-body bg-white">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <p class="text-muted">
                    Use this form to update counsel information associated with the case. Ensure the counsel’s name,
                    firm, contact details, and role are correctly recorded for proper case management.
                </p>
                <form method="POST" action="{{ route('legal.disputes.counsels.update', [$case->Id, $counsel->Id]) }}"
                      class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Counsel Name</label>
                        <input type="text" name="CounselName" value="{{ $counsel->CounselName }}"
                               class="form-control shadow-sm" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Law Firm</label>
                        <input type="text" name="FirmName" value="{{ $counsel->FirmName }}"
                               class="form-control shadow-sm" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="Email" value="{{ $counsel->Email }}" class="form-control shadow-sm"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="tel" name="Phone"
                               value="{{ old('Phone') }}"
                               class="form-control"
                               placeholder="+254 700 000000"
                               required
                               pattern="^\+254\s?[17]\d{2}\s?\d{6}$"
                               title="Enter a valid Kenyan phone number e.g. +254 712 345678">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Role</label>
                        <input type="text" name="Role" value="{{ $counsel->Role }}" class="form-control shadow-sm"
                               required>
                    </div>

                    {{-- Footer buttons --}}
                    <div class="d-flex justify-content-end gap-2 mb-3">
                        <a href="{{ route('legal.disputes.counsels.index', $case->Id) }}"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-long-arrow-alt-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-info"
                                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit();}">
                            <i class="fas fa-save"></i> Update Counsel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
