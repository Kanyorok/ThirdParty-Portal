@extends('layouts.app')
@section('title', 'Edit Intellectual Property')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">✏️ Edit IP Record</h4>

    <form method="POST" action="{{ route('legal.intellectual.update', $record->ID) }}">
        @csrf
        @method('PUT')

        @include('legal.intellectual.partials.form', ['record' => $record])
        
        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
