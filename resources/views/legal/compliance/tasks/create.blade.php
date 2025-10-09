@extends('layouts.app')
@section('title', 'New Compliance Task')
@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">Add Task for: {{ $obligation->Title }}</div>
        <div class="card-body">
            <form action="{{ route('legal.compliance.tasks.store', $obligation->Id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="TaskDescription" class="form-label">Task Description</label>
                    <textarea name="TaskDescription" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="DueDate" class="form-label">Due Date</label>
                    <input type="date" name="DueDate" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="ResponsiblePerson" class="form-label">Responsible Person</label>
                    <input type="text" name="ResponsiblePerson" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="Status" class="form-label">Status</label>
                    <select name="Status" class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Save Task</button>
                <a href="{{ route('legal.compliance.tasks.index', $obligation->Id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
