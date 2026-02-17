@extends('layouts.app')

@section('title', 'Disciplinary Policies')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Disciplinary Policies</h2>
        <a class="btn btn-primary" href="{{ route('hr.discipline.policies.create') }}">+ New Policy</a>
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
                            <th>Effective From</th>
                            <th>Appeal Days</th>
                            <th>Retention (months)</th>
                            <th>Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($policies as $policy)
                            <tr>
                                <td>{{ $policy->Name }}</td>
                                <td>{{ $policy->EffectiveFrom ? $policy->EffectiveFrom->format('Y-m-d') : '-' }}</td>
                                <td>{{ $policy->AppealDeadlineDays }}</td>
                                <td>{{ $policy->RetentionMonths }}</td>
                                <td>{{ $policy->IsActive ? 'Yes' : 'No' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.discipline.policies.edit', $policy->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No policies found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
