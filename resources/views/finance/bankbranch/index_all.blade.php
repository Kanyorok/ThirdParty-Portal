@extends('layouts.app')

@section('content')
<div class="container">
    <h1>All Bank Branches</h1>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Bank</th>
                <th>Branch Name</th>
                <th>Branch Code</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($branches as $branch)
            <tr>
                <td>{{ optional($branch->bank)->BankName ?? '—' }}</td>
                <td>{{ $branch->BranchName }}</td>
                <td>{{ $branch->BranchCode }}</td>
                <td>
                    <a href="{{ route('finance.bankbranch.edit', [$branch->BankID, $branch->BranchID]) }}" class="btn btn-sm btn-warning">Edit</a>
                    <a href="{{ route('finance.bankbranch.show', [$branch->BankID, $branch->BranchID]) }}" class="btn btn-sm btn-info">View</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No branches yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $branches->links() }}
</div>
@endsection
