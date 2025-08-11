@extends('layouts.app')
@section('title', 'New Legal Obligation')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">➕ New Legal Obligation</h4>

    <form action="{{ route('legal.obligations.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="Title" class="form-label">Title</label>
            <input type="text" name="Title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="SourceType" class="form-label">Source Type</label>
            <select name="SourceType" class="form-select">
                <option value="Contract">Contract</option>
                <option value="Case">Case</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="SourceID" class="form-label">Source ID</label>
            <input type="number" name="SourceID" class="form-control">
        </div>

        <div class="mb-3">
            <label for="DueDate" class="form-label">Due Date</label>
            <input type="date" name="DueDate" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Description</label>
            <textarea name="Description" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-success">💾 Save</button>
    </form>
</div>
@endsection
