@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Edit Branch</h1>
    <form action="{{ route('finance.bankbranch.update', $branch->BranchID) }}" method="POST" class="mt-3">
        @csrf @method('PUT')
        @include('finance.bankbranch._form', ['branch' => $branch, 'bank' => $bank ?? null])
        <div class="mt-4">
            <button class="btn btn-success">Save</button>
            <a href="{{ route('finance.bankbranch.bybank', $branch->BankID) }}" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
@endsection
