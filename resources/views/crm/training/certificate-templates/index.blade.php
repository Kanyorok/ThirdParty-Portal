@extends('layouts.app')

@section('title', 'Certificate Templates')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Certificate Templates</h2>
        <div class="d-flex gap-2">
            <a class="btn btn-primary" href="{{ route('crm.training.certificate-templates.create') }}">+ New Template</a>
            <a class="btn btn-outline-secondary" href="{{ route('crm.training.certificates.index') }}">Certificates</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Program</label>
                    <select name="program_id" class="form-select">
                        <option value="">All</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->Id }}" @selected(request('program_id') == $program->Id)>{{ $program->Title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Scope</label>
                    <select name="scope" class="form-select">
                        <option value="">All</option>
                        @foreach($scopeList as $scopeKey => $scopeLabel)
                            <option value="{{ $scopeKey }}" @selected(request('scope') === $scopeKey)>{{ $scopeLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="sample" class="form-select">
                        <option value="">All</option>
                        <option value="1" @selected(request('sample') === '1')>Sample</option>
                        <option value="0" @selected(request('sample') === '0')>Custom</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="active" class="form-select">
                        <option value="">All</option>
                        <option value="1" @selected(request('active') === '1')>Active</option>
                        <option value="0" @selected(request('active') === '0')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary" href="{{ route('crm.training.certificate-templates.index') }}">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Scope</th>
                            <th>Type</th>
                            <th>Default Issuer</th>
                            <th>Validity (Months)</th>
                            <th>Template File</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $template->Name }}</div>
                                    <div class="text-muted small">{{ $template->Description ?? '-' }}</div>
                                </td>
                                <td>{{ $template->program?->Title ?? 'Global' }}</td>
                                <td>{{ $template->IsSample ? 'Sample' : 'Custom' }}</td>
                                <td>{{ $template->DefaultIssuingBody ?? '-' }}</td>
                                <td>{{ $template->DefaultValidityMonths ?? '-' }}</td>
                                <td>
                                    @if($template->document)
                                        <a href="{{ route('file.preview', ['document' => $template->document->DocumentId]) }}" target="_blank">View</a>
                                    @elseif(!empty($template->BackgroundImagePath))
                                        <a href="{{ asset($template->BackgroundImagePath) }}" target="_blank" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                            <img src="{{ asset($template->BackgroundImagePath) }}" alt="Background sample" style="width: 50px; height: 35px; object-fit: cover; border: 1px solid #d9dde3; border-radius: 4px;">
                                            <span>Built-in sample</span>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($template->IsActive)
                                        <span class="text-success">Active</span>
                                    @else
                                        <span class="text-muted">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('crm.training.certificate-templates.edit', $template->Id) }}">Edit</a>
                                    @if($template->IsActive)
                                        <form method="POST" action="{{ route('crm.training.certificate-templates.destroy', $template->Id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Deactivate this template?')">Deactivate</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No certificate templates found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $templates->links() }}
    </div>
</div>
@endsection
