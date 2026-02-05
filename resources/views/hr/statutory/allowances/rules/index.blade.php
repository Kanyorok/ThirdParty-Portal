@extends('layouts.app')

@section('title', 'Allowance Rules')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">{{ $allowance->Name }} Rules</h2>
            <div class="text-muted">Code: {{ $allowance->Code }}</div>
        </div>
        <a class="btn btn-primary" href="{{ route('hr.statutory.allowances.rules.create', $allowance->Id) }}">+ New Rule</a>
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
                            <th>Calc Method</th>
                            <th>Rate</th>
                            <th>Amount</th>
                            <th>Band From</th>
                            <th>Band To</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Formula</th>
                            <th>Effective From</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                            <tr>
                                <td>{{ $rule->CalcMethod }}</td>
                                <td>{{ $rule->Rate }}</td>
                                <td>{{ $rule->Amount }}</td>
                                <td>{{ $rule->IncomeFrom }}</td>
                                <td>{{ $rule->IncomeTo ?? 'No cap' }}</td>
                                <td>{{ $rule->MinAmount ?? '-' }}</td>
                                <td>{{ $rule->MaxAmount ?? '-' }}</td>
                                <td><code>{{ \Illuminate\Support\Str::limit($rule->FormulaText,50) }}</code></td>
                                <td>{{ $rule->EffectiveFrom }}</td>
                                <td>{{ $rule->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.statutory.allowances.rules.edit', [$allowance->Id, $rule->Id]) }}">Edit</a>
                                    @if($rule->IsActive)
                                        <form class="d-inline" action="{{ route('hr.statutory.allowances.rules.destroy', [$allowance->Id, $rule->Id]) }}" method="POST" onsubmit="return confirm('Deactivate this rule?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                        </form>
                                    @else
                                        <form class="d-inline" action="{{ route('hr.statutory.allowances.rules.activate', [$allowance->Id, $rule->Id]) }}" method="POST" onsubmit="return confirm('Activate this rule?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit">Activate</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center">No rules found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $rules->links() }}
        </div>
    </div>
</div>
@endsection
