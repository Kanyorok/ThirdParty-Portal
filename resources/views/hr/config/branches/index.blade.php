@extends('layouts.app')

@section('title', 'Branches')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Branches</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.branches.create') }}">+ New Branch</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>City</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($branches as $branch)
                    <tr>
                        <td>{{ $branch->BranchID }}</td>
                        <td>{{ $branch->Name }}</td>
                        <td>{{ $branch->City }}</td>
                        <td>{{ $branch->Country }}</td>
                        <td>{{ $branch->IsActive ? 'Active' : 'Inactive' }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.branches.edit', $branch->Id) }}">Edit</a>
                            <form action="{{ route('hr.config.branches.destroy', $branch->Id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deactivate this branch?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No branches found.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $branches->links() }}
        </div>
    </div>
</div>
@endsection
