@extends('layouts.app')

@section('title', 'Religions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Religions</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.religions.create') }}">+ New Religion</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($religions as $religion)
                            <tr>
                                <td>{{ $religion->Name }}</td>
                                <td>{{ $religion->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.religions.edit', $religion->Id) }}">Edit</a>
                                    <form action="{{ route('hr.config.religions.destroy', $religion->Id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Deactivate this religion?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center">No religions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $religions->links() }}
        </div>
    </div>
</div>
@endsection
