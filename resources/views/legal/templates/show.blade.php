@extends('layouts.app')
@section('title', 'View Template')

@section('content')
<div class="card p-4 shadow rounded-4 mb-4">
    <h4 class="mb-4">📄 Template Details</h4>

    <ul class="list-group mb-4">
        <li class="list-group-item"><strong>Template Name:</strong> {{ $template->TemplateName }}</li>
        <li class="list-group-item"><strong>Document Type:</strong> {{ $template->DocumentType }}</li>
        <li class="list-group-item"><strong>Version:</strong> {{ $template->Version }}</li>
        <li class="list-group-item"><strong>Description:</strong> {{ $template->Description }}</li>
        <li class="list-group-item"><strong>Status:</strong> 
            @if ($template->IsActive)
                <span class="badge bg-success">Active</span>
            @else
                <span class="badge bg-secondary">Inactive</span>
            @endif
        </li>
        <li class="list-group-item"><strong>Created On:</strong> {{ \Carbon\Carbon::parse($template->CreatedOn)->format('d M Y, h:i A') }}</li>
    </ul>

    <div class="card border shadow-sm">
        <div class="card-header bg-light fw-bold">📑 Template Content</div>
        <div class="card-body" style="white-space: pre-wrap;">
            {!! $template->TemplateBody !!}
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('legal.templates.edit', $template->ID) }}" class="btn btn-warning">✏️ Edit Template</a>
        <a href="{{ route('legal.templates.index') }}" class="btn btn-secondary">⬅️ Back to List</a>
    </div>
</div>
@endsection
