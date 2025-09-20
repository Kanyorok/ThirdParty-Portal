@extends('layouts.app')
@section('title', 'Clarification Review')
@section('content')
<div class="container mt-4">

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('tenderclarification.update') }}" method="POST">
        @csrf
        @method('PATCH')
        <input type="hidden" name="clarification_id" value="{{ $clarification->ClarificationID ?? '' }}">

        <div class="mb-3">
            <label for="supplierName" class="form-label">From Supplier</label>
            <input type="text" class="form-control" name="supplierName" value="{{ $clarification->supplierName ?? 'Unknown Supplier' }}" readonly>
        </div>

        <div class="mb-3">
            <label for="questionText" class="form-label">Question</label>
            <textarea class="form-control" id="questionText" name="question" rows="3" readonly>{{ $clarification->Question ?? 'No question available' }}</textarea>
        </div>

        <div class="mb-3">
            <label for="responseText" class="form-label">Response</label>
            <textarea class="form-control" id="responseText" name="answer" rows="4" placeholder="Enter official response...">{{ old('answer', $clarification->Answer ?? '') }}</textarea>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_published_to_all" id="publishToAll" value="1" {{ isset($clarification->ISPUBLISHEDTOALL) && $clarification->ISPUBLISHEDTOALL ? 'checked' : '' }}>
            <label class="form-check-label" for="publishToAll">Publish this clarification for all bidders</label>
        </div>

        <button type="submit" class="btn btn-primary">Save Response</button>
    </form>
</div>
@endsection
