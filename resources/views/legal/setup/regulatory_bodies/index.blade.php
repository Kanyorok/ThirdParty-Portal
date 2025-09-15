@extends('layouts.app')
@section('title', 'Regulatory Bodies')

@section('content')
    <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-info mb-0"></h4>
 <a href="{{ route('legal.setup.regulatory_bodies.create') }}" class="btn btn-primary mb-3">➕ Add Regulatory Body</a>
    </div>
<div class="card shadow rounded-4 p-4">
  
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

       <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Jurisdiction</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($bodies as $body)
            <tr>
                <td>{{ $body->Name }}</td>
                <td>{{ $body->Jurisdiction }}</td>
                <td>{{ $body->ContactPerson }}</td>
                <td>{{ $body->ContactEmail }}</td>
                <td>{{ $body->ContactPhone }}</td>
                <td>{{ $body->IsActive ? 'Active' : 'Inactive' }}</td>
                <td>
                    <a href="{{ route('legal.setup.regulatory_bodies.edit', $body->Id) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                    <form action="{{ route('legal.setup.regulatory_bodies.destroy', $body->Id) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">🗑️ Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">No records found</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
