@extends('layouts.app')

@section('title', 'Edit Holiday')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Holiday</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.holidays.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.config.holidays.update', $holiday->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $holiday->Name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Holiday Date *</label>
                        <input type="date" name="HolidayDate" class="form-control" value="{{ old('HolidayDate', \Illuminate\Support\Carbon::parse($holiday->HolidayDate)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Region</label>
                        <input type="text" name="Region" class="form-control" value="{{ old('Region', $holiday->Region) }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsRecurring" value="1" id="IsRecurring" @checked(old('IsRecurring', $holiday->IsRecurring))>
                            <label for="IsRecurring" class="form-check-label">Recurring</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsActive" value="1" id="IsActive" @checked(old('IsActive', $holiday->IsActive))>
                            <label for="IsActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
