@extends('layouts.app')
@section('title','Available Procurement Modes')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Procurement Modes</h3>
        <a href="{{ route('procurement-modes.create') }}" class="btn btn-primary">+ Add Mode</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($modes->isEmpty())
        <div class="alert alert-info">No procurement modes found.</div>
    @else
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>PMode ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($modes as $index => $mode)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $mode->UniqueCode }}</td>
                    <td>{{ $mode->Name }}</td>
                    <td>{{ $mode->Description }}</td>
                    <td>{{ $mode->created_at->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('procurement-modes.edit', $mode->id) }}" class="btn btn-sm btn-warning">Edit</a>

                        <form action="{{ route('procurement-modes.destroy', $mode->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this mode?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
