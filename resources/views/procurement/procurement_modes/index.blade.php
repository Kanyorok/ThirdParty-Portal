@extends('layouts.app')
@section('title','Available Procurement Methods')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Procurement Methods</h3>
        <a href="{{ route('procurement-modes.create') }}" class="btn btn-primary">
            + Add Method
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($modes->isEmpty())
        <div class="alert alert-info">No procurement methods found.</div>
    @else
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>PMethod id</th>
                <th>Name</th>
                <th>Description</th>
                <th>Created At</th>
                <th style="width: 200px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($modes as $index => $mode)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $mode->UniqueCode }}</td>
                <td>{{ $mode->Name }}</td>
                <td>{{ $mode->Description }}</td>
                <td>{{ optional($mode->CreatedOn)->format('Y-m-d') ?? 'N/A' }}</td>
                <td>
                    <a href="{{ route('procurement-modes.show', $mode->id) }}"
                        class="btn btn-sm btn-info"
                        title="View Mode">
                        View
                    </a>

                    <a href="{{ route('procurement-modes.edit', $mode->id) }}"
                        class="btn btn-sm btn-warning"
                        title="Edit Mode">
                        Edit
                    </a>

                    <form action="{{ route('procurement-modes.destroy', $mode->id) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this mode?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Mode">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
