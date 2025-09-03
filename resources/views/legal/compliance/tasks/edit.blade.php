@extends('layouts.app')
@section('title', 'Edit Compliance Task')
@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">Edit Task - {{ $task->TaskDescription }}</div>
        <div class="card-body">
            <form action="{{ route('legal.compliance.tasks.update', [$obligation->Id, $task->Id]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="TaskDescription" class="form-label">Task Description</label>
                    <textarea name="TaskDescription" class="form-control" required>{{ $task->TaskDescription }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="DueDate" class="form-label">Due Date</label>
                    <input type="date" name="DueDate" class="form-control" value="{{ $task->DueDate }}" required>
                </div>
                <div class="mb-3">
                    <label for="ResponsiblePerson" class="form-label">Responsible Person</label>
                    <input type="text" name="ResponsiblePerson" class="form-control" value="{{ $task->ResponsiblePerson }}" required>
                </div>
                <div class="mb-3">
                    <label for="Status" class="form-label">Status</label>
                    <select name="Status" class="form-select">
                        <option value="Pending" {{ $task->Status === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="In Progress" {{ $task->Status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Completed" {{ $task->Status === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Update Task</button>
                <a href="{{ route('legal.compliance.tasks.index', $obligation->Id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
