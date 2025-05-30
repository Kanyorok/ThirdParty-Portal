@extends('layouts.app')
@section('title', 'Product Type Setup')
@section('content')
    <div class="card p-4">
        <h5>🧩 Product Type Setup</h5>
        <div class="mb-3">
            <label for="typeName" class="form-label">Type Name</label>
            <input type="text" class="form-control" id="typeName" placeholder="e.g., Loan, Deposit, Fee">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" rows="3" placeholder="Optional description..."></textarea>
        </div>
        <button class="btn btn-primary">💾 Save</button>
        <button class="btn btn-secondary">🔄 Reset</button>
    </div>
@endsection
