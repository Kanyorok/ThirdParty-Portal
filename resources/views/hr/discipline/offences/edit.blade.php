@extends('layouts.app')

@section('title', 'Edit Offence')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Offence</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.discipline.offences.index') }}">Back</a>
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
            <form action="{{ route('hr.discipline.offences.update', $offence->Id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code', $offence->Code) }}" required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $offence->Name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="CategoryID" class="form-select">
                            <option value="">Select</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->Id }}" @selected(old('CategoryID', $offence->CategoryID) == $category->Id)>{{ $category->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Severity</label>
                        <select name="Severity" class="form-select">
                            @foreach(['Minor','Major','Gross'] as $severity)
                                <option value="{{ $severity }}" @selected(old('Severity', $offence->Severity) === $severity)>{{ $severity }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Recommended Sanction</label>
                        <select name="RecommendedSanctionID" class="form-select">
                            <option value="">Select</option>
                            @foreach($sanctions as $sanction)
                                <option value="{{ $sanction->Id }}" @selected(old('RecommendedSanctionID', $offence->RecommendedSanctionID) == $sanction->Id)>{{ $sanction->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="HearingRequired" value="1" @checked(old('HearingRequired', $offence->HearingRequired))>
                            <label class="form-check-label">Hearing Required</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="SummaryDismissalAllowed" value="1" @checked(old('SummaryDismissalAllowed', $offence->SummaryDismissalAllowed))>
                            <label class="form-check-label">Summary Dismissal Allowed</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="RequiresEvidence" value="1" @checked(old('RequiresEvidence', $offence->RequiresEvidence))>
                            <label class="form-check-label">Requires Evidence</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="RequiresApproval" value="1" @checked(old('RequiresApproval', $offence->RequiresApproval))>
                            <label class="form-check-label">Requires Approval</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" @checked(old('IsActive', $offence->IsActive))>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.discipline.offences.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Offence</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
