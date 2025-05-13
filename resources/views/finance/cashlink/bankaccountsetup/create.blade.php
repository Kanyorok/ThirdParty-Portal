@extends('layouts.app')
@section('title', 'Account Setup')
@section('content')

<div class="container">
    <h1 class="my-4">Create a New Bank Account</h1>

    <!-- Create Account Form (Optional - No POST method used) -->
    <form action="create.php" method="get"> <!-- Using GET instead of POST -->
        <div class="mb-3">
            <label for="accountHolder" class="form-label">Account Holder Name</label>
            <input type="text" class="form-control" id="accountHolder" name="accountHolder">
        </div>
        <div class="mb-3">
            <label for="accountNumber" class="form-label">Account Number</label>
            <input type="text" class="form-control" id="accountNumber" name="accountNumber">
        </div>
        <div class="mb-3">
            <label for="accountType" class="form-label">Account Type</label>
            <select class="form-select" id="accountType" name="accountType">
                <option value="Savings">Savings</option>
                <option value="Checking">Checking</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Account Status</label>
            <select class="form-select" id="status" name="status">
                <option value="Active">Active</option>
                <option value="Pending">Pending</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create Account</button>
    </form>

    <!-- Back to List Link -->
    <a href="{{route('bankaccountsetup.index')}} " class="btn btn-secondary mt-3">Back to Bank Account List</a>
</div>

@endsection