@extends('layouts.app')
@section('title', 'Edit Legal Obligation')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">✏️ Edit Legal Obligation</h4>

    <form method="POST" action="{{ route('legal.obligations.update', $obligation->ID) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="ObligationTitle" class="form-label">Obligation Title</label>
            <input type="text" name="ObligationTitle" class="form-control" value="{{ old('ObligationTitle', $obligation->ObligationTitle) }}" required>
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Description</label>
            <textarea name="Description" class="form-control" rows="4" required>{{ old('Description', $obligation->Description) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="DueDate" class="form-label">Due Date</label>
            <input type="date" name="DueDate" class="form-control" value="{{ old('DueDate', \Carbon\Carbon::parse($obligation->DueDate)->format('Y-m-d')) }}" required>
        </div>

        <div class="mb-3">
            <label for="Status" class="form-label">Status</label>
            <select name="Status" class="form-select" required>
                <option value="Pending" {{ old('Status', $obligation->Status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Completed" {{ old('Status', $obligation->Status) == 'Completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">💾 Update Obligation</button>
        <a href="{{ route('legal.obligations.index') }}" class="btn btn-secondary">↩️ Cancel</a>
    </form>
</div>
@endsection
