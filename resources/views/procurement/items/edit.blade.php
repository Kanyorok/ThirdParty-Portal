@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Edit Item</h2>
    @include('procurement.items.form', [
        'route' => route('procurement.items.update', $item),
        'method' => 'PUT',
        'item' => $item
    ])
</div>
@endsection
