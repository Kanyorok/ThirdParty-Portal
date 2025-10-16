@extends('layouts.app')
@section('title', 'Claim Assessment Details')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold">
                Claim Assessment Details
            </div>
            <div class="card-body">
                <form>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Claim ID</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   value="{{ $assessment->ClaimId ?? '-' }} _ {{$assessment->claim->policy->PolicyNumber ?? '-'}}"
                                   readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Assessed By</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" value="{{ $assessment->assessedby->Name }}"
                                   readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Assessment Date</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   value="{{ \Carbon\Carbon::parse($assessment->AssessmentDate)->format('d/m/Y') }}"
                                   readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Assessment Amount</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                   value="{{ number_format($assessment->AssessmentAmount, 2) }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Decision</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" value="{{ $assessment->Decision }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Assessment Comments</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" rows="3"
                                      readonly>{{ $assessment->AssessmentComments ?? 'N/A' }}</textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Created By</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" value="{{ $assessment->CreatedBy }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Modified By</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" value="{{ $assessment->ModifiedBy }}" readonly>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('bancassurance.claims.index') }}" class="btn btn-secondary">Back to List</a>
                <a href="#" class="btn btn-primary">Edit</a>
            </div>
        </div>
    </div>
@endsection
