@extends('layouts.app')
@section('title', 'Period Management')
@section('content')

<div class="container mt-4">
    <h2>Manage Accounting Period</h2>
    <form>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="period_name" class="form-label">Period Name</label>
                <input type="text" class="form-control" id="period_name" name="period_name" value="April 2025" required>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="2025-04-01" required>
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="2025-04-30" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="Open" selected>Open</option>
                <option value="Closed">Closed</option>
                <option value="Locked">Locked</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="remarks" name="remarks" rows="3">Mid-quarter update</textarea>
        </div>

        <button type="button" class="btn btn-primary" onclick="alert('Form not submitted. Static demo only.')">Save Period</button>
    </form>
</div>

@endsection
