@extends('layouts.app')
@section('title', 'Cash FLow Statement')
@section('content')
 
<div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Create Cash Flow Entry</h4>
        </div>
        <div class="card-body">
            <form action="create.php" method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" name="description" id="description" class="form-control" required placeholder="e.g., Loan Repayment">
                    </div>
                    <div class="col-md-6">
                        <label for="type" class="form-label">Cash Flow Type</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="">-- Select Type --</option>
                            <option value="Operating">Operating</option>
                            <option value="Investing">Investing</option>
                            <option value="Financing">Financing</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="date" class="form-label">Cash Flow Date</label>
                        <input type="date" name="date" id="date" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="">-- Select Status --</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection