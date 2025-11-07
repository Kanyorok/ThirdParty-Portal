@extends('layouts.app')
@section('content')
    <h4 class="mb-3">New Bank Transaction</h4>
    <form method="POST" action="{{ route('finance.banktransactions.store') }}">
        @csrf
        @include('finance.bank-transactions._form')
    </form>
@endsection
