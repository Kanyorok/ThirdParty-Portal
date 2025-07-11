@extends('layouts.app')
@section('title', 'Add COA Segment')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">➕ Add New COA Segment</h4>

    <form method="POST" action="{{ route('segment.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Segment Type</label>
            <select name="SegmentType" class="form-select" required>
                <option value="">-- Select Type --</option>
                <option value="AccountType">Account Type</option>
                <option value="SubTypeGroup">Sub Type Group</option>
                <option value="SubAccountType">Sub Account Type</option>
                <option value="BranchCode">Branch Code</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Segment Code</label>
            <input type="text" name="SegmentCode" class="form-control" placeholder="e.g., AST, FA, BLD, 001" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Segment Name</label>
            <input type="text" name="SegmentName" class="form-control" placeholder="e.g., Assets, Fixed Assets" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="IsActive" class="form-select">
                <option value="1" selected>Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Create Segment</button>
        <a href="{{ route('segment.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
