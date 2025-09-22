@extends('layouts.app')
@section('title', 'Edit Insurance Provider')
@section('content')
    <div class="container mt-4">

        <form method="POST" action="{{ route('bancassurance.insurers.update', $provider->Id) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input name="Name" type="text" class="form-control" value="{{ old('Name', $provider->Name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Country <span class="text-danger">*</span></label>
                <input name="Country" type="text" class="form-control" value="{{ old('Country', $provider->Country) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                <input name="ContactPerson" type="text" class="form-control"
                       value="{{ old('ContactPerson', $provider->ContactPerson) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input name="Email" type="email" class="form-control" value="{{ old('Email', $provider->Email) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Phone <span class="text-danger">*</span></label>
                <input name="Phone" type="text" class="form-control" value="{{ old('Phone', $provider->Phone) }}">
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck"
                    {{ old('IsActive', $provider->IsActive) ? 'checked' : '' }}>
                <label class="form-check-label" for="primaryCheck">Is Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Update Provider</button>
        </form>
    </div>
@endsection
