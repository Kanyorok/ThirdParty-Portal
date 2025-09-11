@extends('layouts.app')
@section('title', 'Add Legal Case')

@section('content')
<div class="container">
    <div class="card p-2 shadow rounded-4 mb-0">
        <div class="card-body mb-0">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <p class="text-muted">Fill out the form below to register a new legal case in the system.</p>
            <form action="{{ route('legal.cases.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Case Title</label>
                        <input type="text" name="CaseTitle" value="{{ old('CaseTitle', $case->CaseTitle ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Case Number</label>
                        <input type="text" name="CaseNumber" value="{{ old('CaseNumber', $case->CaseNumber ?? '') }}" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Court Name</label>
                        <input type="text" name="CourtName" value="{{ old('CourtName', $case->CourtName ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Filing Date</label>
                        <input type="date" name="FilingDate" value="{{ old('FilingDate', $case->FilingDate ?? '') }}" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Opposing Party</label>
                        <input type="text" name="OpposingParty" value="{{ old('OpposingParty', $case->OpposingParty ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Case Type</label>
                        <select class="form-select" name="CaseType" id="CaseType" required>
                            <option value="" disabled selected>-- Select Case Type --</option>
                            @foreach($caseTypes as $type)
                                <option value="{{ $type->Value }}">{{ $type->Value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

{{--                <div class="mb-3">--}}
{{--                    <label class="form-label">DMS Document ID</label>--}}
{{--                    <input type="text" name="DMSDocID" value="{{ old('DMSDocID', $case->DMSDocID ?? '') }}" class="form-control">--}}
{{--                </div>--}}

                <div class="mb-3">
                    <label class="form-label">Summary</label>
                    <textarea name="Summary" class="form-control" rows="3" required>{{ old('Summary', $case->Summary ?? '') }}</textarea>
                </div>
                <div class="d-flex justify-content-end gap-2 mb-3">
                    <a href="{{ route('legal.cases.index') }}" class="btn btn-outline-secondary"><i class="fas fa-long-arrow-alt-left"></i> Back</a>
                    <button type="submit" class="btn btn-info" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}"><i class="fas fa-save"></i> Save Case</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
