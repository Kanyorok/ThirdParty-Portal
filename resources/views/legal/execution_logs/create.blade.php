@extends('layouts.app')
@section('title', 'Add Execution Log')
@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">✅ Add Execution Log for: {{ $document->DocumentTitle }}</h4>
        <form method="POST" action="{{ route('legal.documents.execution_logs.store', $document->ID) }}">
            @csrf

            <div class="mb-3">
                <label>Signed By</label>
                <input type="text" name="SignedBy" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Signed On</label>
                <input type="datetime-local" name="SignedOn" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>DMS Document ID (if linked)</label>
                <input type="number" name="LinkedDMSDocID" class="form-control">
            </div>

            <div class="mb-3">
                <label>Remarks</label>
                <textarea name="Remarks" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>
@endsection
