@extends('layouts.app')

@section('title', 'Exit Letter Templates')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Exit Letter Templates</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.exit-letter-templates.create') }}">+ New Mapping</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Letter Type</th>
                            <th>Template</th>
                            <th>Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>{{ $template->LetterType }}</td>
                                <td>{{ $legalTemplates[$template->TemplateID]->Title ?? '-' }}</td>
                                <td>{{ $template->IsActive ? 'Yes' : 'No' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.exit-letter-templates.edit', $template->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No letter templates mapped.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
