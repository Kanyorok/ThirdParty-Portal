@extends('layouts.app')

@section('title', 'KPI Formulas')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Calculation / Scoring Formulas</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.kpi.formulas.create') }}">+ New Formula</a>
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
                            <th>Expression</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($formulas as $formula)
                            <tr>
                                <td>{{ $formula->Code }}</td>
                                <td>{{ $formula->Name }}</td>
                                <td><code>{{ \Illuminate\Support\Str::limit($formula->Expression, 80) }}</code></td>
                                <td>{{ $formula->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.kpi.formulas.edit', $formula->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.config.kpi.formulas.destroy', $formula->Id) }}" method="POST" onsubmit="return confirm('Deactivate this formula?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No formulas found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $formulas->links() }}
        </div>
    </div>
</div>
@endsection
