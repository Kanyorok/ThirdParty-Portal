@extends('layouts.app')
@section('title', 'Compliance Obligations')

@section('content')
<div class="card shadow rounded-4 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">📑 Compliance Obligations</h4>
        <a href="{{ route('legal.compliance.obligations.create') }}" class="btn btn-sm btn-primary">➕ Add Obligation</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Title</th>
                <th>Regulator</th>
                <th>Compliance Area</th>
                <th>Effective Date</th>
                <th>Status</th>
                <th style="width: 120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($obligations as $obligation)
            <tr>
                <td>{{ $obligation->Title }}</td>
                <td>{{ $obligation->regulator->Name ?? '' }}</td>
                <td>{{ $obligation->area->Name ?? '' }}</td>
                <td>{{ $obligation->EffectiveDate }}</td>
                <td>{{ $obligation->IsActive ? 'Active' : 'Inactive' }}</td>
                <td>
                    <a href="{{ route('legal.compliance.obligations.show', $obligation->Id) }}" class="btn btn-sm btn-info">🔍</a>
                    <a href="{{ route('legal.compliance.obligations.edit', $obligation->Id) }}" class="btn btn-sm btn-warning">✏️</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">No obligations found</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
