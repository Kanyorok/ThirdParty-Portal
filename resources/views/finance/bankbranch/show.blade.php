@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Branch Details</h1>
    <div class="card mt-3">
        <div class="card-body">
            <dl class="row">
                <dt class="col-md-3">Bank</dt><dd class="col-md-9">{{ optional($branch->bank)->BankName ?? '—' }}</dd>
                <dt class="col-md-3">Branch Name</dt><dd class="col-md-9">{{ $branch->BranchName }}</dd>
                <dt class="col-md-3">Branch Code</dt><dd class="col-md-9">{{ $branch->BranchCode ?? '—' }}</dd>
                <dt class="col-md-3">Address1</dt><dd class="col-md-9">{{ $branch->Address1 ?? '—' }}</dd>
                <dt class="col-md-3">Address2</dt><dd class="col-md-9">{{ $branch->Address2 ?? '—' }}</dd>
                <dt class="col-md-3">City ID</dt><dd class="col-md-9">{{ $branch->CityID ?? '—' }}</dd>
                <dt class="col-md-3">Country ID</dt><dd class="col-md-9">{{ $branch->CountryID ?? '—' }}</dd>
                <dt class="col-md-3">Zip Code</dt><dd class="col-md-9">{{ $branch->ZipCode ?? '—' }}</dd>
                <dt class="col-md-3">Phone</dt><dd class="col-md-9">{{ $branch->Phone ?? '—' }}</dd>
                <dt class="col-md-3">Email</dt><dd class="col-md-9">{{ $branch->EmailID ?? '—' }}</dd>
                <dt class="col-md-3">Active</dt><dd class="col-md-9">{{ $branch->IsActive ? 'Yes' : 'No' }}</dd>
            </dl>
        </div>
        <div class="card-footer">
            <a href="{{ route('finance.bankbranch.edit', $branch->BranchID) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('finance.bankbranch.bybank', $branch->BankID) }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
