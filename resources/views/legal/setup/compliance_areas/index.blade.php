@extends('layouts.app')
@section('title', 'Compliance Areas')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <h4 class="mb-3">📂 Compliance Areas</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('legal.setup.compliance_areas.create') }}" class="btn btn-primary mb-3">➕ Add Compliance
            Area</a>

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
            @forelse($areas as $area)
                <tr>
                    <td>{{ $area->Name }}</td>
                    <td>{{ $area->Description }}</td>
                    <td>{{ $area->IsActive ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <a href="{{ route('legal.setup.compliance_areas.edit', $area->Id) }}"
                           class="btn btn-sm btn-warning">✏️ Edit</a>
                        <form action="{{ route('legal.setup.compliance_areas.destroy', $area->Id) }}" method="POST"
                              style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">🗑️
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No records found</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
