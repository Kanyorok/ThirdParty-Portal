@extends('layouts.app')

@section('title', 'Edit Insurance Provider')

@section('content')
<div class="container mt-4" style="max-width: 700px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Edit Insurance Provider</h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('bancassurance.insurers.update', $provider->Id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input name="Name" type="text" class="form-control"
                           value="{{ old('Name', $provider->Name) }}" placeholder="Enter provider name" required>
                </div>

                {{-- Contact Person --}}
                <div class="mb-3">
                    <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                    <input name="ContactPerson" type="text" class="form-control"
                           value="{{ old('ContactPerson', $provider->ContactPerson) }}" placeholder="Enter contact person">
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input name="Email" type="email" class="form-control"
                           value="{{ old('Email', $provider->Email) }}" placeholder="example@email.com">
                </div>

                {{-- Country & Phone on the same row --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Country <span class="text-danger">*</span></label>
                        <select name="Country" class="form-select" required>
                            <option value="">Select Country</option>
                            @foreach($Countrys as $country)
                                <option value="{{ $country->Id }}" {{ old('Country', $provider->Country) == $country->Id ? 'selected' : '' }}>
                                    {{ $country->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input name="Phone" type="text" class="form-control"
                               value="{{ old('Phone', $provider->Phone) }}" placeholder="Enter phone number">
                    </div>
                </div>

                {{-- Active Checkbox --}}
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="isActiveCheck"
                           {{ old('IsActive', $provider->IsActive) ? 'checked' : '' }}>
                    <label class="form-check-label" for="isActiveCheck">
                        Active Provider
                    </label>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('bancassurance.insurers.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
