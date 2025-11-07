@extends('layouts.app')
@section('title', 'Edit Procurement Period')

@section('content')
<div class="container">
    <h3>Edit Procurement Period</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Please fix the following:<br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('procurement-periods.update', $period->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label class="form-label" for="Title">Title </label>
            <input type="text" name="Title" id="name" value="{{ old('Title', $period->Title) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Start Date</label>
            <input type="date" name="StartDate" class="form-control" value="{{ $period->StartDate }}" required>
        </div>

        <div class="mb-3">
            <label>End Date</label>
            <input type="date" name="EndDate" class="form-control" value="{{ $period->EndDate }}" required>
        </div>

        <button type="submit" class="btn btn-success mt-2">Update</button>
        <a href="{{ route('procurement-periods.index') }}" class="btn btn-secondary mt-2">Back</a>
    </form>
</div>
@endsection
