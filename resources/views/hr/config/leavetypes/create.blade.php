@extends('layouts.app')

@section('title', 'New Leave Type')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Leave Type</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.leavetypes.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.config.leavetypes.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Annual Entitlement Days *</label>
                        <input type="number" name="AnnualEntitlementDays" class="form-control" value="{{ old('AnnualEntitlementDays', 0) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Allowed Gender</label>
                        <select name="AllowedGender" class="form-select">
                            <option value="">Any</option>
                            @foreach(['Male','Female'] as $g)
                                <option value="{{ $g }}" @selected(old('AllowedGender') == $g)>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="AllowCarryForward" value="1" id="AllowCarryForward" @checked(old('AllowCarryForward'))>
                            <label for="AllowCarryForward" class="form-check-label">Allow Carry Forward</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Carry Forward Days</label>
                        <input type="number" name="MaxCarryForwardDays" class="form-control" value="{{ old('MaxCarryForwardDays') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="RequiresAttachment" value="1" id="RequiresAttachment" @checked(old('RequiresAttachment'))>
                            <label for="RequiresAttachment" class="form-check-label">Requires Attachment</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsPaid" value="1" id="IsPaid" @checked(old('IsPaid', true))>
                            <label for="IsPaid" class="form-check-label">Is Paid</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Eligible Job Grades</label>
                    <div class="row row-cols-2 row-cols-md-3 g-2">
                        @foreach($grades as $grade)
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="GradeIDs[]" value="{{ $grade->Id }}" id="grade_{{ $grade->Id }}" @checked(collect(old('GradeIDs', []))->contains($grade->Id))>
                                    <label class="form-check-label" for="grade_{{ $grade->Id }}">{{ $grade->Name }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Leave Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
