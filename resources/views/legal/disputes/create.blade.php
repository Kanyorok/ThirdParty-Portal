@extends('layouts.app')
@section('title', 'Add Legal Case')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">➕ Add Legal Case</h4>

    <form action="{{ route('legal.cases.store') }}" method="POST">
        @csrf
        @include('legal.disputes.partials.form-fields')
        <button type="submit" class="btn btn-success">💾 Save Case</button>
    </form>
</div>
@endsection
