@extends('layouts.app')

@section('title', 'Payroll Deductions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Payroll Deductions</h2>
        <a class="btn btn-primary" href="{{ route('hr.statutory.deductions.create') }}">+ New Deduction</a>
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
                            <th>Status</th>
                            <th>Rules</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deductions as $deduction)
                            <tr>
                                <td>{{ $deduction->Code }}</td>
                                <td>{{ $deduction->Name }}</td>
                                <td>{{ $deduction->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td><a href="{{ route('hr.statutory.deductions.rules.index', $deduction->Id) }}" class="btn btn-sm btn-outline-secondary">Manage Rules</a></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.statutory.deductions.edit', $deduction->Id) }}">Edit</a>
                                    @if($deduction->IsActive)
                                        <form class="d-inline" action="{{ route('hr.statutory.deductions.destroy', $deduction->Id) }}" method="POST" onsubmit="return confirm('Deactivate this deduction?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                        </form>
                                    @else
                                        <form class="d-inline" action="{{ route('hr.statutory.deductions.activate', $deduction->Id) }}" method="POST" onsubmit="return confirm('Activate this deduction?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit">Activate</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No deductions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $deductions->links() }}
        </div>
    </div>
</div>
@endsection
