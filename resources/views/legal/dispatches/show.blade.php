@extends('layouts.app')
@section('title', 'Dispatch Entry Details')
@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📄 Dispatch Details</h4>
    <ul class="list-group">
        <li class="list-group-item"><strong>Dispatch Date:</strong> {{ $dispatch->DispatchDate }}</li>
        <li class="list-group-item"><strong>Recipient:</strong> {{ $dispatch->DispatchedTo }}</li>
        <li class="list-group-item"><strong>Method:</strong> {{ $dispatch->DispatchMethod }}</li>
        <li class="list-group-item"><strong>Status:</strong> {{ $dispatch->Status }}</li>
        <li class="list-group-item"><strong>Remarks:</strong> {{ $dispatch->Remarks }}</li>
    </ul>
</div>


@endsection
