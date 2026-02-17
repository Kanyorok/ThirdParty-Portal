@extends('layouts.app')

@section('title', 'Offences')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Offences</h2>
        <a class="btn btn-primary" href="{{ route('hr.discipline.offences.create') }}">+ New Offence</a>
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
                            <th>Name</th>
                            <th>Category</th>
                            <th>Severity</th>
                            <th>Hearing</th>
                            <th>Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offences as $offence)
                            <tr>
                                <td>{{ $offence->Code }}</td>
                                <td>{{ $offence->Name }}</td>
                                <td>{{ $offence->category?->Name ?? '-' }}</td>
                                <td>{{ $offence->Severity }}</td>
                                <td>{{ $offence->HearingRequired ? 'Yes' : 'No' }}</td>
                                <td>{{ $offence->IsActive ? 'Yes' : 'No' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.discipline.offences.edit', $offence->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No offences found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
