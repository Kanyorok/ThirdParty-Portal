@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Edit Cashbook Entry #{{ $entry->CashbookID }}</h1>
        <form action="{{ route('cashbook.update', $entry->CashbookID) }}" method="POST" class="mt-3">
            @csrf @method('PUT')
            @include('finance.cashbook._form', ['entry' => $entry])
            <div class="mt-4">
                <button class="btn btn-success">Save</button>
                <a href="{{ route('cashbook.show', $entry->CashbookID) }}" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
@endsection
