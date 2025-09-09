@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Branches of {{ $bank->BankName }}</h1>
    <a href="{{ route('finance.bankbranch.create', $bank->BankID) }}" class="btn btn-primary">Add New Branch</a>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Branch Name</th>
                <th>Branch Code</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($branches as $branch)
                <tr>
                    <td>{{ $branch->BranchName }}</td>
                    <td>{{ $branch->BranchCode }}</td>
                    <td>
                        <a href="{{ route('finance.bankbranch.edit', [$bank->BankID, $branch->BranchID]) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('finance.bankbranch.destroy', [$bank->BankID, $branch->BranchID]) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
