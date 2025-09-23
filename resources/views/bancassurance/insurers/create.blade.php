@extends('layouts.app')

@section('title', 'Register Insurance Provider')

@section('content')
<div class="container mt-4" style="max-width: 700px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Register Insurance Provider</h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('bancassurance.insurers.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Name --}}
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input name="Name" type="text" class="form-control" placeholder="Enter provider name" required>
                </div>

                {{-- Country --}}
                <div class="mb-3">
                    <label class="form-label">Country <span class="text-danger">*</span></label>
                    <input name="Country" type="text" class="form-control" placeholder="Enter country">
                </div>

                {{-- Contact Person --}}
                <div class="mb-3">
                    <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                    <input name="ContactPerson" type="text" class="form-control" placeholder="Enter contact person">
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input name="Email" type="email" class="form-control" placeholder="example@email.com">
                </div>

                {{-- Phone --}}
                <div class="mb-3">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input name="Phone" type="text" class="form-control" placeholder="Enter phone number">
                </div>

                {{-- Active Checkbox --}}
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="isActiveCheck">
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
                        <i class="bi bi-save"></i> Save Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
