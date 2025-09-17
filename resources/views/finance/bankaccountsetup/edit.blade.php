@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Edit Bank Account</h1>
    <form action="{{ route('finance.bankaccountsetup.update', $account->AccountID) }}" method="POST" class="mt-3">
        @csrf @method('PUT')
        @include('finance.bankaccountsetup._form', ['account' => $account])
        <div class="mt-4">
            <button class="btn btn-success">Save</button>
            <a href="{{ route('finance.bankaccountsetup.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
@endsection
