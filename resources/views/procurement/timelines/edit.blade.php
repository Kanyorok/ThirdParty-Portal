@extends('layouts.app')
@section('title','Edit Timeline')
@section('content')
<div class="container">
    <h2>Edit Timeline for {{ $procurement_mode->Name }}</h2>

    {{-- Flash & Validation --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('timelines.update', $timeline->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <input type="hidden" name="ProcurementModeId" value="{{ $procurement_mode->id }}">

        <div class="form-group">
            <label for="Stage">Stage</label>
            <input type="text" name="Stage" class="form-control" value="{{ old('Stage', $timeline->Stage) }}" required>
        </div>

        <div class="form-group">
            <label for="DurationDays">Duration (Days)</label>
            <input type="number" name="DurationDays" class="form-control" value="{{ old('DurationDays', $timeline->DurationDays) }}" required>
        </div>

        <button type="submit" class="btn btn-success mt-2">Update Timeline</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary mt-2">Cancel</a>
    </form>
</div>
@endsection
