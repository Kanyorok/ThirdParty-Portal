@extends('layouts.app')

@section('title', 'Promotions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Promotions</h2>
        <a class="btn btn-primary" href="{{ route('hr.movements.promotions.create') }}">+ New Promotion</a>
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
                        @forelse($promotions as $promo)
                            <tr>
                                <td>{{ $promo->employee->FirstName ?? '' }} {{ $promo->employee->LastName ?? '' }}</td>
                                <td>{{ $promo->FromGradeID }}</td>
                                <td>{{ $promo->ToGradeID }}</td>
                                <td>{{ optional($promo->EffectiveDate)->format('Y-m-d') }}</td>
                                <td>{{ $promo->Status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.movements.promotions.show', $promo->Id) }}">View</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.movements.promotions.edit', $promo->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No promotion requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $promotions->links() }}
        </div>
    </div>
</div>
@endsection
