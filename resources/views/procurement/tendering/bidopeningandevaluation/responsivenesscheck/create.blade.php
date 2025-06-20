@extends('layouts.app')
@section('title', 'Bid Responsiveness Review')
@section('content')
<div class="container mt-4">
    <h4>📋 Bid Responsiveness Review</h4>
    <form>
        <div class="mb-3">
            <label class="form-label">Bidder</label>
            <input type="text" class="form-control" value="CompTech Solutions Ltd" readonly>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Was Submission Timely?</label>
                <select class="form-select">
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mandatory Documents Complete?</label>
                <select class="form-select">
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Meets Eligibility Criteria?</label>
                <select class="form-select">
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" rows="3" placeholder="E.g. Missing tax clearance certificate"></textarea>
        </div>
        <div class="mt-3 text-end">
            <button class="btn btn-primary">Mark as Responsive</button>
            <button class="btn btn-danger">Mark as Non-Responsive</button>
        </div>
    </form>
</div>

@endsection