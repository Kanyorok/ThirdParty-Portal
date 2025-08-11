@extends('layouts.app')
@section('title', 'Template Library')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">📄 Template Library</h4>
        <a href="{{ route('legal.templates.create') }}" class="btn btn-primary">➕ Add Template</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Template Name</th>
                <th>Document Type</th>
                <th>Version</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($templates as $template)
                <tr>
                    <td>{{ $template->TemplateName }}</td>
                    <td>{{ $template->DocumentType }}</td>
                    <td>{{ $template->Version }}</td>
                    <td>{{ $template->Description }}</td>
                    <td>
                        <a href="{{ route('legal.templates.show', $template->ID) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('legal.templates.edit', $template->ID) }}" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No templates found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
