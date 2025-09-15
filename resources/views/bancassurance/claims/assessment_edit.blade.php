@extends('layouts.app')
@section('title', 'Edit Claim Assessment')

@section('content')
<div class="container mt-4">
    <form action="{{ route('bancassurance.claims.assessment_update', $assessment->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold">
                Edit Claim Assessment
            </div>
            <div class="card-body">

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Claim ID</label>
                    <div class="col-sm-9">
                        <input type="text" name="ClaimId" class="form-control" 
                               value="{{ old('ClaimId', $assessment->claim->policy->PolicyNumber) }}" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Assessed By</label>
                    <div class="col-sm-9">
                        <input type="text" name="AssessedBy" class="form-control" 
                               value="{{ old('AssessedBy', $assessment->assessedby->Name) }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Assessment Date</label>
                    <div class="col-sm-9">
                        <input type="date" name="AssessmentDate" class="form-control" 
                               value="{{ old('AssessmentDate', \Carbon\Carbon::parse($assessment->AssessmentDate)->format('Y-m-d')) }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Assessment Amount</label>
                    <div class="col-sm-9">
                        <input type="number" step="0.01" name="AssessmentAmount" class="form-control" 
                               value="{{ old('AssessmentAmount', $assessment->AssessmentAmount) }}" required>
                    </div>
                </div>

                {{-- Decision dropdown --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Decision</label>
                    <div class="col-sm-9">
                        <select name="Decision" class="form-select" required>
                            <option value="">-- Select Decision --</option>
                            @foreach($decisions as $decision)
                                <option value="{{ $decision->ID }}" 
                                    {{ old('Decision', $assessment->Decision) == $decision->ID ? 'selected' : '' }}>
                                    {{ $decision->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Assessment Comments</label>
                    <div class="col-sm-9">
                        <textarea name="AssessmentComments" class="form-control" rows="3">{{ old('AssessmentComments', $assessment->AssessmentComments) }}</textarea>
                    </div>
                </div>

            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('bancassurance.claims.assessment_list') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Assessment</button>
            </div>
        </div>
    </form>
</div>
@endsection
