@extends('layouts.app')
@section('title', 'Appoint Tender Committee')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📋 Appoint Tender Committee</h4>
    <form>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="tenderRef" class="form-label">Tender Reference</label>
                <select class="form-select" id="tenderRef">
                    <option selected disabled>-- Select Tender --</option>
                    <option>TND/PROC/2025/001</option>
                    <option>TND/PROC/2025/002</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="appointmentDate" class="form-label">Appointment Date</label>
                <input type="date" class="form-control" id="appointmentDate">
            </div>
        </div>

        <div class="mb-3">
            <label for="committeeMembers" class="form-label">Select Committee Members</label>
            <select class="form-select" id="committeeMembers" multiple>
                <!-- Populate from system user list -->
                <option>Moses K. – Procurement Manager</option>
                <option>Grace A. – Finance Officer</option>
                <option>John O. – Technical Expert</option>
                <option>Linda M. – Legal Counsel</option>
            </select>
            <small class="form-text text-muted">Hold CTRL/CMD to select multiple users.</small>
        </div>

        <button type="submit" class="btn btn-primary">Appoint Committee</button>
    </form>
</div>

@endsection
