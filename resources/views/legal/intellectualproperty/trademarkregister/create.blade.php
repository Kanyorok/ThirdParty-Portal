@extends('layouts.app')
@section('title','Trademark Register')
@section('content')

<div class="container my-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-success">Add New Trademark</h2>
        <p class="text-muted">Enter trademark details below</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="save.php">
                <!-- Trademark Name -->
                <div class="mb-3">
                    <label for="trademark" class="form-label">Trademark Name</label>
                    <input type="text" class="form-control" id="trademark" name="trademark" required>
                </div>

                <!-- Company Name -->
                <div class="mb-3">
                    <label for="company" class="form-label">Company Name</label>
                    <input type="text" class="form-control" id="company" name="company" required>
                </div>

                <!-- Application Date -->
                <div class="mb-3">
                    <label for="appDate" class="form-label">Application Date</label>
                    <input type="date" class="form-control" id="appDate" name="appDate" required>
                </div>

                <!-- Registration Date -->
                <div class="mb-3">
                    <label for="regDate" class="form-label">Registration Date</label>
                    <input type="date" class="form-control" id="regDate" name="regDate" required>
                </div>

                <!-- Renewal Due Date -->
                <div class="mb-3">
                    <label for="renewDate" class="form-label">Renewal Due Date</label>
                    <input type="date" class="form-control" id="renewDate" name="renewDate" required>
                </div>

                <!-- Registration Status -->
                <div class="mb-4">
                    <label for="status" class="form-label">Registration Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="" selected disabled>Select status</option>
                        <option value="Registered">Registered</option>
                        <option value="Pending">Pending</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">Submit Trademark</button>
                    <a href="{{route('trademarkregister.index')}} " class="btn btn-secondary">← Back to Register</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection