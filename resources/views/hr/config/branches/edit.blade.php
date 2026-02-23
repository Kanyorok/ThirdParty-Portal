@extends('layouts.app')

@section('title', 'Edit Branch')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Branch</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.branches.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('hr.config.branches.update', $branch->Id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Branch Code *</label>
                        <input type="text" name="BranchID" class="form-control" value="{{ old('BranchID', $branch->BranchID) }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $branch->Name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" name="Address" class="form-control" value="{{ old('Address', $branch->Address) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">City</label>
                        <input type="text" name="City" class="form-control" value="{{ old('City', $branch->City) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Country</label>
                        <input type="text" name="Country" class="form-control" value="{{ old('Country', $branch->Country) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="Phone" class="form-control" value="{{ old('Phone', $branch->Phone) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="Email" class="form-control" value="{{ old('Email', $branch->Email) }}">
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.config.branches.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
