@extends('layouts.app')

@section('title', 'Legal References')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Legal References</h2>
        <a class="btn btn-primary" href="{{ route('hr.discipline.legal-refs.create') }}">+ New Reference</a>
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
                            <th>Code</th>
                            <th>Section</th>
                            <th>Title</th>
                            <th>Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($refs as $ref)
                            <tr>
                                <td>{{ $ref->Code }}</td>
                                <td>{{ $ref->Section }}</td>
                                <td>{{ $ref->Title }}</td>
                                <td>{{ $ref->IsActive ? 'Yes' : 'No' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.discipline.legal-refs.edit', $ref->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No references found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
