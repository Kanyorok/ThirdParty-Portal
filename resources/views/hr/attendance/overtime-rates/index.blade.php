@extends('layouts.app')

@section('title', 'Overtime Rates')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Overtime Rates</h2>
        <a class="btn btn-primary" href="{{ route('hr.attendance.overtime-rates.create') }}">+ New Rate</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Multiplier</th>
                            <th>Effective From</th>
                            <th>Effective To</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr>
                                <td>{{ $rate->grade?->Name ?? '-' }}</td>
                                <td>{{ number_format($rate->RateMultiplier ?? 0, 4) }}</td>
                                <td>{{ $rate->EffectiveFrom?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $rate->EffectiveTo?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $rate->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.attendance.overtime-rates.edit', $rate->Id) }}">Edit</a>
                                    @if($rate->IsActive)
                                        <form method="POST" action="{{ route('hr.attendance.overtime-rates.destroy', $rate->Id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No overtime rates configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $rates->links() }}
    </div>
</div>
@endsection
