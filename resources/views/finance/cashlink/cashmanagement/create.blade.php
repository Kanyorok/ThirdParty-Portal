@extends('layouts.app')
@section('title', 'Cash Management')
@section('content')


<div class="container mt-5">
    <h2>Add Cash Entry</h2>
    <form action="#" method="post">
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" id="date" name="date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="recipient" class="form-label">Recipient</label>
            <input type="text" id="recipient" name="recipient" class="form-control" placeholder="Enter recipient name" required>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount (KSh)</label>
            <input type="number" id="amount" name="amount" class="form-control" placeholder="Enter amount" required>
        </div>

        <div class="mb-3">
            <label for="purpose" class="form-label">Purpose</label>
            <input type="text" id="purpose" name="purpose" class="form-control" placeholder="Enter purpose" required>
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select id="payment_method" name="payment_method" class="form-select" required>
                <option value="">Select method</option>
                <option value="Cash">Cash</option>
                <option value="Mobile Money">Mobile Money</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea id="remarks" name="remarks" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
        </div>

        <button type="submit" class="btn btn-success">Save Entry</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection