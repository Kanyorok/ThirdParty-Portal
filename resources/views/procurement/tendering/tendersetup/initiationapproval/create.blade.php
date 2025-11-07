@extends('layouts.app')
@section('title', 'Tender Initiation Approval')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Tender Initiation Approval</h4>
    <form>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="tenderRef" class="form-label">Tender Reference</label>
                <select class="form-select" id="tenderRef">
                    <option selected disabled>-- Select Tender --</option>
                    <option>TND/PROC/2025/001 - Supply of Laptops</option>
                    <option>TND/PROC/2025/002 - Office Furniture</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="approvalStatus" class="form-label">Approval Status</label>
                <select class="form-select" id="approvalStatus">
                    <option selected disabled>-- Select Status --</option>
                    <option>Approved</option>
                    <option>Rejected</option>
                    <option>Returned for Clarification</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="approvedBy" class="form-label">Approved By</label>
                <input type="text" class="form-control" id="approvedBy" placeholder="e.g., Procurement Head">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="approvalDate" class="form-label">Approval Date</label>
                <input type="date" class="form-control" id="approvalDate">
            </div>
            <div class="col-md-6 mb-3">
                <label for="approvalRemarks" class="form-label">Remarks</label>
                <textarea class="form-control" id="approvalRemarks" rows="2" placeholder="Add comments or reasons..."></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Submit Approval</button>
        <button type="reset" class="btn btn-secondary">Cancel</button>
    </form>
</div>

@endsection
