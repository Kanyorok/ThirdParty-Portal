@extends('layouts.app')
@section('title', 'Edit Execution Log')
@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">✏️ Edit Execution Log</h4>
    <form method="POST" action="{{ route('legal.documents.execution_logs.update', [$document->ID, $log->ID]) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Signed By</label>
            <input type="text" name="SignedBy" class="form-control" value="{{ $log->SignedBy }}" required>
        </div>

        <div class="mb-3">
            <label>Signed On</label>
            <input type="datetime-local" name="SignedOn" class="form-control"
                   value="{{ \Carbon\Carbon::parse($log->SignedOn)->format('Y-m-d\\TH:i') }}" required>
        </div>

        <div class="mb-3">
            <label>DMS Document ID (if linked)</label>
            <input type="number" name="LinkedDMSDocID" class="form-control" value="{{ $log->LinkedDMSDocID }}">
        </div>

        <div class="mb-3">
            <label>Remarks</label>
            <textarea name="Remarks" class="form-control">{{ $log->Remarks }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
