@extends('layouts.app')

@section('title', 'Create Committee')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Create New Committee</h4>
                <a href="{{ route('hrms.committees.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
            </div>

            <form action="{{ route('hrms.committees.store') }}" method="POST" autocomplete="off">
                @csrf

                <div class="card-body row">
                    <!-- Committee ID -->
                    <div class="mb-3 col-md-4">
                        <label for="CommitteeID" class="form-label">Committee ID <span class="text-danger">*</span></label>
                        <input type="text" name="CommitteeID" id="CommitteeID" class="form-control @error('CommitteeID') is-invalid @enderror" value="{{ old('CommitteeID') }}" required>
                        @error('CommitteeID')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Committee Name -->
                    <div class="mb-3 col-md-4">
                        <label for="Name" class="form-label">Committee Name <span class="text-danger">*</span></label>
                        <input type="text" name="Name" id="Name" class="form-control @error('Name') is-invalid @enderror" value="{{ old('Name') }}" required>
                        @error('Name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div class="mb-3 col-md-4">
                        <label for="Type" class="form-label">Type <span class="text-danger">*</span></label>
                        <input type="text" name="Type" id="Type" class="form-control @error('Type') is-invalid @enderror" value="{{ old('Type') }}" required>
                        @error('Type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="mb-3 col-12">
                        <label for="Notes" class="form-label">Notes</label>
                        <textarea name="Notes" id="Notes" rows="4" class="form-control @error('Notes') is-invalid @enderror">{{ old('Notes') }}</textarea>
                        @error('Notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Create Committee</button>
                </div>
            </form>
        </div>
    </div>
@endsection
