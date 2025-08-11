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
            <tr>
                <td>Contract Agreement Template</td>
                <td>Legal</td>
                <td>1.0</td>
                <td>Standard agreement format for client contracts.</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <tr>
                <td>Non-Disclosure Agreement</td>
                <td>Legal</td>
                <td>2.1</td>
                <td>Confidentiality agreement for partnerships.</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <tr>
                <td>Employee Onboarding Template</td>
                <td>HR</td>
                <td>3.0</td>
                <td>Checklist and forms for new hires.</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <tr>
                <td>Project Proposal Template</td>
                <td>Business</td>
                <td>1.4</td>
                <td>Standardized proposal for client projects.</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <tr>
                <td>Financial Report Template</td>
                <td>Finance</td>
                <td>2.0</td>
                <td>Quarterly and annual financial report format.</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            {{-- @forelse($templates as $template)
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
            @endforelse --}}
        </tbody>
    </table>
</div>
@endsection
