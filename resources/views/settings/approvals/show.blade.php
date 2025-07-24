@extends('layouts.app')
@section('title', 'Approval Workflow Details')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">👁️ View Approval Workflow</h4>

    <dl class="row">
        <dt class="col-sm-3">Name</dt>
        <dd class="col-sm-9">{{ $approval->Name }}</dd>

        <dt class="col-sm-3">Description</dt>
        <dd class="col-sm-9">{{ $approval->Description }}</dd>

        <dt class="col-sm-3">Document Type</dt>
        <dd class="col-sm-9">{{ class_basename($approval->Source) }}</dd>

        <dt class="col-sm-3">Created By</dt>
        <dd class="col-sm-9">{{ optional($approval->createdByUser)->Name ?? 'N/A' }}</dd>

        <dt class="col-sm-3">Created On</dt>
        <dd class="col-sm-9">{{ \Carbon\Carbon::parse($approval->CreatedOn)->format('d-m-Y H:i') }}</dd>
    </dl>

    <a href="{{ route('settings.approval_stages') }}" class="btn btn-secondary mt-3">← Back to List</a>
</div>
@endsection
