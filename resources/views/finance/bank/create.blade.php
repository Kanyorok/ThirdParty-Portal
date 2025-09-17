@extends('layouts.app')
@section('title', 'Create Bank')
@section('content')
<div class="container">
    <div class="card p-3">
        <form action="{{ route('finance.bank.store') }}" method="POST" class="mt-3">
            @csrf
            @include('finance.bank._form', ['bank' => null])
            <div class="mt-4">
                <button class="btn btn-success">Save</button>
                <a href="{{ route('finance.bank.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

</div>
@endsection
