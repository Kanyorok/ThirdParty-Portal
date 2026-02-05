@extends('layouts.app')

@section('title', 'Allowances')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Allowances</h2>
        <a class="btn btn-primary" href="{{ route('hr.statutory.allowances.create') }}">+ New Allowance</a>
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
                            <th>Taxable</th>
                            <th>Mandatory</th>
                            <th>Status</th>
                            <th>Rules</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allowances as $allowance)
                            <tr>
                                <td>{{ $allowance->Code }}</td>
                                <td>{{ $allowance->Name }}</td>
                                <td>{{ $allowance->IsTaxable ? 'Yes' : 'No' }}</td>
                                <td>
                                    @if($allowance->IsMandatory)
                                        <span class="badge bg-primary">Yes</span>
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>
                                <td>{{ $allowance->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    @if($allowance->IsMandatory)
                                        <a href="{{ route('hr.statutory.allowances.rules.index', $allowance->Id) }}" class="btn btn-sm btn-outline-secondary">Manage Rules</a>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.statutory.allowances.edit', $allowance->Id) }}">Edit</a>
                                    @if($allowance->IsActive)
                                        <form class="d-inline" action="{{ route('hr.statutory.allowances.destroy', $allowance->Id) }}" method="POST" onsubmit="return confirm('Deactivate this allowance?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                        </form>
                                    @else
                                        <form class="d-inline" action="{{ route('hr.statutory.allowances.activate', $allowance->Id) }}" method="POST" onsubmit="return confirm('Activate this allowance?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit">Activate</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No allowances found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $allowances->links() }}
        </div>
    </div>
</div>
@endsection
