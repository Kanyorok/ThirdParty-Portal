@extends('layouts.app')
@section('title', 'Register Insurance Provider')
@section('content')

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please fix the following issues:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

<div class="container mt-5" style="max-width: 800px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4 py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-building me-2"></i> Insurance Provider Information
            </h5>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.insurers.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Provider Details Section --}}
                <div class="mb-4">
                    <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                        <i class="bi bi-info-circle me-2"></i>Provider Details
                    </h6>

                    {{-- Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Provider Name <span class="text-danger">*</span></label>
                        <input name="Name" type="text" class="form-control rounded-pill shadow-sm" 
                            placeholder="Enter insurance provider name" required>
                    </div>

                    {{-- Contact Person --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contact Person <span class="text-danger">*</span></label>
                        <input name="ContactPerson" type="text" class="form-control rounded-pill shadow-sm" 
                            placeholder="Enter primary contact person name">
                    </div>
                </div>

                {{-- Contact Information Section --}}
                <div class="mb-4">
                    <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                        <i class="bi bi-telephone me-2"></i>Contact Information
                    </h6>

                    <div class="row g-3">
                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm rounded-pill">
                                <span class="input-group-text bg-light border-0 rounded-start-pill">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input name="Email" type="email" class="form-control border-0 rounded-end-pill" 
                                    placeholder="example@provider.com">
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm rounded-pill">
                                <span class="input-group-text bg-light border-0 rounded-start-pill">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input name="Phone" type="text" class="form-control border-0 rounded-end-pill" 
                                    placeholder="Enter phone number">
                            </div>
                        </div>
                    </div>

                    {{-- Country --}}
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Country <span class="text-danger">*</span></label>
                        <select name="Country" class="form-select rounded-pill shadow-sm" required>
                            <option value="" disabled selected>-- Select Country --</option>
                            @foreach($Countrys as $country)
                                <option value="{{ $country->Id }}">{{ $country->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Status Section --}}
                <div class="mb-4">
                    <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                        <i class="bi bi-toggles me-2"></i>Provider Status
                    </h6>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="IsActive" value="1" 
                            id="isActiveCheck" checked style="cursor: pointer;">
                        <label class="form-check-label fw-semibold" for="isActiveCheck" style="cursor: pointer;">
                            Active Provider
                            <small class="text-muted d-block">Enable this provider to be available for selection</small>
                        </label>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">
                    <a href="{{ route('bancassurance.insurers.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-check-circle me-2"></i>Save Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
