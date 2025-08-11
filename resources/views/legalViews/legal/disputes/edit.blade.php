@extends('layouts.app')
@section('title', 'Edit Legal Case')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">✏️ Edit Legal Case</h4>

    <form action="{{ route('legal.cases.update', $case->ID) }}" method="POST">
        @csrf
        @method('PUT')
        @include('legal.disputes.partials.form-fields', ['case' => $case])
        <button type="submit" class="btn btn-primary">💾 Update Case</button>
    </form>
</div>
@endsection
