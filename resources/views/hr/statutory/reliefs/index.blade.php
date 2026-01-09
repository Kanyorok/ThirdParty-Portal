@extends('layouts.app')

@section('title', 'Tax Reliefs')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Tax Reliefs</h2>
        <a class="btn btn-primary" href="{{ route('hr.statutory.reliefs.create') }}">+ New Relief</a>
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
                            <th>Stage</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Effective From</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reliefs as $relief)
                            <tr>
                                <td>{{ $relief->Code }}</td>
                                <td>{{ $relief->Name }}</td>
                                <td>{{ ($relief->ApplyStage ?? 'PostTax') === 'PreTax' ? 'Pre-tax' : 'Post-tax' }}</td>
                                <td>{{ $relief->ReliefType ?? 'Fixed' }}</td>
                                <td>
                                    @if(($relief->ReliefType ?? 'Fixed') === 'Percentage')
                                        {{ number_format($relief->ReliefRate ?? 0, 2) }}%
                                        @if($relief->DeductionID)
                                            of {{ $deductionLookup[$relief->DeductionID] ?? 'deduction' }}
                                        @endif
                                    @else
                                        {{ number_format($relief->Amount, 2) }}
                                    @endif
                                </td>
                                <td>{{ $relief->EffectiveFrom }}</td>
                                <td>{{ $relief->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.statutory.reliefs.edit', $relief->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.statutory.reliefs.destroy', $relief->Id) }}" method="POST" onsubmit="return confirm('Deactivate this relief?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center">No reliefs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $reliefs->links() }}
        </div>
    </div>
</div>
@endsection
