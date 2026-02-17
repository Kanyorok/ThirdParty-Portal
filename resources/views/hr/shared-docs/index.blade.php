@extends('layouts.app')

@section('title', 'Shared Documents')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Shared Documents</h2>
        <a class="btn btn-primary" href="{{ route('hr.shared-docs.create') }}">+ New Document</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->Id }}" @selected(request('category_id') == $category->Id)>{{ $category->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Access</label>
                    <select name="access" class="form-select">
                        <option value="">All</option>
                        @foreach($accessLevels as $level)
                            <option value="{{ $level }}" @selected(request('access') === $level)>{{ $level }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach($statusList as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Mandatory</label>
                    <select name="mandatory" class="form-select">
                        <option value="">All</option>
                        <option value="1" @selected(request('mandatory') === '1')>Yes</option>
                        <option value="0" @selected(request('mandatory') === '0')>No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Version</th>
                            <th>Effective</th>
                            <th>Access</th>
                            <th>Mandatory</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            <tr>
                                <td>{{ $doc->Title }}</td>
                                <td>{{ $doc->category?->Name ?? '-' }}</td>
                                <td>{{ $doc->Version ?? '-' }}</td>
                                <td>{{ $doc->EffectiveDate?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $doc->AccessLevel }}</td>
                                <td>{{ $doc->IsMandatory ? 'Yes' : 'No' }}</td>
                                <td>{{ $doc->Status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.shared-docs.show', $doc->Id) }}">View</a>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.shared-docs.edit', $doc->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No documents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $documents->links() }}
    </div>
</div>
@endsection
