@extends('layouts.app')
@section('title','Create Category')
@section('content')
    <h1>Create Item Category</h1>
    <form method="POST" action="{{ route('categories.store') }}">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Category Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Category Description</label>
            <input type="text" name="description" id="description" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Category</button>
    </form>
@endsection
