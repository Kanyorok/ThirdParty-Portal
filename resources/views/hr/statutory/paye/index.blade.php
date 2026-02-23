@extends('layouts.app')

@section('title', 'PAYE Bands')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">PAYE Bands</h2>
        <a class="btn btn-primary" href="{{ route('hr.statutory.paye.create') }}">+ New Band</a>
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
                            <th>Lower</th>
                            <th>Upper</th>
                            <th>Rate (%)</th>
                            <th>Fixed Amount</th>
                            <th>Effective From</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bands as $band)
                            <tr>
                                <td>{{ number_format($band->LowerLimit,2) }}</td>
                                <td>{{ $band->UpperLimit ? number_format($band->UpperLimit,2) : 'No cap' }}</td>
                                <td>{{ $band->Rate }}</td>
                                <td>{{ $band->FixedAmount }}</td>
                                <td>{{ $band->EffectiveFrom }}</td>
                                <td>{{ $band->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.statutory.paye.edit', $band->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.statutory.paye.destroy', $band->Id) }}" method="POST" onsubmit="return confirm('Deactivate this band?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No bands found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $bands->links() }}
        </div>
    </div>
</div>
@endsection
