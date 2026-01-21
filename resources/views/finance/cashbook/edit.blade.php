@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Edit Cashbook Entry #{{ $entry->CashbookID }}</h1>
        <form action="{{ route('cashbook.update', $entry->CashbookID) }}" method="POST" class="mt-3">
            @csrf @method('PUT')
            @include('finance.cashbook._form', ['entry' => $entry])
        </form>
    </div>
@endsection
