@extends('layouts.app')
@section('title', 'Create Procurement Period')
@section('content')
<div class="container">
    <h3>Create New Procurement Period</h3>

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Whoops!</strong> Please fix the following issues:<br>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('procurement-periods.store') }}">
        @csrf

        <div class="form-group mb-3">
            <label class="form-label" for="Title">Title <span class="text-danger">*</span></label>
            <input type="text" name="Title" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label for="StartDate">Start Date <span class="text-danger">*</span></label>
            <input type="date" name="StartDate" class="form-control" required min="{{ date('Y-m-d') }}">
        </div>

        <div class="form-group mb-3">
            <label for="EndDate">End Date <span class="text-danger">*</span></label>
            <input type="date" name="EndDate" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary mt-3">Create Period</button>
    </form>
</div>
@endsection