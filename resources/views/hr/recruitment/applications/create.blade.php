@extends('layouts.app')

@section('title', 'New Application')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Application</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.applications.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.recruitment.applications.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Job Opening *</label>
                        <select name="JobOpeningID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($openings as $job)
                                <option value="{{ $job->Id }}" @selected(old('JobOpeningID', $opening?->Id) == $job->Id)>{{ $job->Title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="FirstName" class="form-control" value="{{ old('FirstName') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="LastName" class="form-control" value="{{ old('LastName') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Other Names</label>
                        <input type="text" name="OtherNames" class="form-control" value="{{ old('OtherNames') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="Email" class="form-control" value="{{ old('Email') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="Phone" class="form-control" value="{{ old('Phone') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Gender</label>
                        <select name="Gender" class="form-select">
                            <option value="">Select</option>
                            @foreach(['Male','Female','Other'] as $gender)
                                <option value="{{ $gender }}" @selected(old('Gender') === $gender)>{{ $gender }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="DateOfBirth" class="form-control" value="{{ old('DateOfBirth') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" name="Address" class="form-control" value="{{ old('Address') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Source</label>
                        <input type="text" name="Source" class="form-control" value="{{ old('Source') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Expected Salary</label>
                        <input type="number" step="0.01" name="ExpectedSalary" class="form-control" value="{{ old('ExpectedSalary') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Notice Period (days)</label>
                        <input type="number" name="NoticePeriodDays" class="form-control" value="{{ old('NoticePeriodDays') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <textarea name="Notes" class="form-control" rows="3">{{ old('Notes') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Resume</label>
                        <input type="file" name="Resume" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cover Letter</label>
                        <input type="file" name="CoverLetter" class="form-control">
                    </div>
                </div>

                <div class="mt-4">
                    <h6>Supporting Documents</h6>
                    <div class="row g-2">
                        @for($i = 0; $i < 2; $i++)
                            <div class="col-md-4">
                                <input type="file" name="documents[]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="documents_category[]" class="form-control" placeholder="Category">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="documents_description[]" class="form-control" placeholder="Description">
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
