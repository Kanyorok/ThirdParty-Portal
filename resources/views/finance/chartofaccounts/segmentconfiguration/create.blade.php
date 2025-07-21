@extends('layouts.app')
@section('title', 'Add COA Segment')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">➕ Add New Segment Type</h4>

        <form method="POST" action="#">
            <div class="mb-3">
                <label class="form-label">Segment Code</label>
                <input type="text" name="SegmentCode" class="form-control" placeholder="e.g., BRANCH" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Segment Name</label>
                <input type="text" name="SegmentName" class="form-control" placeholder="e.g., Branch" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="IsActive" class="form-select">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Create Segment</button>
            <a href="/finance/segment-configuration" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
