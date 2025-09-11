@extends('layouts.app')
@section('title', 'Policy Categories')

@section('content')

    <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center mb-3">
    <h4 class="text-info mb-0"> </h4>
    <a href="{{ route('legal.setup.policy_categories.create') }}" class="btn btn-primary mb-3">➕ Add Policy Category</a>
  </div>
<div class="card shadow rounded-4 p-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($categories as $category)
            <tr>
                <td>{{ $category->Name }}</td>
                <td>{{ $category->Description }}</td>
                <td>{{ $category->IsActive ? 'Active' : 'Inactive' }}</td>
                <td>
                    <a href="{{ route('legal.setup.policy_categories.edit', $category->Id) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                    <form action="{{ route('legal.setup.policy_categories.destroy', $category->Id) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">🗑️ Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center">No records found</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
