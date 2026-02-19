@extends('layouts.app')

@section('title', 'Edit Exit Type')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Exit Type</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.exit-types.index') }}">Back</a>
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
            <form action="{{ route('hr.config.exit-types.update', $type->Id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code', $type->Code) }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $type->Name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description', $type->Description) }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsEmployerInitiated" value="1" @checked(old('IsEmployerInitiated', $type->IsEmployerInitiated))>
                            <label class="form-check-label">Employer Initiated</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="RequiresCase" value="1" @checked(old('RequiresCase', $type->RequiresCase))>
                            <label class="form-check-label">Requires Case</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="RequiresHearing" value="1" @checked(old('RequiresHearing', $type->RequiresHearing))>
                            <label class="form-check-label">Requires Hearing</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsRedundancy" value="1" @checked(old('IsRedundancy', $type->IsRedundancy))>
                            <label class="form-check-label">Redundancy</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsSummaryDismissal" value="1" @checked(old('IsSummaryDismissal', $type->IsSummaryDismissal))>
                            <label class="form-check-label">Summary Dismissal</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" @checked(old('IsActive', $type->IsActive))>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.config.exit-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Exit Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
