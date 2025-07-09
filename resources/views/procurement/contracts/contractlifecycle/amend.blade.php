@extends('layouts.app')
@section('title', '✍️ Contract Amendment / Termination')

@section('content')
<div class="container mt-4">
    <h4>✍️ Contract Amendment / Termination – CONTRACT/PROC/2025/010</h4>

    <!-- Summary -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5>📦 Supply of Office Furniture</h5>
            <p class="mb-1">Vendor: <strong>OfficePro Ltd</strong></p>
            <p class="mb-1">Period: <strong>2025-07-01 to 2025-12-31</strong></p>
            <p>Status: <span class="badge bg-success">Active</span></p>
        </div>
    </div>

    <!-- Amendment -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">🛠 Contract Amendment</div>
        <div class="card-body">
            <form>
                <div class="mb-3">
                    <label for="amendType" class="form-label">Amendment Type</label>
                    <select class="form-select" id="amendType">
                        <option disabled selected>-- Select Type --</option>
                        <option value="scope">Scope Change</option>
                        <option value="value">Value Change</option>
                        <option value="duration">Duration Extension</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Dynamic Fields -->
                <div class="mb-3 d-none" id="scopeDiv">
                    <label for="scopeDetails" class="form-label">New Scope Details</label>
                    <textarea class="form-control" id="scopeDetails" rows="3" placeholder="Describe scope changes..."></textarea>
                </div>

                <div class="mb-3 d-none" id="valueDiv">
                    <label for="newValue" class="form-label">New Contract Value (KES)</label>
                    <input type="number" class="form-control" id="newValue" placeholder="Enter new value">
                </div>

                <div class="row g-3 d-none" id="dateDiv">
                    <div class="col-md-6">
                        <label for="newStart" class="form-label">New Start Date</label>
                        <input type="date" class="form-control" id="newStart">
                    </div>
                    <div class="col-md-6">
                        <label for="newEnd" class="form-label">New End Date</label>
                        <input type="date" class="form-control" id="newEnd">
                    </div>
                </div>

                <div class="mb-3 d-none" id="otherDiv">
                    <label for="otherDetails" class="form-label">Describe Amendment</label>
                    <textarea class="form-control" id="otherDetails" rows="3" placeholder="Explain the amendment..."></textarea>
                </div>

                <div class="mb-3">
                    <label for="amendLetter" class="form-label">Attach Amendment Letter (PDF)</label>
                    <input type="file" class="form-control" id="amendLetter">
                </div>

                <button class="btn btn-outline-primary">Submit Amendment</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('amendType').addEventListener('change', function () {
        let type = this.value;

        document.getElementById('scopeDiv').classList.add('d-none');
        document.getElementById('valueDiv').classList.add('d-none');
        document.getElementById('dateDiv').classList.add('d-none');
        document.getElementById('otherDiv').classList.add('d-none');

        if (type === 'scope') document.getElementById('scopeDiv').classList.remove('d-none');
        if (type === 'value') document.getElementById('valueDiv').classList.remove('d-none');
        if (type === 'duration') document.getElementById('dateDiv').classList.remove('d-none');
        if (type === 'other') document.getElementById('otherDiv').classList.remove('d-none');
    });
</script>
@endsection
