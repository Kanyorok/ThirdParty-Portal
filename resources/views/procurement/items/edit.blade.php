@extends('layouts.app')
@section('title','Edit Item')
@section('content')
<div class="container">
    <h2>Edit Item</h2>
    @include('procurement.items.form', [
        'route' => route('items.update', $item),
        'method' => 'PUT',
        'item' => $item
    ])
</div>
@endsection
