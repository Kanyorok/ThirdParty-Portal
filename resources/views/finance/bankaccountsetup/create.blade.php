@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Create Bank Account</h1>
    <form action="{{ route('finance.bankaccountsetup.store') }}" method="POST" class="mt-3">
        @csrf
        @include('finance.bankaccountsetup._form', ['account' => null])
        <div class="mt-4">
            <button class="btn btn-success">Save</button>
            <a href="{{ route('finance.bankaccountsetup.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
