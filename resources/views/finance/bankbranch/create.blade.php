@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create New Branch for {{ $bankId }}</h1>

    <form action="{{ route('finance.bankbranch.store', $bankId) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="BranchName">Branch Name</label>
            <input type="text" name="BranchName" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="BranchCode">Branch Code</label>
            <input type="text" name="BranchCode" class="form-control">
        </div>
        <button type="submit" class="btn btn-success mt-3">Save Branch</button>
    </form>
</div>
@endsection
