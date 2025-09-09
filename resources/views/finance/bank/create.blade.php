@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create New Bank</h1>

    <form action="{{ route('bank.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="BankName">Bank Name</label>
            <input type="text" name="BankName" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="ShortName">Short Name</label>
            <input type="text" name="ShortName" class="form-control">
        </div>
        <!-- Add other fields here -->
        <button type="submit" class="btn btn-success mt-3">Save Bank</button>
    </form>
</div>
@endsection
