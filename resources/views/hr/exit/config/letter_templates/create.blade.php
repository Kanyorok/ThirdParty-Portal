@extends('layouts.app')

@section('title', 'New Exit Letter Mapping')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Exit Letter Mapping</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.exit-letter-templates.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('hr.config.exit-letter-templates.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Letter Type *</label>
                        <select name="LetterType" class="form-select" required>
                            @foreach($letterTypes as $type)
                                <option value="{{ $type }}" @selected(old('LetterType') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Legal Template</label>
                        <select name="TemplateID" class="form-select">
                            <option value="">Select</option>
                            @foreach($legalTemplates as $template)
                                <option value="{{ $template->Id }}" @selected(old('TemplateID') == $template->Id)>
                                    {{ $template->Title }} ({{ $template->DocumentType ?? 'Template' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.config.exit-letter-templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Mapping</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
