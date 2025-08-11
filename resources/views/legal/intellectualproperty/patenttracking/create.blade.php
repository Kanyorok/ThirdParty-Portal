@extends('layouts.app')
@section('title','Patent Tracking')
@section('content')

<div class="container my-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-primary">Add New Patent</h2>
        <p class="text-muted">Fill in the form to register a new patent</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="save.php">
                <!-- File Number -->
                <div class="mb-3">
                    <label for="fileNumber" class="form-label">File Number</label>
                    <input type="text" class="form-control" id="fileNumber" name="fileNumber" required>
                </div>

                <!-- Jurisdiction -->
                <div class="mb-3">
                    <label for="jurisdiction" class="form-label">Jurisdiction</label>
                    <select class="form-select" id="jurisdiction" name="jurisdiction" required>
                        <option value="" selected disabled>Select jurisdiction</option>
                        <option value="US">United States</option>
                        <option value="EU">European Union</option>
                        <option value="JP">Japan</option>
                        <option value="KE">Kenya</option>
                        <option value="IN">India</option>
                    </select>
                </div>

                <!-- Filing Date -->
                <div class="mb-3">
                    <label for="filingDate" class="form-label">Filing Date</label>
                    <input type="date" class="form-control" id="filingDate" name="filingDate" required>
                </div>

                <!-- Status -->
                <div class="mb-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="" selected disabled>Select status</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Under Review">Under Review</option>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Submit Patent</button>
                    <a href="{{route('patenttracking.index')}} " class="btn btn-secondary">← Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection