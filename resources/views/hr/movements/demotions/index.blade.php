@extends('layouts.app')

@section('title', 'Demotions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Demotions</h2>
        <a class="btn btn-primary" href="{{ route('hr.movements.demotions.create') }}">+ New Demotion</a>
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
                            <th>Employee</th>
                            <th>From Grade</th>
                            <th>To Grade</th>
                            <th>Effective</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demotions as $item)
                            <tr>
                                <td>{{ $item->employee->FirstName ?? '' }} {{ $item->employee->LastName ?? '' }}</td>
                                <td>{{ $item->FromGradeID }}</td>
                                <td>{{ $item->ToGradeID }}</td>
                                <td>{{ optional($item->EffectiveDate)->format('Y-m-d') }}</td>
                                <td>{{ $item->Status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.movements.demotions.show', $item->Id) }}">View</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.movements.demotions.edit', $item->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No demotion requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $demotions->links() }}
        </div>
    </div>
</div>
@endsection
