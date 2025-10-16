@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>
            New {{ ($presetType ?? '') === 'PAYMENT' ? 'Payment' : (($presetType ?? '') === 'RECEIPT' ? 'Receipt' : 'Entry') }}</h1>
        <form action="{{ route('cashbook.store') }}" method="POST" class="mt-3">
            @csrf
            @include('finance.cashbook._form', ['entry' => null])
            <div class="mt-4">
                <button class="btn btn-success">Save Draft</button>
                <a href="{{ route('cashbook.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
