@extends('layouts.app')

@section('title', 'Fringe Benefits')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Fringe Benefit Valuation Rules</h2>
        <a class="btn btn-primary" href="{{ route('hr.statutory.fringe.create') }}">+ New Rule</a>
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
                            <th>Rate Type</th>
                            <th>Rate</th>
                            <th>Cap</th>
                            <th>Effective From</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($benefits as $benefit)
                            <tr>
                                <td>{{ $benefit->Code }}</td>
                                <td>{{ $benefit->Name }}</td>
                                <td>{{ $benefit->RateType }}</td>
                                <td>{{ $benefit->Rate }}</td>
                                <td>{{ $benefit->CapAmount ? number_format($benefit->CapAmount,2) : '-' }}</td>
                                <td>{{ $benefit->EffectiveFrom }}</td>
                                <td>{{ $benefit->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.statutory.fringe.edit', $benefit->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.statutory.fringe.destroy', $benefit->Id) }}" method="POST" onsubmit="return confirm('Deactivate this rule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center">No fringe benefit rules found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $benefits->links() }}
        </div>
    </div>
</div>
@endsection
