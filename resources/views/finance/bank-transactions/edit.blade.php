@extends('layouts.app')
@section('content')
    <h4 class="mb-3">Edit Bank Transaction #{{ $row->BankTxnID }}</h4>
    <form method="POST" action="{{ route('finance.banktransactions.update',$row->BankTxnID) }}">
        @csrf @method('PUT')
        @include('finance.bank-transactions._form')
    </form>
@endsection
