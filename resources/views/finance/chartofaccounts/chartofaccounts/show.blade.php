@extends('layouts.app')
@section('title', 'GL Account Details')
@section('content')
    <div class="container mt-4">
        <h4>🔍 GL Account Details</h4>

        <ul class="list-group">
            <li class="list-group-item"><strong>Code:</strong> {{ $account->GLCode }}</li>
            <li class="list-group-item"><strong>Name:</strong> {{ $account->AccountName }}</li>
            <li class="list-group-item"><strong>Description:</strong> {{ $account->Description }}</li>
            <li class="list-group-item"><strong>Active:</strong> {{ $account->IsActive ? 'Yes' : 'No' }}</li>
        </ul>

        <a href="{{ route('chartofaccounts.index') }}" class="btn btn-secondary mt-3">Back</a>
    </div>
@endsection
