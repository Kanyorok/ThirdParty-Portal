@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Add New Item</h2>
    @include('procurement.items.form', ['route' => route('procurement.items.store'), 'method' => 'POST'])
</div>
@endsection
