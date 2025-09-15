@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Create Bank</h1>
    <form action="{{ route('finance.bank.store') }}" method="POST" class="mt-3">
        @csrf
        @include('finance.bank._form', ['bank' => null])
        <div class="mt-4">
            <button class="btn btn-success">Save</button>
            <a href="{{ route('finance.bank.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
