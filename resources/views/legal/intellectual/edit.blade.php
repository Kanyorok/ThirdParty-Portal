@extends('layouts.app')
@section('title', 'Edit Intellectual Property')

@section('content')
<div class="card shadow p-1 rounded-4">
    <div class="card-body">
        <p class="text-muted">
            Update the details of the Intellectual Property (IP) record below.
            Modify the necessary fields and save changes to ensure accurate documentation.
        </p>
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <form method="POST" action="{{ route('legal.intellectual.update', $record->Id) }}">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">IP Type</label>
                    <select class="form-select" name="IPType" id="IPType" required>
                        <option disabled value="">-- Select IP Type --</option>
                        @foreach($details as $item)
                        <option value="{{ $item->Value }}"
                            {{ $record->IPType == $item->Value ? 'selected' : '' }}>
                            {{ $item->Value }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="Title" class="form-control"
                        value="{{ $record->Title }}" placeholder="Enter the IP title" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Owner</label>
                    <input type="text" name="Owner" class="form-control"
                        value="{{ $record->Owner }}" placeholder="Name of the owner or organization" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="RegistrationNumber" class="form-control"
                        value="{{ $record->RegistrationNumber }}" placeholder="Unique registration number" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Registration Date</label>
                    <input type="date" name="RegistrationDate" class="form-control"
                        value="{{ $record->RegistrationDate ? \Carbon\Carbon::parse($record->RegistrationDate)->format('Y-m-d') : '' }}"
                        required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="ExpiryDate" class="form-control"
                        value="{{ $record->ExpiryDate ? \Carbon\Carbon::parse($record->ExpiryDate)->format('Y-m-d') : '' }}"
                        required>
                </div>
            </div>

            <div class="mb-3">
                <label for="Status" class="form-label">Status</label>
                <select name="Status" id="Status" class="form-select">{{ $record->Status}}
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>


            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea name="Remarks" class="form-control" rows="3" placeholder="Additional notes or details"
                    required>{{ $record->Remarks }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-3">
                <a href="{{ route('legal.intellectual.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-long-arrow-alt-left"></i> Back
                </a>
                <button type="submit" class="btn btn-info"
                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit();}">
                    <i class="fas fa-save"></i> Update IP
                </button>
            </div>
        </form>
    </div>
</div>
@endsection