@extends('layouts.app')
@section('title', isset($branch) ? 'Edit Branch' : 'Create Branch')

@section('content')
<div class="container mt-0">
    <div class="card shadow rounded-4">
        <div class="card-header bg-light py-2 px-3 d-flex align-items-center">
            <h6 class="mb-0 text-muted">
                <i class="fab fa-wpforms text-info"></i>
                {{-- {{ isset($branch) ? 'Edit Branch' : 'Create Branch' }} --}}
            </h6>
        </div>

        <div class="card-body">
            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger rounded-3">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ isset($branch) ? route('finance.bankbranch.update', $branch->BranchID) : route('finance.bankbranch.store') }}"
                  method="POST" novalidate>
                @csrf
                @if(isset($branch)) @method('PUT') @endif

                {{-- Bank Selection --}}
                <div class="card mb-3 border-0 shadow-sm rounded-3">
                    <div class="card-header bg-light py-2 px-3">
                        <h6 class="mb-0 text-primary"><i class="fas fa-university me-1"></i> Bank Info</h6>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bank <span class="text-danger">*</span></label>
                            @if(isset($bank))
                                <input type="hidden" name="BankID" value="{{ $bank->BankID }}">
                                <input class="form-control" value="{{ $bank->BankName }}" disabled>
                            @else
                                <input type="number" name="BankID" class="form-control"
                                       value="{{ old('BankID', $branch->BankID ?? '') }}" required>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                            <input name="BranchName" class="form-control"
                                   value="{{ old('BranchName', $branch->BranchName ?? '') }}" required>
                        </div>
                    </div>
                </div>

                {{-- Branch Details --}}
                <div class="card mb-3 border-0 shadow-sm rounded-3">
                    <div class="card-header bg-light py-2 px-3">
                        <h6 class="mb-0 text-primary"><i class="fas fa-code-branch me-1"></i> Branch Details</h6>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Branch Code</label>
                            <input name="BranchCode" class="form-control"
                                   value="{{ old('BranchCode', $branch->BranchCode ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City ID</label>
                            <input type="number" name="CityID" class="form-control"
                                   value="{{ old('CityID', $branch->CityID ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country ID</label>
                            <input type="number" name="CountryID" class="form-control"
                                   value="{{ old('CountryID', $branch->CountryID ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address 1</label>
                            <input name="Address1" class="form-control"
                                   value="{{ old('Address1', $branch->Address1 ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address 2</label>
                            <input name="Address2" class="form-control"
                                   value="{{ old('Address2', $branch->Address2 ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Zip Code</label>
                            <input name="ZipCode" class="form-control"
                                   value="{{ old('ZipCode', $branch->ZipCode ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input name="Phone" class="form-control"
                                   value="{{ old('Phone', $branch->Phone ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="EmailID" class="form-control"
                                   value="{{ old('EmailID', $branch->EmailID ?? '') }}">
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                {{-- <div class="card mb-3 border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="form-check">
                            <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input"
                                   {{ old('IsActive', $branch->IsActive ?? 1) ? 'checked' : '' }}>
                            <label for="IsActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                </div> --}}

                {{-- Actions --}}
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('finance.bank.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success"
                            onclick="if(this.form.checkValidity()){
                                this.disabled = true;
                                this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Please Wait...';
                                this.form.submit();
                            }">
                        <i class="fas fa-save me-1"></i>
                        Create Branch
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
