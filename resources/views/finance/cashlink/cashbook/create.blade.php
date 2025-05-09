@extends('layouts.app')
@section('title', 'Cash Book')
@section('content')


<div class="container form-container">
    <h2 class="mb-4">Add New Cash Book Entry</h2>
    <form action="submit.php" method="post">
        <div class="mb-3">
            <label for="date" class="form-label">Date:</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <input type="text" class="form-control" id="description" name="description" placeholder="Enter description" required>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount:</label>
            <input type="number" class="form-control" id="amount" name="amount" placeholder="Enter amount" step="0.01" required>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type:</label>
            <select class="form-select" id="type" name="type" required>
                <option value="">-- Select type --</option>
                <option value="Credit">Credit</option>
                <option value="Debit">Debit</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Save Entry</button>
    </form>
</div>


@endsection