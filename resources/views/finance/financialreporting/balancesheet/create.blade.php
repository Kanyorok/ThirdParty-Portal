@extends('layouts.app')
@section('title', 'Balance Sheet')
@section('content')

<div class="container mt-5">
    <h2 class="mb-4">New Balance Sheet Entry</h2>
    <form action="create.php" method="POST" class="border p-4 bg-light rounded">

        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" id="title" class="form-control" required placeholder="e.g., Accounts Receivable">
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select name="category" id="category" class="form-select" required>
                <option value="">-- Select Category --</option>
                <option value="Asset">Asset</option>
                <option value="Liability">Liability</option>
                <option value="Equity">Equity</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required placeholder="e.g., 100000.00">
        </div>

        <div class="mb-3">
            <label for="as_of_date" class="form-label">As of Date</label>
            <input type="date" name="as_of_date" id="as_of_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="">-- Select Status --</option>
                <option value="Active">Active</option>
                <option value="Pending">Pending</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Entry</button>
        </div>
    </form>
</div>

@endsection