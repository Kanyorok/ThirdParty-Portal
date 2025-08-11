@extends('layouts.app')
@section('title', 'Intellectual Property Details')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4>🧠 Intellectual Property Details</h4>
    <ul class="list-group">
        <li class="list-group-item"><strong>Type:</strong> {{ $record->IPType }}</li>
        <li class="list-group-item"><strong>Title:</strong> {{ $record->Title }}</li>
        <li class="list-group-item"><strong>Owner:</strong> {{ $record->Owner }}</li>
        <li class="list-group-item"><strong>Status:</strong> {{ $record->Status }}</li>
        <li class="list-group-item"><strong>Registration Number:</strong> {{ $record->RegistrationNumber }}</li>
        <li class="list-group-item"><strong>Registration Date:</strong> {{ $record->RegistrationDate }}</li>
        <li class="list-group-item"><strong>Expiry Date:</strong> {{ $record->ExpiryDate }}</li>
        <li class="list-group-item"><strong>Remarks:</strong> {{ $record->Remarks }}</li>
    </ul>
</div>
@endsection
