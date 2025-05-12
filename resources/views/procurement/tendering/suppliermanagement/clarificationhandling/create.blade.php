@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Clarification Review</h4>
    <form>
        <div class="mb-3">
            <label for="supplierName" class="form-label">From Supplier</label>
            <input type="text" class="form-control" value="Tech Supplies Ltd" readonly>
        </div>

        <div class="mb-3">
            <label for="questionText" class="form-label">Question</label>
            <textarea class="form-control" id="questionText" rows="3" readonly>Do we need to include delivery timelines in our pricing?</textarea>
        </div>

        <div class="mb-3">
            <label for="responseText" class="form-label">Response</label>
            <textarea class="form-control" id="responseText" rows="4" placeholder="Enter official response..."></textarea>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="publishToAll">
            <label class="form-check-label" for="publishToAll">Publish this clarification for all bidders</label>
        </div>

        <button type="submit" class="btn btn-primary">Save Response</button>
    </form>
</div>

@endsection