@extends('layouts.app')

@section('title', 'Sanctions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Sanctions</h2>
        <a class="btn btn-primary" href="{{ route('hr.discipline.sanctions.create') }}">+ New Sanction</a>
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
                            <th>Suspension</th>
                            <th>Payroll</th>
                            <th>Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sanctions as $sanction)
                            <tr>
                                <td>{{ $sanction->Code }}</td>
                                <td>{{ $sanction->Name }}</td>
                                <td>{{ $sanction->IsSuspension ? 'Yes' : 'No' }}</td>
                                <td>{{ $sanction->AffectsPayroll ? 'Yes' : 'No' }}</td>
                                <td>{{ $sanction->IsActive ? 'Yes' : 'No' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.discipline.sanctions.edit', $sanction->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No sanctions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
