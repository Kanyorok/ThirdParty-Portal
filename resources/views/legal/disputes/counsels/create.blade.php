@extends('layouts.app')
@section('title', 'Assign Legal Counsel')

@section('content')
<div class="container">
    <div class="card p-2 shadow rounded-4 mb-0">
        <div class="card-body mb-0">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <p class="text-muted">
                Fill out the form below to assign a new legal counsel to the case:
                <strong>{{ $case->CaseTitle }}</strong>.
            </p>

            <form method="POST" action="{{ route('legal.disputes.counsels.store', $case->Id) }}">
                @csrf

                <div class="row mb-3">
                    <input type="hidden" name="LegalCaseID" value="{{ $case->Id }}">
                    <div class="col-md-6">
                        <label class="form-label">Counsel Name</label>
                        <input type="text" name="CounselName"
                               value="{{ old('CounselName') }}"
                               class="form-control"
                               placeholder="Enter full name of counsel" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Law Firm</label>
                        <input type="text" name="FirmName"
                               value="{{ old('FirmName') }}"
                               class="form-control"
                               placeholder="e.g. XYZ & Co. Advocates" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="Email"
                               value="{{ old('Email') }}"
                               class="form-control"
                               placeholder="example@lawfirm.com" required>
                    </div>
                    <div class="col-md-6">
                   <label class="form-label">Phone</label>
                   <input type="tel" name="Phone"
                       value="{{ old('Phone') }}"
                       class="form-control"
                       placeholder="e.g., +12025550123"
                       required
                       pattern="^\+[1-9]\d{7,14}$"
                       title="Use international format (E.164), e.g., +12025550123">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Role in Case</label>
                    <input type="text" name="Role"
                           value="{{ old('Role') }}"
                           class="form-control"
                           placeholder="e.g. Lead Counsel, Assistant Counsel" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="Remarks" class="form-control" rows="2" placeholder="Additional notes or remarks" required>{{ old('Remarks') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3">
                    <a href="{{ route('legal.disputes.counsels.index', $case->Id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-long-arrow-alt-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-info"
                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">
                        <i class="fas fa-save"></i> Save Counsel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
