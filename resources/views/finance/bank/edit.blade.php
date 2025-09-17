@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Edit Bank</h1>
    <form action="{{ route('finance.bank.update', $bank->BankID) }}" method="POST" class="mt-3">
        @csrf @method('PUT')
        @include('finance.bank._form', ['bank' => $bank])
        <div class="mt-4">
            <button class="btn btn-success">Save</button>
            <a href="{{ route('finance.bank.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
@endsection
