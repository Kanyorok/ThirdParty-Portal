@extends('layouts.app')
@section('title', 'Register Intellectual Property')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">➕ New IP Registration</h4>

    <form method="POST" action="{{ route('legal.intellectual.store') }}">
        @csrf

        @include('legal.intellectual.partials.form')
        
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
