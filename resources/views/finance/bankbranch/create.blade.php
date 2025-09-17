@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Create Branch</h1>
    <form action="{{ route('finance.bankbranch.store') }}" method="POST" class="mt-3">
        @csrf
        @include('finance.bankbranch._form', ['branch' => null, 'bank' => $bank ?? null])
        <div class="mt-4">
            <button class="btn btn-success">Save</button>
            <a href="{{ isset($bank) ? route('finance.bankbranch.bybank', $bank->BankID) : route('finance.bankbranch.index') }}"
               class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
