@extends('layouts.app')

@section('title', 'Document Categories')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Document Categories</h2>
        <a class="btn btn-primary" href="{{ route('hr.shared-docs.categories.create') }}">+ New Category</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->Code ?? '-' }}</td>
                                <td>{{ $category->Name }}</td>
                                <td>{{ $category->Description ?? '-' }}</td>
                                <td>{{ $category->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.shared-docs.categories.edit', $category->Id) }}">Edit</a>
                                    @if($category->IsActive)
                                        <form method="POST" action="{{ route('hr.shared-docs.categories.destroy', $category->Id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No categories found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $categories->links() }}
    </div>
</div>
@endsection
