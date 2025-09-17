@extends('layouts.app')
@section('content')
<h4 class="mb-3">New Bank Transfer</h4>
<form method="POST" action="{{ route('finance.banktransfers.store') }}">
  @csrf
  @include('finance.bank-transfers._form')
</form>
@endsection
