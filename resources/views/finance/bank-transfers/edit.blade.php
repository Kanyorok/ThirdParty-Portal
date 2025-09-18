@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit Transfer #{{ $row->TransferID }}</h4>
<form method="POST" action="{{ route('finance.banktransfers.update',$row->TransferID) }}">
  @csrf @method('PUT')
  @include('finance.bank-transfers._form')
</form>
@endsection
