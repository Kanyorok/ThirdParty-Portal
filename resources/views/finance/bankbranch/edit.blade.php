@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Branch: {{ $branch->BranchName }}</h1>

    <form action="{{ route('finance.bankbranch.update', [$bankId, $branch->BranchID]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="BranchName">Branch Name</label>
            <input type="text" name="BranchName" class="form-control" value="{{ old('BranchName', $branch->BranchName) }}" required>
        </div>
        <div class="form-group">
            <label for="BranchCode">Branch Code</label>
            <input type="text" name="BranchCode" class="form-control" value="{{ old('BranchCode', $branch->BranchCode) }}">
        </div>
        <div class="form-group">
            <label for="Address1">Address Line 1</label>
            <input type="text" name="Address1" class="form-control" value="{{ old('Address1', $branch->Address1) }}">
        </div>
        <div class="form-group">
            <label for="Address2">Address Line 2</label>
            <input type="text" name="Address2" class="form-control" value="{{ old('Address2', $branch->Address2) }}">
        </div>
        <div class="form-group">
            <label for="Phone">Phone</label>
            <input type="text" name="Phone" class="form-control" value="{{ old('Phone', $branch->Phone) }}">
        </div>
        <div class="form-group">
            <label for="EmailID">Email</label>
            <input type="email" name="EmailID" class="form-control" value="{{ old('EmailID', $branch->EmailID) }}">
        </div>

        <button type="submit" class="btn btn-success mt-3">Save Changes</button>
        <a href="{{ route('finance.bankbranch.index', $bankId) }}" class="btn btn-secondary mt-3 ml-2">Cancel</a>
    </form>
</div>
@endsection
